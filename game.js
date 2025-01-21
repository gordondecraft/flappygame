let canStartGame = true;
const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');
const img = new Image();
img.src = "./images/v1-6-3.png";

let gamePlaying = false;
let gameOver = false;
const gravity = .4;
let speed;
const baseSpeed = 5.8;
const size = [51, 36];
const jump = -8.5;
let cTenth;
let index = 0;
let bestScore = 0;
let flight;
let flyHeight;
let currentScore;
let pipes = [];
let devicePixelRatio;
let gameStartTime;
let lastPipeTime;
let lastJumpTime;
let jumpCount = 0;
let scoreVerificationData = [];

let pipeWidth = 78;
let pipeGap;

const pipeLoc = () => {
    const randomValue = Math.random();
    scoreVerificationData.push({
        type: 'pipe',
        random: randomValue,
        timestamp: Date.now()
    });
    return (randomValue * ((canvas.height / devicePixelRatio - (pipeGap + pipeWidth)) - pipeWidth)) + pipeWidth;
};

let initialLeaderboardButton = {
    x: 0,
    y: 0,
    width: 150,
    height: 50,
};

let buyBuddyButton = {
    x: 0,
    y: 0,
    width: 150,
    height: 50,
};

let shareButton = {
    x: 0,
    y: 0,
    width: 150,
    height: 50,
};

let leaderboardButton = {
    x: 0,
    y: 0,
    width: 150,
    height: 50,
};

let playAgainButton = {
    x: 0,
    y: 0,
    width: 150,
    height: 50,
};

function setCanvasSize() {
    devicePixelRatio = window.devicePixelRatio || 1;
    const windowHeight = window.innerHeight;
    const windowWidth = window.innerWidth;

    // Imposta le dimensioni del canvas per riempire lo schermo
    canvas.style.height = windowHeight + 'px';
    canvas.style.width = windowWidth + 'px';
    canvas.width = windowWidth * devicePixelRatio;
    canvas.height = windowHeight * devicePixelRatio;
    
    // Adatta la velocità in base al dispositivo
    if (windowWidth <= 768) {
        // Velocità per mobile - mantiene la formula originale
        speed = baseSpeed * (windowWidth / 431);
    } else {
        // Velocità ridotta per desktop - dividiamo per un fattore più alto
        speed = (baseSpeed * (windowWidth / 431)) / 2.5;
    }

    ctx.scale(devicePixelRatio, devicePixelRatio);
    ctx.imageSmoothingEnabled = false;
    
    cTenth = (canvas.width / devicePixelRatio / 10);
    pipeGap = (canvas.height / devicePixelRatio) * 0.40;
    
    // Posiziona i bottoni della schermata iniziale
    initialLeaderboardButton = {
        x: (canvas.width / devicePixelRatio) / 2 - 150,
        y: (canvas.height / devicePixelRatio) / 2 + 120,
        width: 300,
        height: 50,
    };
    
    buyBuddyButton = {
        x: (canvas.width / devicePixelRatio) / 2 - 150,
        y: (canvas.height / devicePixelRatio) / 2 + 190,
        width: 300,
        height: 50,
    };
    
    // Bottoni del game over
    const buttonY = (canvas.height / devicePixelRatio) / 2;
    
    playAgainButton = {
        x: (canvas.width / devicePixelRatio) / 2 - 150,
        y: buttonY - 80,
        width: 300,
        height: 50,
    };
    
    shareButton = {
        x: (canvas.width / devicePixelRatio) / 2 - 150,
        y: buttonY,
        width: 300,
        height: 50,
    };
    
    leaderboardButton = {
        x: (canvas.width / devicePixelRatio) / 2 - 150,
        y: buttonY + 80,
        width: 300,
        height: 50,
    };
}

function setup() {
    currentScore = 0;
    flight = jump;
    flyHeight = (canvas.height / devicePixelRatio / 2) - (size[1] / 2);
    pipes = [];
    scoreVerificationData = [];
    jumpCount = 0;
    gameStartTime = Date.now();
    lastPipeTime = gameStartTime;
    
    if (gamePlaying) {
        // Calcoliamo quante pipe servono per riempire lo schermo
        const screenWidth = canvas.width / devicePixelRatio;
        const distanceBetweenPipes = pipeGap + pipeWidth;
        const numberOfPipes = Math.ceil(screenWidth / distanceBetweenPipes) + 2; // +2 per sicurezza
        
        setTimeout(() => {
            pipes = Array(numberOfPipes).fill().map((a, i) => {
                const pipeData = [
                    canvas.width / devicePixelRatio + ((i + 1) * (pipeGap + pipeWidth)),
                    pipeLoc()
                ];
                return pipeData;
            });
        }, 250);
    }
    
    gameOver = false;
}

function drawButton(button, text) {
    ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
    ctx.fillRect(button.x + 2, button.y + 4, button.width, button.height);

    ctx.fillStyle = '#45a049';
    ctx.fillRect(button.x, button.y + button.height - 4, button.width, 4);

    ctx.fillStyle = '#4CAF50';
    ctx.fillRect(button.x, button.y, button.width, button.height - 4);

    const radius = 4;
    ctx.fillStyle = '#4CAF50';
    ctx.beginPath();
    ctx.moveTo(button.x + radius, button.y);
    ctx.lineTo(button.x + button.width - radius, button.y);
    ctx.quadraticCurveTo(button.x + button.width, button.y, button.x + button.width, button.y + radius);
    ctx.lineTo(button.x + button.width, button.y + button.height - radius - 4);
    ctx.quadraticCurveTo(button.x + button.width, button.y + button.height - 4, button.x + button.width - radius, button.y + button.height - 4);
    ctx.lineTo(button.x + radius, button.y + button.height - 4);
    ctx.quadraticCurveTo(button.x, button.y + button.height - 4, button.x, button.y + button.height - radius - 4);
    ctx.lineTo(button.x, button.y + radius);
    ctx.quadraticCurveTo(button.x, button.y, button.x + radius, button.y);
    ctx.fill();

    ctx.fillStyle = "#ffffff";
    ctx.font = "bold 20px Arial";
    ctx.letterSpacing = "2px";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.fillText(text, button.x + button.width / 2, button.y + button.height / 2 - 2);

    const gradient = ctx.createLinearGradient(button.x, button.y, button.x, button.y + 10);
    gradient.addColorStop(0, 'rgba(255, 255, 255, 0.2)');
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
    ctx.fillStyle = gradient;
    ctx.fillRect(button.x, button.y, button.width, 10);
}

function render() {
 index++;
    
    ctx.clearRect(0, 0, canvas.width / devicePixelRatio, canvas.height / devicePixelRatio);
    
    if (gamePlaying || !gameOver) {
        const backgroundWidth = canvas.width / devicePixelRatio;
        const backgroundHeight = canvas.height / devicePixelRatio;

        ctx.drawImage(img, 0, 0, 431, 768, 
            -((index * (speed / 2)) % backgroundWidth) + backgroundWidth, 
            0, backgroundWidth, backgroundHeight);
        ctx.drawImage(img, 0, 0, 431, 768, 
            -(index * (speed / 2)) % backgroundWidth, 
            0, backgroundWidth, backgroundHeight);
    }

    if (gamePlaying) {
        pipes.map(pipe => {
            pipe[0] -= speed;

            ctx.drawImage(img, 432, 588 - pipe[1], pipeWidth, pipe[1], 
                pipe[0], 0, pipeWidth, pipe[1]);
            ctx.drawImage(img, 432 + pipeWidth, 108, pipeWidth, 768 - pipe[1] + pipeGap, 
                pipe[0], pipe[1] + pipeGap, pipeWidth, 768 - pipe[1] + pipeGap);

            if (pipe[0] <= -pipeWidth) {
                currentScore++;
                bestScore = Math.max(bestScore, currentScore);
                pipes = [...pipes.slice(1), [
                    pipes[pipes.length - 1][0] + pipeGap + pipeWidth, 
                    pipeLoc()
                ]];
            }

            if ([
                pipe[0] <= cTenth + size[0],
                pipe[0] + pipeWidth >= cTenth,
                pipe[1] > flyHeight || pipe[1] + pipeGap < flyHeight + size[1]
            ].every(elem => elem)) {
                endGame();
            }
        });

        ctx.font = "30px 'Press Start 2P', cursive";
        ctx.textAlign = "center";
        ctx.strokeStyle = "black";
        ctx.lineWidth = 8;
        ctx.strokeText(`${currentScore}`, canvas.width / devicePixelRatio / 2, 100);
        ctx.fillStyle = "white";
        ctx.fillText(`${currentScore}`, canvas.width / devicePixelRatio / 2, 100);

        flight += gravity;
        flyHeight = Math.min(flyHeight + flight, canvas.height / devicePixelRatio - size[1]);
        ctx.drawImage(img, 432, Math.floor((index % 9) / 3) * size[1], ...size, cTenth, flyHeight, ...size);
    } else if (!gameOver) {
          ctx.drawImage(img, 432, Math.floor((index % 9) / 3) * size[1], ...size, 
            ((canvas.width / devicePixelRatio / 2) - size[0] / 2), flyHeight, ...size);
        flyHeight = (canvas.height / devicePixelRatio / 2) - (size[1] / 2);

        ctx.font = "30px 'Press Start 2P', cursive";
        ctx.textAlign = "center";
        ctx.strokeStyle = "black";
        ctx.lineWidth = 8;
        ctx.strokeText("Flappy Buddy", canvas.width / devicePixelRatio / 2, 180);
        ctx.fillStyle = "white";
        ctx.fillText("Flappy Buddy", canvas.width / devicePixelRatio / 2, 180);
    

        // Disegna l'immagine TAP con dimensioni maggiorate
        const originalTapWidth = 56; // 587 - 531
        const originalTapHeight = 17;
        const scaleFactor = 2; // Aumenta questo valore per ingrandire ulteriormente
        
        const tapWidth = originalTapWidth * scaleFactor;
        const tapHeight = originalTapHeight * scaleFactor;
        const tapX = (canvas.width / devicePixelRatio / 2) - (tapWidth / 2);
        const tapY = shareButton.y + 30;
        
  ctx.drawImage(img, 
            531, 0,  // Coordinate x,y del TAP
            57, 18,  // Dimensioni del TAP
            tapX, tapY, 
            tapWidth, tapHeight
        );
        
    

        // Draw initial screen buttons
        drawButton(initialLeaderboardButton, "LEADERBOARD");
        drawButton(buyBuddyButton, "BUY $BUDDY");

    } else {
        ctx.fillStyle = "#4a4a4a";
        ctx.fillRect(0, 0, canvas.width / devicePixelRatio, canvas.height / devicePixelRatio);
        
        ctx.font = "30px 'Press Start 2P', cursive";
        ctx.fillStyle = "white";
        ctx.textAlign = "center";
        ctx.fillText(`Score: ${currentScore}`, canvas.width / devicePixelRatio / 2, playAgainButton.y - 50);

        drawButton(playAgainButton, "PLAY AGAIN");
        drawButton(shareButton, "SHARE");
        drawButton(leaderboardButton, "ADD TO LEADERBOARD");
    }

    window.requestAnimationFrame(render);
}

function generateScoreHash() {
    const gameData = {
        score: currentScore,
        startTime: gameStartTime,
        endTime: Date.now(),
        jumps: jumpCount,
        pipeData: scoreVerificationData,
        version: '1.0'
    };
    return btoa(JSON.stringify(gameData));
}

function submitScore(scoreHash) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'leaderboard';

    // Add score input
    const scoreInput = document.createElement('input');
    scoreInput.type = 'hidden';
    scoreInput.name = 'game_score';
    scoreInput.value = currentScore;
    form.appendChild(scoreInput);

    // Add verification hash input
    const hashInput = document.createElement('input');
    hashInput.type = 'hidden';
    hashInput.name = 'verify_hash';
    hashInput.value = scoreHash;
    form.appendChild(hashInput);

    // Add submission type input
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'submission_type';
    typeInput.value = 'score_submission';
    form.appendChild(typeInput);

    document.body.appendChild(form);
    form.submit();
}

function endGame() {
    gamePlaying = false;
    gameOver = true;
    canStartGame = false;
    setTimeout(() => {
        canStartGame = true;
    }, 1000);
}

function handleGameClick(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / devicePixelRatio / rect.width;
    const scaleY = canvas.height / devicePixelRatio / rect.height;
    
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;

    if (!gamePlaying) {
        if (gameOver) {
            // Game over buttons handling
            if (mouseX > playAgainButton.x && 
                mouseX < playAgainButton.x + playAgainButton.width && 
                mouseY > playAgainButton.y && 
                mouseY < playAgainButton.y + playAgainButton.height) {
                if (canStartGame) {
                    gamePlaying = true;
                    setup();
                }
            }
            else if (mouseX > shareButton.x && 
                mouseX < shareButton.x + shareButton.width && 
                mouseY > shareButton.y && 
                mouseY < shareButton.y + shareButton.height) {
                
                const shareText = `I scored ${currentScore} points on Flappy Buddy! Try to beat my score at flappybuddy.com 🎮 #FlappyBuddy @BuddycoinToken`;
                const twitterUrl = `https://x.com/intent/tweet?text=${encodeURIComponent(shareText)}`;
                window.open(twitterUrl, '_blank');
            }
            else if (mouseX > leaderboardButton.x && 
                     mouseX < leaderboardButton.x + leaderboardButton.width && 
                     mouseY > leaderboardButton.y && 
                     mouseY < leaderboardButton.y + leaderboardButton.height) {
                
                const scoreHash = generateScoreHash();
                submitScore(scoreHash);
            }
        } else {
            // Initial screen buttons handling
            if (mouseX > initialLeaderboardButton.x && 
                mouseX < initialLeaderboardButton.x + initialLeaderboardButton.width && 
                mouseY > initialLeaderboardButton.y && 
                mouseY < initialLeaderboardButton.y + initialLeaderboardButton.height) {
                // Redirect to leaderboard page
                window.location.href = 'leaderboard';
            }
            else if (mouseX > buyBuddyButton.x && 
                     mouseX < buyBuddyButton.x + buyBuddyButton.width && 
                     mouseY > buyBuddyButton.y && 
                     mouseY < buyBuddyButton.y + buyBuddyButton.height) {
                // Redirect to buy BUDDY page
                window.location.href = 'https://mint.club/token/base/BUDDY';
            }
            else if (canStartGame) {
                gamePlaying = true;
                setup();
            }
        }
    } else {
        flight = jump;
        jumpCount++;
        lastJumpTime = Date.now();
        scoreVerificationData.push({
            type: 'jump',
            timestamp: lastJumpTime,
            height: flyHeight
        });
    }
}

canvas.addEventListener('click', handleGameClick);

document.addEventListener('keydown', (e) => {
    if (e.code === 'Space') {
        e.preventDefault();
        if (gamePlaying) {
            flight = jump;
            jumpCount++;
            lastJumpTime = Date.now();
            scoreVerificationData.push({
                type: 'jump',
                timestamp: lastJumpTime,
                height: flyHeight
            });
        } else if (gameOver) {
            if(canStartGame) {
                gamePlaying = true;
                setup();
            }
        } else {
            gamePlaying = true;
            setup();
        }
    }
});

setCanvasSize();
setup();
img.onload = render;
window.addEventListener('resize', () => {
    setCanvasSize();
    setup();
});
