<?php
session_start();

class ScoreVerifier {
    private function validateTiming($gameData) {
        $minTimePerScore = 0.01; // Minimum seconds needed per point
        $duration = ($gameData->endTime - $gameData->startTime) / 1000; // Convert to seconds
        $minimumTimeNeeded = $gameData->score * $minTimePerScore;
        return $duration >= $minimumTimeNeeded;
    }
    
    private function validateJumps($gameData) {
        $minJumpsPerPoint = 1;
        return $gameData->jumps >= ($gameData->score * $minJumpsPerPoint);
    }
    
    private function validatePipeData($gameData) {
        if (empty($gameData->pipeData)) {
            return false;
        }
        
        $lastTimestamp = $gameData->startTime;
        foreach ($gameData->pipeData as $data) {
            if ($data->timestamp < $lastTimestamp) {
                return false;
            }
            $lastTimestamp = $data->timestamp;
            
            if ($data->type === 'pipe' && ($data->random < 0 || $data->random > 1)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function verifyScore($hash) {
        try {
            $gameData = json_decode(base64_decode($hash));
            
            if (!$gameData || !isset($gameData->score) || !isset($gameData->version)) {
                error_log("Basic validation failed");
                return false;
            }
            
            $timing = $this->validateTiming($gameData);
            $jumps = $this->validateJumps($gameData);
            $pipes = $this->validatePipeData($gameData);
            
            if (!$timing) {
                error_log("Timing validation failed - Duration: " . ($gameData->endTime - $gameData->startTime) / 1000 . " Score: " . $gameData->score);
            }
            if (!$jumps) {
                error_log("Jumps validation failed - Jumps: " . $gameData->jumps . " Score: " . $gameData->score);
            }
            if (!$pipes) {
                error_log("Pipe data validation failed");
            }
            
            return $timing && $jumps && $pipes;
                   
        } catch (Exception $e) {
            error_log("Exception in verifyScore: " . $e->getMessage());
            return false;
        }
    }
}

// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => '',
    'user' => '',
    'pass' => ''
];

$error = null;
$success = null;
$leaderboard = [];
$showScoreForm = false;

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['user'],
        $db_config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Handle initial score submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submission_type']) && $_POST['submission_type'] === 'score_submission') {
        $score = filter_input(INPUT_POST, 'game_score', FILTER_VALIDATE_INT);
        $verifyHash = filter_input(INPUT_POST, 'verify_hash', FILTER_SANITIZE_STRING);
        
        if ($score !== null && $verifyHash !== null) {
            $_SESSION['pending_score'] = $score;
            $_SESSION['pending_hash'] = $verifyHash;
            $showScoreForm = true;
        } else {
            $error = "Invalid score data";
        }
    }
    
    // Handle username submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
        $score = $_SESSION['pending_score'] ?? null;
        $verifyHash = $_SESSION['pending_hash'] ?? null;
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        
        if ($score === null || $verifyHash === null) {
            $error = "Score submission expired";
        } else if (empty($username)) {
            $error = "Username is required";
        } else {
            $verifier = new ScoreVerifier();
            
            if ($verifier->verifyScore($verifyHash)) {
                $stmt = $pdo->prepare("
                    INSERT INTO leaderboard (username, score, created_at)
                    VALUES (:username, :score, NOW())
                ");
                
                $stmt->execute([
                    'username' => $username,
                    'score' => $score
                ]);
                
                unset($_SESSION['pending_score']);
                unset($_SESSION['pending_hash']);
                $success = "Score successfully added to leaderboard!";
            } else {
                $error = "Invalid score verification";
            }
        }
    }
    
    // Get top scores
    $stmt = $pdo->query("
         SELECT username, score, created_at
         FROM leaderboard
         ORDER BY score DESC, created_at ASC
         LIMIT 100
    ");
    
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = "Unable to connect to leaderboard at this time";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flappy Buddy Leaderboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Press Start 2P', cursive;
            background-color: #4a4a4a;
            color: white;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            color: #FFD700;
            margin-bottom: 40px;
            font-size: 24px;
        }
        
        .score-form {
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        input[type="text"] {
            font-family: 'Press Start 2P', cursive;
            padding: 10px;
            margin: 10px;
            width: 200px;
            border: none;
            border-radius: 5px;
        }
        
        button {
            font-family: 'Press Start 2P', cursive;
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 30px;
            cursor: pointer;
            margin: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .leaderboard {
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
        }
        
        .leaderboard-entry {
            display: grid;
            grid-template-columns: 50px 1fr 100px;
            padding: 10px;
            border-bottom: 1px solid #555;
            align-items: center;
        }
        
        .leaderboard-entry:last-child {
            border-bottom: none;
        }
        
        .rank {
            color: #FFD700;
            font-size: 14px;
        }
        
        .username {
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .score {
            font-size: 14px;
            text-align: right;
        }
        
        .error {
            background-color: #ff4444;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .success {
            background-color: #44ff44;
            color: black;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            
            h1 {
                font-size: 20px;
            }
            
            .leaderboard-entry {
                grid-template-columns: 40px 1fr 80px;
                font-size: 12px;
            }
            
            button {
                padding: 10px 20px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Flappy Buddy Leaderboard</h1>
        
        <div class="action-buttons">
            <button type="button" onclick="window.location.href='https://flappybuddy.com'">PLAY GAME</button>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($showScoreForm): ?>
            <div class="score-form">
                <h2>Submit Your Score: <?php echo htmlspecialchars($_SESSION['pending_score']); ?></h2>
                <form method="POST" action="leaderboard">
                    <input type="text" name="username" placeholder="Enter your name" required maxlength="20"
                           pattern="[A-Za-z0-9\s]{1,20}" title="Letters, numbers and spaces only (max 20 characters)">
                    <br>
                    <button type="submit">SUBMIT SCORE</button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="leaderboard">
            <?php if (empty($leaderboard)): ?>
                <div style="text-align: center; padding: 20px;">No scores yet. Be the first to play!</div>
            <?php else: ?>
                <?php foreach ($leaderboard as $index => $entry): ?>
                    <div class="leaderboard-entry">
                        <span class="rank">#<?php echo $index + 1; ?></span>
                        <span class="username" style="padding-left:20px;"><?php echo htmlspecialchars($entry['username']); ?></span>
                        <span class="score"><?php echo htmlspecialchars($entry['score']); ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
