<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Play Flappy Buddy</title>
    <meta name="description" content="Flappy Bird but... with Buddy! Now 100% more silly. And fun.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="icon" href="./images/appLogo.png" type="image/png">
    <!--- OpenGraph--->
    <meta property="og:title" content="Flappy Buddy"/>
    <meta property="og:description" content="Flappy Bird but... with Buddy! Now 100% more silly. And fun."/>
    <meta property="og:url" content="https://flappybudd.com"/>
    <meta property="og:site_name" content="Flappy Buddy"/>
    <meta property="og:image" content="https://flappybuddy.com/images/opengraph-3.png"/>
    <meta property="og:type" content="website"/>
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:title" content="Flappy Buddy"/>
    <meta name="twitter:description" content="Flappy Bird but... with Buddy! Now 100% more silly. And fun."/>
    <meta name="twitter:image" content="https://flappybuddy.com/images/opengraph-3.png"/>
    <!-- Farcaster Frame Meta Tags - Simple Redirect Version -->
    <meta property="fc:frame" content="vNext">
    <meta property="fc:frame:image" content="https://i.ibb.co/nBtp7x8/framer.png">
    <meta name="fc:frame:image:aspect_ratio" content="1:1"/>
    <meta property="fc:frame:button:1" content="Play Now 🕹️">
    <meta property="fc:frame:button:1:action" content="link">
    <meta property="fc:frame:button:1:target" content="https://flappybuddy.com">
    <!--- PWA --->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4ebfca">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Flappy Buddy">
    <link rel="apple-touch-icon" href="/images/appLogo.png">   
  </head>
<!---- banner HTML ---->
     <div id="installBanner" class="install-banner hidden">
        <div class="banner-content">
            <img src="https://flappybuddy.com/images/appLogo.png" alt="Flappy Buddy icon" class="app-icon">
            <div class="banner-text">
                <h3 class="banner-title" style="font-weight:bold">Install Flappy Buddy</h3>
                <p class="banner-instruction"></p>
                <p class="banner-benefits"></p>
            </div>
            <button class="close-button" aria-label="Chiudi">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <span class="sr-only">Close</span>
            </button>
        </div>
    </div>
<!--- banner HTML ----->
  <body style="background:#4ebfca">

  <canvas id="canvas" width="431" height="900"></canvas>
  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="/game.js"></script>
    <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
          .then((registration) => {
            console.log('ServiceWorker registration successful');
          })
          .catch((err) => {
            console.log('ServiceWorker registration failed: ', err);
          });
      });
    }
    </script>
  </body>
</html>
<!--- banner script and css --->
<script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if banner was previously dismissed
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }

            // Set cookie that expires in 6 months
            function setBannerCookie() {
                const sixMonths = new Date();
                sixMonths.setMonth(sixMonths.getMonth() + 6);
                document.cookie = `pwaBannerDismissed=true; expires=${sixMonths.toUTCString()}; path=/`;
            }

            // Platform detection
            function detectPlatform() {
                const userAgent = window.navigator.userAgent.toLowerCase();
                if (/iphone|ipad|ipod/.test(userAgent)) return 'ios';
                if (/android/.test(userAgent)) return 'android';
                return 'desktop';
            }

            const messages = {
                    ios: {
                        instruction: "Tap the share button then 'Add to Home Screen'",
                        benefits: "Play offline wherever you are!"
                    },
                    android: {
                        instruction: "Install the App to play offline",
                        benefits: "Quick access from home screen"
                    },
                    desktop: {
                        instruction: "Install the App on your computer",
                        benefits: "Play even without Internet connection"
                    }
            };

            const banner = document.getElementById('installBanner');
            const closeButton = banner.querySelector('.close-button');
            const instruction = banner.querySelector('.banner-instruction');
            const benefits = banner.querySelector('.banner-benefits');

            // Show banner only if it hasn't been dismissed before
            if (!getCookie('pwaBannerDismissed')) {
                const platform = detectPlatform();
                instruction.textContent = messages[platform].instruction;
                benefits.textContent = messages[platform].benefits;
                banner.classList.remove('hidden');
            }

            // Handle banner dismissal
            closeButton.addEventListener('click', function() {
                banner.classList.add('hidden');
                setBannerCookie();
            });
        });
    </script>
 <style>
        .install-banner {
            position: fixed;
            bottom: 1rem;
            left: 1rem;
            right: 1rem;
            max-width: 28rem;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s ease-out;
            border: 1px solid #eee;
            /* Sistema di font per il banner */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, 
                         Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        @supports (backdrop-filter: blur(10px)) {
            .install-banner {
                background: rgba(255, 255, 255, 0.6);
            }
        }

        .banner-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
        }

        .app-icon {
            width: 2.5rem;
            height: 2.5rem;
            flex: none;
            border-radius: 0.5rem;
            object-fit: cover;
        }

        .banner-text {
            flex: 1;
            line-height: 1.2;
        }

        .banner-title {
            font-weight: 600;
            margin: 0 0 0.15rem 0;
            font-size: 0.875rem;
            letter-spacing: -0.01em;
        }

        .banner-instruction {
            margin: 0 0 0.15rem 0;
            font-size: 0.75rem;
            color: #666;
            letter-spacing: -0.01em;
        }

        .banner-benefits {
            margin: 0;
            font-size: 0.75rem;
            color: black;
            font-weight: 600;
            letter-spacing: -0.01em;
            }

        .close-button {
            width: 1.75rem;
            height: 1.75rem;
            border: none;
            background: transparent;
            border-radius: 50%;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            margin-left: 0.25rem;
        }

        .close-button:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .close-button svg {
            width: 0.875rem;
            height: 0.875rem;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .hidden {
            display: none;
        }
    </style>
<!--- banner script and css--->
<style>@import url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');

body {
  margin: 0;
  text-align: center;
  font-family: 'Press Start 2P', cursive;
  user-select: none;
}
header {
  margin: 0 auto;
  width: 431px;
}
h1 {
  padding: 1.2rem 0;
  margin: 0;
}
.score-container {
  display: flex;
  justify-content: space-between;
  padding: 8px 6px;
  background: #5EE270;
}</style>
