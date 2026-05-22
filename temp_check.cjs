
        // Programmatic sound synthesis
        const synth = {
            ctx: null,
            init() {
                if (!this.ctx) {
                    this.ctx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (this.ctx.state === 'suspended') {
                    this.ctx.resume();
                }
            },
            playCountdownBeep(isGo) {
                try {
                    this.init();
                    let osc = this.ctx.createOscillator();
                    let gain = this.ctx.createGain();
                    osc.connect(gain);
                    gain.connect(this.ctx.destination);
                    
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(isGo ? 880 : 440, this.ctx.currentTime);
                    
                    gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.12);
                    
                    osc.start();
                    osc.stop(this.ctx.currentTime + 0.12);
                } catch(e) { console.error(e); }
            },
            playJump() {
                try {
                    this.init();
                    let osc = this.ctx.createOscillator();
                    let gain = this.ctx.createGain();
                    osc.connect(gain);
                    gain.connect(this.ctx.destination);
                    
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(160, this.ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(640, this.ctx.currentTime + 0.15);
                    
                    gain.gain.setValueAtTime(0.12, this.ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.15);
                    
                    osc.start();
                    osc.stop(this.ctx.currentTime + 0.15);
                } catch(e) { console.error(e); }
            },
            playLand() {
                try {
                    this.init();
                    let osc = this.ctx.createOscillator();
                    let gain = this.ctx.createGain();
                    osc.connect(gain);
                    gain.connect(this.ctx.destination);
                    
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(110, this.ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(30, this.ctx.currentTime + 0.08);
                    
                    gain.gain.setValueAtTime(0.15, this.ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.08);
                    
                    osc.start();
                    osc.stop(this.ctx.currentTime + 0.08);
                } catch(e) { console.error(e); }
            },
            playSuccess() {
                try {
                    this.init();
                    const now = this.ctx.currentTime;
                    
                    const playTone = (freq, start, duration) => {
                        let osc = this.ctx.createOscillator();
                        let gain = this.ctx.createGain();
                        osc.connect(gain);
                        gain.connect(this.ctx.destination);
                        
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(freq, start);
                        
                        gain.gain.setValueAtTime(0.08, start);
                        gain.gain.exponentialRampToValueAtTime(0.001, start + duration);
                        
                        osc.start(start);
                        osc.stop(start + duration);
                    };
                    
                    playTone(523.25, now, 0.08); // C5
                    playTone(659.25, now + 0.06, 0.08); // E5
                    playTone(783.99, now + 0.12, 0.08); // G5
                    playTone(1046.50, now + 0.18, 0.25); // C6
                    playTone(523.25, now + 0.18, 0.25); // Chord base
                } catch(e) { console.error(e); }
            },
            playSplash() {
                try {
                    this.init();
                    const now = this.ctx.currentTime;
                    const duration = 0.5;
                    const bufferSize = this.ctx.sampleRate * duration;
                    const buffer = this.ctx.createBuffer(1, bufferSize, this.ctx.sampleRate);
                    const data = buffer.getChannelData(0);
                    
                    for (let i = 0; i < bufferSize; i++) {
                        data[i] = Math.random() * 2 - 1;
                    }
                    
                    let noise = this.ctx.createBufferSource();
                    noise.buffer = buffer;
                    
                    let filter = this.ctx.createBiquadFilter();
                    filter.type = 'lowpass';
                    filter.frequency.setValueAtTime(800, now);
                    filter.frequency.exponentialRampToValueAtTime(80, now + duration);
                    
                    let gain = this.ctx.createGain();
                    gain.gain.setValueAtTime(0.25, now);
                    gain.gain.exponentialRampToValueAtTime(0.001, now + duration);
                    
                    noise.connect(filter);
                    filter.connect(gain);
                    gain.connect(this.ctx.destination);
                    
                    noise.start(now);
                    noise.stop(now + duration);
                    
                    // low rumble
                    let osc = this.ctx.createOscillator();
                    let oscGain = this.ctx.createGain();
                    osc.connect(oscGain);
                    oscGain.connect(this.ctx.destination);
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(80, now);
                    osc.frequency.linearRampToValueAtTime(25, now + 0.3);
                    oscGain.gain.setValueAtTime(0.18, now);
                    oscGain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                    osc.start(now);
                    osc.stop(now + 0.3);
                } catch(e) { console.error(e); }
            }
        };

        // Draw helper: SV98 Lily
        function drawLily(ctx, x, y, size, color, opacity = 1) {
            ctx.save();
            ctx.translate(x, y);
            ctx.scale(size / 100, size / 100);
            ctx.globalAlpha = opacity;
            ctx.fillStyle = color;
            ctx.beginPath();
            
            // Central petal
            ctx.moveTo(0, -45);
            ctx.bezierCurveTo(12, -18, 12, 10, 0, 20);
            ctx.bezierCurveTo(-12, 10, -12, -18, 0, -45);
            
            // Left petal
            ctx.moveTo(0, 5);
            ctx.bezierCurveTo(-18, -8, -40, -15, -40, 5);
            ctx.bezierCurveTo(-40, 22, -18, 22, -5, 15);
            ctx.bezierCurveTo(-8, 20, -12, 28, -12, 32);
            ctx.bezierCurveTo(-12, 36, 0, 34, 0, 15);
            
            // Right petal
            ctx.moveTo(0, 5);
            ctx.bezierCurveTo(18, -8, 40, -15, 40, 5);
            ctx.bezierCurveTo(40, 22, 18, 22, 5, 15);
            ctx.bezierCurveTo(8, 20, 12, 28, 12, 32);
            ctx.bezierCurveTo(12, 36, 0, 34, 0, 15);
            
            ctx.fill();
            
            // Cross bar (ribbon)
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(-16, 12, 32, 5);
            
            ctx.restore();
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('jumpGame', () => ({
                players: [],
                currentPlayer: '',
                previousPlayer: '',
                nextPlayer: '',
                history: [],
                gameState: 'ready', // ready, countdown, playing, handover, success, failed, game_over
                countdownText: '1',
                showSplashOverlay: false,
                saving: false,
                canvas: null,
                ctx: null,
                animationFrameId: null,
                gameTime: 0,
                
                // Game Mode state
                gameMode: 'continuous', // 'continuous' or 'calm'
                handoverProgress: 100,
                handoverInterval: null,
                bgImages: [],
                cachedBgs: {},

                // Game engine variables
                playerState: {
                    x: 0,
                    y: 0,
                    vx: 0,
                    vy: 0,
                    width: 20,
                    height: 36,
                    isGrounded: false,
                    jumpCount: 0,
                    maxJumps: 1 // Single-jump only
                },
                level: {
                    speed: 5.0,
                    gravity: 0.45,
                    jumpForce: -9.5,
                    leftEnd: 450,
                    gapSize: 160,
                    rightStart: 610,
                    totalWidth: 1060
                },
                platforms: [],
                particles: [],
                splashParticles: [],
                camX: 0,

                init() {
                    this.preloadBackgrounds();
                    if (this.players.length >= 3) {
                        this.initializeRotation();
                    }
                },

                preloadBackgrounds() {
                    this.bgImages = [];
                    for (let i = 1; i <= 5; i++) {
                        let img = new Image();
                        img.src = `/images/schlossgraben/bg${i}.png`;
                        this.bgImages.push(img);
                    }
                },

                initializeRotation() {
                    this.history = [];
                    this.previousPlayer = '';
                    
                    // Choose first player completely randomly
                    let randIndex = Math.floor(Math.random() * this.players.length);
                    this.currentPlayer = this.players[randIndex];
                    
                    // Choose next player randomly from remaining
                    let available = this.players.filter(p => p !== this.currentPlayer);
                    let nextRandIdx = Math.floor(Math.random() * available.length);
                    this.nextPlayer = available[nextRandIdx];
                },

                rotatePlayers() {
                    this.previousPlayer = this.currentPlayer;
                    this.currentPlayer = this.nextPlayer;
                    
                    // Select a new random nextPlayer who is different from the new currentPlayer
                    let available = this.players.filter(p => p !== this.currentPlayer);
                    let randIndex = Math.floor(Math.random() * available.length);
                    this.nextPlayer = available[randIndex];
                },

                resetGame() {
                    if (this.handoverInterval) {
                        clearInterval(this.handoverInterval);
                        this.handoverInterval = null;
                    }
                    this.history = [];
                    this.currentPlayer = '';
                    this.previousPlayer = '';
                    this.nextPlayer = '';
                    this.gameState = 'ready';
                    this.saving = false;
                    if (this.players.length >= 3) {
                        this.initializeRotation();
                    }
                },

                startCountdown() {
                    synth.init();
                    this.gameState = 'countdown';
                    this.countdownText = '1';
                    synth.playCountdownBeep(false);

                    setTimeout(() => {
                        synth.playCountdownBeep(true);
                        this.startRunning();
                    }, 1000);
                },

                setupLevel() {
                    const turns = this.history.length;
                    const canvasHeight = this.canvas ? this.canvas.height : 350;
                    const groundY = canvasHeight - 90;
                    
                    // Ensure single jump only
                    this.playerState.maxJumps = 1;
                    
                    // Difficulty scaling: speed starts at 5.0, scales up by 0.35 per turn, max 8.0.
                    this.level.speed = Math.min(5.0 + turns * 0.35, 8.0);
                    this.level.gravity = 0.45;
                    this.level.jumpForce = -9.5;
                    
                    // Select layout type
                    let allowedTypes = [0];
                    if (turns >= 1) allowedTypes.push(1);
                    if (turns >= 2) {
                        allowedTypes.push(2);
                        allowedTypes.push(3);
                    }
                    const layoutType = allowedTypes[Math.floor(Math.random() * allowedTypes.length)];
                    
                    // Clear platforms
                    this.platforms = [];
                    
                    if (layoutType === 0) {
                        // Layout 0: Classic Moat (single gap)
                        const leftEnd = 400;
                        // Gap size scales up to 260px
                        const gapSize = Math.min(130 + turns * 15, 260);
                        const rightStart = leftEnd + gapSize;
                        
                        this.platforms.push({ x: 0, y: groundY, width: leftEnd, height: canvasHeight - groundY + 100, type: 'start' });
                        this.platforms.push({ x: rightStart, y: groundY, width: 450, height: canvasHeight - groundY + 100, type: 'end' });
                        
                        // Add a block/crate in the middle for higher turns
                        if (turns >= 2) {
                            const blockWidth = 40;
                            const bx = leftEnd + gapSize / 2 - blockWidth / 2;
                            const blockHeight = turns >= 4 ? 60 : 30;
                            const by = groundY - blockHeight;
                            this.platforms.push({ x: bx, y: by, width: blockWidth, height: canvasHeight - by + 100, type: 'block' });
                        }
                        
                        this.level.leftEnd = leftEnd;
                        this.level.gapSize = gapSize;
                        this.level.rightStart = rightStart;
                        this.level.totalWidth = rightStart + 450;
                        
                    } else if (layoutType === 1) {
                        // Layout 1: Twin Bridges (Double gap with intermediate flat island)
                        const startWidth = 320;
                        // Gaps scale up to 160px
                        const gap1 = Math.min(100 + turns * 10, 160);
                        const gap2 = Math.min(100 + turns * 10, 160);
                        // Island width shrinks as it gets harder
                        const islandWidth = Math.max(160 - turns * 8, 85);
                        
                        const islandX = startWidth + gap1;
                        const rightStart = islandX + islandWidth + gap2;
                        
                        this.platforms.push({ x: 0, y: groundY, width: startWidth, height: canvasHeight - groundY + 100, type: 'start' });
                        this.platforms.push({ x: islandX, y: groundY, width: islandWidth, height: canvasHeight - groundY + 100, type: 'island' });
                        this.platforms.push({ x: rightStart, y: groundY, width: 450, height: canvasHeight - groundY + 100, type: 'end' });
                        
                        this.level.leftEnd = startWidth;
                        this.level.gapSize = gap1; // Reference for first gap
                        this.level.rightStart = rightStart;
                        this.level.totalWidth = rightStart + 450;
                        
                    } else if (layoutType === 2) {
                        // Layout 2: Stepping Stones (Three gaps, two small stone platforms)
                        const startWidth = 280;
                        const gap = Math.min(95 + turns * 6, 140);
                        const stoneWidth = Math.max(60 - turns * 3, 40);
                        
                        const stone1X = startWidth + gap;
                        const stone2X = stone1X + stoneWidth + gap;
                        const rightStart = stone2X + stoneWidth + gap;
                        
                        this.platforms.push({ x: 0, y: groundY, width: startWidth, height: canvasHeight - groundY + 100, type: 'start' });
                        this.platforms.push({ x: stone1X, y: groundY, width: stoneWidth, height: canvasHeight - groundY + 100, type: 'island' });
                        this.platforms.push({ x: stone2X, y: groundY, width: stoneWidth, height: canvasHeight - groundY + 100, type: 'island' });
                        this.platforms.push({ x: rightStart, y: groundY, width: 450, height: canvasHeight - groundY + 100, type: 'end' });
                        
                        this.level.leftEnd = startWidth;
                        this.level.gapSize = gap;
                        this.level.rightStart = rightStart;
                        this.level.totalWidth = rightStart + 450;
                        
                    } else if (layoutType === 3) {
                        // Layout 3: Crate Climb (Ascending and descending crate steps)
                        const startWidth = 320;
                        const gap1 = Math.min(90 + turns * 8, 140);
                        const gap2 = Math.min(90 + turns * 8, 140);
                        const gap3 = Math.min(90 + turns * 8, 140);
                        
                        const crateWidth = 70;
                        const crate1Height = 50; // Higher crate
                        const crate2Height = 25; // Lower crate
                        
                        const crate1X = startWidth + gap1;
                        const crate2X = crate1X + crateWidth + gap2;
                        const rightStart = crate2X + crateWidth + gap3;
                        
                        this.platforms.push({ x: 0, y: groundY, width: startWidth, height: canvasHeight - groundY + 100, type: 'start' });
                        // Crate 1 (block type)
                        this.platforms.push({ x: crate1X, y: groundY - crate1Height, width: crateWidth, height: canvasHeight - (groundY - crate1Height) + 100, type: 'block' });
                        // Crate 2 (block type)
                        this.platforms.push({ x: crate2X, y: groundY - crate2Height, width: crateWidth, height: canvasHeight - (groundY - crate2Height) + 100, type: 'block' });
                        this.platforms.push({ x: rightStart, y: groundY, width: 450, height: canvasHeight - groundY + 100, type: 'end' });
                        
                        this.level.leftEnd = startWidth;
                        this.level.gapSize = gap1;
                        this.level.rightStart = rightStart;
                        this.level.totalWidth = rightStart + 450;
                    }
                },

                startRunning() {
                    this.gameState = 'playing';
                    this.showSplashOverlay = false;
                    this.gameTime = 0;
                    
                    this.particles = [];
                    this.splashParticles = [];
                    this.camX = 0;

                    // Initialize Canvas and setup level
                    this.$nextTick(() => {
                        this.canvas = document.getElementById('gameCanvas');
                        if (this.canvas) {
                            this.ctx = this.canvas.getContext('2d');
                            this.canvas.width = 800;
                            const rect = this.canvas.getBoundingClientRect();
                            const clientWidth = rect.width || this.canvas.clientWidth || 800;
                            const clientHeight = rect.height || this.canvas.clientHeight || 450;
                            this.canvas.height = Math.round(800 * (clientHeight / clientWidth));
                            
                            // Setup level physics and layouts
                            this.setupLevel();

                            // Reset player state
                            const groundY = this.canvas.height - 90;
                            this.playerState.x = 100;
                            this.playerState.y = groundY - this.playerState.height;
                            this.playerState.vx = this.level.speed;
                            this.playerState.vy = 0;
                            this.playerState.isGrounded = true;
                            this.playerState.jumpCount = 0;

                            // Keyboard listeners
                            window.removeEventListener('keydown', this.handleKeydown);
                            window.addEventListener('keydown', this.handleKeydown.bind(this));

                            // Start loop
                            if (this.animationFrameId) cancelAnimationFrame(this.animationFrameId);
                            this.gameLoop();
                        }
                    });
                },

                startHandoverCountdown() {
                    if (this.handoverInterval) {
                        clearInterval(this.handoverInterval);
                    }
                    
                    this.gameState = 'handover';
                    
                    this.$nextTick(() => {
                        this.canvas = document.getElementById('gameCanvas');
                        if (this.canvas) {
                            this.ctx = this.canvas.getContext('2d');
                            this.canvas.width = 800;
                            const rect = this.canvas.getBoundingClientRect();
                            const clientWidth = rect.width || this.canvas.clientWidth || 800;
                            const clientHeight = rect.height || this.canvas.clientHeight || 450;
                            this.canvas.height = Math.round(800 * (clientHeight / clientWidth));
                            
                            // Reset visuals to start of level
                            this.setupLevel();
                            const groundY = this.canvas.height - 90;
                            this.playerState.x = 100;
                            this.playerState.y = groundY - this.playerState.height;
                            this.playerState.vx = 0;
                            this.playerState.vy = 0;
                            this.playerState.isGrounded = true;
                            this.playerState.jumpCount = 0;
                            this.particles = [];
                            this.splashParticles = [];
                            this.camX = 0;
                            
                            this.draw();
                        }
                    });
                    
                    // Handover duration shrinks by 0.3s per round, minimum 0.8s (800ms)
                    const duration = Math.max(3000 - this.history.length * 300, 800);
                    let elapsed = 0;
                    this.handoverProgress = 100;
                    
                    let lastTickedSecond = -1;
                    
                    this.handoverInterval = setInterval(() => {
                        elapsed += 50;
                        this.handoverProgress = Math.max(0, 100 - (elapsed / duration) * 100);
                        
                        // Beep at whole seconds
                        let secondsRemaining = Math.ceil((duration - elapsed) / 1000);
                        if (secondsRemaining > 0 && secondsRemaining !== lastTickedSecond) {
                            synth.playCountdownBeep(false);
                            lastTickedSecond = secondsRemaining;
                        }
                        
                        if (elapsed >= duration) {
                            clearInterval(this.handoverInterval);
                            this.handoverInterval = null;
                            synth.playCountdownBeep(true);
                            this.startRunningAfterHandover();
                        }
                    }, 50);
                },

                startRunningAfterHandover() {
                    this.gameState = 'playing';
                    this.showSplashOverlay = false;
                    this.gameTime = 0;
                    
                    // Start running
                    this.playerState.vx = this.level.speed;
                    
                    window.removeEventListener('keydown', this.handleKeydown);
                    window.addEventListener('keydown', this.handleKeydown.bind(this));

                    if (this.animationFrameId) cancelAnimationFrame(this.animationFrameId);
                    this.gameLoop();
                },

                handleKeydown(e) {
                    if (e.code === 'Space' || e.code === 'ArrowUp') {
                        e.preventDefault();
                        this.onJumpTrigger();
                    }
                },

                onJumpTrigger() {
                    if (this.gameState !== 'playing') return;
                    
                    let p = this.playerState;
                    if (p.isGrounded) {
                        p.vy = this.level.jumpForce;
                        p.isGrounded = false;
                        p.jumpCount = 1;
                        synth.playJump();
                        this.createDust(p.x + p.width/2, p.y + p.height, 8);
                    } else if (p.jumpCount < p.maxJumps) {
                        p.vy = this.level.jumpForce * 0.95;
                        p.jumpCount++;
                        synth.playJump();
                        this.createDust(p.x + p.width/2, p.y + p.height/2, 6);
                    }
                },

                createDust(x, y, count) {
                    for(let i=0; i<count; i++) {
                        this.particles.push({
                            x: x,
                            y: y,
                            vx: -1 - Math.random() * 2,
                            vy: -Math.random() * 1.5,
                            size: 2 + Math.random() * 4,
                            alpha: 1,
                            decay: 0.04 + Math.random() * 0.04
                        });
                    }
                },

                createSplash(x, y) {
                    for(let i=0; i<25; i++) {
                        this.splashParticles.push({
                            x: x,
                            y: y,
                            vx: (Math.random() - 0.5) * 5,
                            vy: -3 - Math.random() * 5,
                            size: 3 + Math.random() * 5,
                            alpha: 1,
                            decay: 0.03 + Math.random() * 0.03
                        });
                    }
                },

                triggerSuccess() {
                    this.gameState = 'success';
                    synth.playSuccess();
                    
                    // Stop runner
                    this.playerState.vx = 0;
                    this.playerState.vy = 0;
                    
                    // Save history
                    this.history.push(this.currentPlayer);
                    
                    if (this.handoverInterval) {
                        clearInterval(this.handoverInterval);
                        this.handoverInterval = null;
                    }
                    
                    const waitTime = this.gameMode === 'continuous' ? 1200 : 1800;
                    
                    setTimeout(() => {
                        if (this.gameState === 'success') {
                            this.rotatePlayers();
                            if (this.gameMode === 'continuous') {
                                this.startHandoverCountdown();
                            } else {
                                this.gameState = 'ready';
                            }
                        }
                    }, waitTime);
                },

                triggerFail() {
                    if (this.handoverInterval) {
                        clearInterval(this.handoverInterval);
                        this.handoverInterval = null;
                    }
                    
                    this.gameState = 'failed';
                    this.showSplashOverlay = true;
                    synth.playSplash();
                    
                    this.createSplash(this.playerState.x + this.playerState.width/2, this.canvas.height - 60);
                    
                    // Stop runner
                    this.playerState.vx = 0;
                    this.playerState.vy = 0;

                    setTimeout(() => {
                        if (this.gameState === 'failed') {
                            this.gameState = 'game_over';
                        }
                    }, 1800);
                },

                saveLosers() {
                    this.saving = true;
                    
                    let p1 = this.currentPlayer;
                    let p2 = this.previousPlayer;
                    
                    let promises = [];
                    
                    // In Calm Mode, only the current player (who failed) is saved.
                    // In Continuous Mode, both are saved.
                    if (this.gameMode === 'continuous') {
                        promises.push(this.postWinner(p1));
                        if (p2) {
                            promises.push(this.postWinner(p2));
                        }
                    } else {
                        promises.push(this.postWinner(p1));
                    }
                    
                    Promise.all(promises).then(() => {
                        window.location.reload();
                    }).catch(e => {
                        console.error('Failed saving score', e);
                        this.saving = false;
                    });
                },

                postWinner(name) {
                    return fetch('/tools/save-winner', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '"dummy_token"'
                        },
                        body: JSON.stringify({ winner: name })
                    }).then(res => res.json());
                },

                gameLoop() {
                    if (this.gameState !== 'playing' && this.gameState !== 'success' && this.gameState !== 'failed') {
                        return;
                    }

                    this.updatePhysics();
                    this.draw();

                    this.animationFrameId = requestAnimationFrame(this.gameLoop.bind(this));
                },

                updatePhysics() {
                    this.gameTime++;

                    if (this.gameState === 'playing') {
                        let p = this.playerState;
                        
                        // Apply gravity
                        p.vy += this.level.gravity;
                        p.x += p.vx;
                        p.y += p.vy;
                        
                        p.isGrounded = false;

                        // Check collision with platforms
                        for (let plat of this.platforms) {
                            if (p.x + p.width > plat.x && p.x < plat.x + plat.width) {
                                // Falling onto platform top
                                if (p.vy >= 0 && 
                                    p.y + p.height >= plat.y && 
                                    p.y + p.height - p.vy <= plat.y + 14) {
                                    
                                    // Land
                                    if (!p.isGrounded && p.vy > 1) {
                                        synth.playLand();
                                        this.createDust(p.x + p.width/2, plat.y, 4);
                                    }
                                    p.y = plat.y - p.height;
                                    p.vy = 0;
                                    p.isGrounded = true;
                                    p.jumpCount = 0;
                                }
                                // Hit side of a block/wall
                                else if (p.y + p.height > plat.y + 6 && p.y < plat.y + plat.height) {
                                    this.triggerFail();
                                    return;
                                }
                            }
                        }

                        // Spawn running dust
                        if (p.isGrounded && this.gameTime % 6 === 0) {
                            this.particles.push({
                                x: p.x,
                                y: p.y + p.height,
                                vx: -0.8 - Math.random() * 1.5,
                                vy: -Math.random() * 0.5,
                                size: 1.5 + Math.random() * 2.5,
                                alpha: 0.8,
                                decay: 0.05
                            });
                        }

                        // Check water bounds
                        const waterY = this.canvas.height - 60;
                        if (p.y + p.height >= waterY + 5) {
                            this.triggerFail();
                            return;
                        }

                        // Check win condition
                        if (p.x >= this.level.rightStart + 120) {
                            this.triggerSuccess();
                            return;
                        }

                        // Update camera
                        this.camX = p.x - 200;
                        this.camX = Math.max(0, Math.min(this.camX, this.level.totalWidth - 800));
                    }

                    // Update particles
                    for (let i = this.particles.length - 1; i >= 0; i--) {
                        let pt = this.particles[i];
                        pt.x += pt.vx;
                        pt.y += pt.vy;
                        pt.alpha -= pt.decay;
                        if (pt.alpha <= 0) this.particles.splice(i, 1);
                    }

                    // Update splash particles
                    for (let i = this.splashParticles.length - 1; i >= 0; i--) {
                        let sp = this.splashParticles[i];
                        sp.x += sp.vx;
                        sp.y += sp.vy;
                        sp.vy += 0.25; // gravity for splash
                        sp.alpha -= sp.decay;
                        if (sp.alpha <= 0) this.splashParticles.splice(i, 1);
                    }
                },

                draw() {
                    let ctx = this.ctx;
                    let p = this.playerState;
                    
                    const canvasHeight = this.canvas.height;
                    const groundY = canvasHeight - 90;
                    const waterY = canvasHeight - 60;

                    ctx.clearRect(0, 0, 800, canvasHeight);

                    let bgIndex = this.history.length % 5;
                    let bgImg = this.bgImages[bgIndex];
                    let hasBg = bgImg && bgImg.complete && bgImg.naturalWidth > 0;

                    // 1. SKY GRADIENT
                    let skyTopColor = '#0a0524';
                    let skyMidColor = '#190e4a';
                    let skyBottomColor = '#341566';

                    if (hasBg) {
                        if (bgIndex === 0) {
                            skyTopColor = '#4a73b5';
                            skyMidColor = '#5c8cde';
                            skyBottomColor = '#81cde4';
                        } else if (bgIndex === 1) {
                            skyTopColor = '#122c61';
                            skyMidColor = '#1d479b';
                            skyBottomColor = '#2978b3';
                        } else if (bgIndex === 2) {
                            skyTopColor = '#112d64';
                            skyMidColor = '#1c489f';
                            skyBottomColor = '#214e8c';
                        } else if (bgIndex === 3) {
                            skyTopColor = '#1e1c1b';
                            skyMidColor = '#31231c';
                            skyBottomColor = '#483b33';
                        } else {
                            skyTopColor = '#102a5e';
                            skyMidColor = '#1a4498';
                            skyBottomColor = '#3484b4';
                        }
                    }

                    let skyGrad = ctx.createLinearGradient(0, 0, 0, waterY);
                    skyGrad.addColorStop(0, skyTopColor);
                    skyGrad.addColorStop(0.6, skyMidColor);
                    skyGrad.addColorStop(1, skyBottomColor);
                    ctx.fillStyle = skyGrad;
                    ctx.fillRect(0, 0, 800, canvasHeight);

                    // 2. PARALLAX STARS
                    ctx.fillStyle = '#ffffff';
                    for(let i=0; i<30; i++) {
                        let sx = (i * 12345) % 1600;
                        let sy = (i * 54321) % (waterY - 80);
                        let size = (i % 3 === 0) ? 1.5 : 0.8;
                        let px = sx - this.camX * 0.05;
                        // Wrap stars
                        px = (px % 850 + 850) % 850 - 50;
                        ctx.beginPath();
                        ctx.arc(px, sy, size, 0, Math.PI*2);
                        ctx.fill();
                    }

                    // 3. CRESCENT MOON
                    ctx.save();
                    let mx = 650 - this.camX * 0.08;
                    ctx.translate(mx, 60);
                    // Glow
                    let moonGlow = ctx.createRadialGradient(0, 0, 5, 0, 0, 35);
                    moonGlow.addColorStop(0, 'rgba(253, 224, 71, 0.2)');
                    moonGlow.addColorStop(1, 'rgba(253, 224, 71, 0)');
                    ctx.fillStyle = moonGlow;
                    ctx.beginPath();
                    ctx.arc(0, 0, 35, 0, Math.PI*2);
                    ctx.fill();
                    // Moon itself
                    ctx.fillStyle = '#fef08a';
                    ctx.beginPath();
                    ctx.arc(0, 0, 20, 0, Math.PI*2);
                    ctx.fill();
                    // Mask for crescent (blends with top sky color)
                    ctx.fillStyle = skyTopColor;
                    ctx.beginPath();
                    ctx.arc(-8, -4, 20, 0, Math.PI*2);
                    ctx.fill();
                    ctx.restore();

                    // 4. BACKGROUND IMAGE (with caching, zoom, lift, and soft edges)
                    if (hasBg) {
                        let cacheKey = `${bgIndex}_${canvasHeight}`;
                        let cached = this.cachedBgs[cacheKey];
                        
                        if (!cached) {
                            // Zoomed out to 65% of the canvas height
                            let scale = (canvasHeight * 0.65) / bgImg.naturalHeight;
                            let drawWidth = Math.round(bgImg.naturalWidth * scale);
                            let drawHeight = Math.round(bgImg.naturalHeight * scale);
                            
                            // Create offscreen canvas for soft edge fading
                            let tempCanvas = document.createElement('canvas');
                            tempCanvas.width = drawWidth;
                            tempCanvas.height = drawHeight;
                            let tempCtx = tempCanvas.getContext('2d');
                            
                            tempCtx.drawImage(bgImg, 0, 0, drawWidth, drawHeight);
                            tempCtx.globalCompositeOperation = 'destination-out';
                            
                            // Fade top edge (50px)
                            let fadeTop = 50;
                            let gradTop = tempCtx.createLinearGradient(0, 0, 0, fadeTop);
                            gradTop.addColorStop(0, 'rgba(0,0,0,1)');
                            gradTop.addColorStop(1, 'rgba(0,0,0,0)');
                            tempCtx.fillStyle = gradTop;
                            tempCtx.fillRect(0, 0, drawWidth, fadeTop);
                            
                            // Fade left edge (50px)
                            let fadeLeft = 50;
                            let gradLeft = tempCtx.createLinearGradient(0, 0, fadeLeft, 0);
                            gradLeft.addColorStop(0, 'rgba(0,0,0,1)');
                            gradLeft.addColorStop(1, 'rgba(0,0,0,0)');
                            tempCtx.fillStyle = gradLeft;
                            tempCtx.fillRect(0, 0, fadeLeft, drawHeight);
                            
                            // Fade right edge (50px)
                            let fadeRight = 50;
                            let gradRight = tempCtx.createLinearGradient(drawWidth - fadeRight, 0, drawWidth, 0);
                            gradRight.addColorStop(0, 'rgba(0,0,0,0)');
                            gradRight.addColorStop(1, 'rgba(0,0,0,1)');
                            tempCtx.fillStyle = gradRight;
                            tempCtx.fillRect(drawWidth - fadeRight, 0, fadeRight, drawHeight);
                            
                            this.cachedBgs[cacheKey] = {
                                canvas: tempCanvas,
                                width: drawWidth,
                                height: drawHeight
                            };
                            cached = this.cachedBgs[cacheKey];
                        }
                        
                        // Lift image to sit higher (bottom aligns just 15px below ground level to blend with grass/stone)
                        let bgY = groundY - cached.height + 15;
                        let bgX = (800 - cached.width) / 2 - (this.camX - (this.level.totalWidth - 800) / 2) * 0.12;
                        
                        ctx.drawImage(cached.canvas, bgX, bgY);
                    }
 else {
                        // 4. PARALLAX LANDMARKS (DARMSTADT fallback)
                        ctx.save();
                        let landmarkX = -this.camX * 0.15;
                        let yOffset = groundY - 260;
                        
                        // Landmark A: Hochzeitsturm (Wedding Tower)
                        let hx = landmarkX + 280;
                        ctx.fillStyle = '#1c1340';
                        ctx.beginPath();
                        ctx.fillRect(hx, 130 + yOffset, 44, 130); // Tower base
                        for (let j = 0; j < 5; j++) {
                            let fx = hx + 1 + j * 8.4;
                            let fy = 130 + yOffset;
                            let fh = 14 - Math.abs(j - 2) * 3;
                            ctx.fillRect(fx, fy - fh, 7.5, fh + 2);
                            ctx.arc(fx + 3.75, fy - fh, 3.75, Math.PI, 0);
                            ctx.fill();
                        }
                        ctx.fillStyle = '#fef08a';
                        ctx.globalAlpha = 0.5;
                        ctx.fillRect(hx + 10, 150 + yOffset, 4, 15);
                        ctx.fillRect(hx + 30, 150 + yOffset, 4, 15);
                        ctx.fillRect(hx + 20, 180 + yOffset, 4, 25);
                        
                        // Landmark B: Schloss Darmstadt
                        ctx.fillStyle = '#181039';
                        let sx = landmarkX + 440;
                        ctx.beginPath();
                        ctx.fillRect(sx, 150 + yOffset, 160, 110);
                        ctx.fillRect(sx + 65, 110 + yOffset, 30, 40);
                        ctx.fillStyle = '#ffffff';
                        ctx.globalAlpha = 0.6;
                        ctx.beginPath();
                        ctx.arc(sx + 80, 125 + yOffset, 8, 0, Math.PI*2);
                        ctx.fill();
                        ctx.fillStyle = '#140c30';
                        ctx.beginPath();
                        ctx.moveTo(sx, 150 + yOffset);
                        ctx.lineTo(sx + 40, 135 + yOffset);
                        ctx.lineTo(sx + 65, 150 + yOffset);
                        ctx.fill();
                        ctx.beginPath();
                        ctx.moveTo(sx + 95, 150 + yOffset);
                        ctx.lineTo(sx + 120, 135 + yOffset);
                        ctx.lineTo(sx + 160, 150 + yOffset);
                        ctx.fill();
                        ctx.beginPath();
                        ctx.moveTo(sx + 65, 110 + yOffset);
                        ctx.lineTo(sx + 80, 90 + yOffset);
                        ctx.lineTo(sx + 95, 110 + yOffset);
                        ctx.fill();
                        
                        // Landmark C: Merck-Stadion floodlight masts
                        let stadiumX = landmarkX + 750;
                        ctx.strokeStyle = '#181039';
                        ctx.lineWidth = 6;
                        ctx.beginPath();
                        ctx.moveTo(stadiumX, 260 + yOffset);
                        ctx.lineTo(stadiumX - 25, 110 + yOffset);
                        ctx.stroke();
                        ctx.fillStyle = '#1c1340';
                        ctx.fillRect(stadiumX - 45, 95 + yOffset, 40, 20);
                        
                        ctx.beginPath();
                        ctx.moveTo(stadiumX + 180, 260 + yOffset);
                        ctx.lineTo(stadiumX + 155, 110 + yOffset);
                        ctx.stroke();
                        ctx.fillRect(stadiumX + 135, 95 + yOffset, 40, 20);

                        ctx.fillStyle = '#fef08a';
                        ctx.globalAlpha = 0.08;
                        ctx.beginPath();
                        ctx.moveTo(stadiumX - 25, 105 + yOffset);
                        ctx.lineTo(stadiumX - 180, 280 + yOffset);
                        ctx.lineTo(stadiumX + 80, 280 + yOffset);
                        ctx.closePath();
                        ctx.fill();
                        ctx.beginPath();
                        ctx.moveTo(stadiumX + 155, 105 + yOffset);
                        ctx.lineTo(stadiumX, 280 + yOffset);
                        ctx.lineTo(stadiumX + 260, 280 + yOffset);
                        ctx.closePath();
                        ctx.fill();

                        ctx.globalAlpha = 0.8;
                        ctx.fillStyle = '#fef08a';
                        for (let lx = 0; lx < 4; lx++) {
                            ctx.beginPath();
                            ctx.arc(stadiumX - 40 + lx * 10, 105 + yOffset, 2.5, 0, Math.PI*2);
                            ctx.fill();
                        }
                        for (let lx = 0; lx < 4; lx++) {
                            ctx.beginPath();
                            ctx.arc(stadiumX + 140 + lx * 10, 105 + yOffset, 2.5, 0, Math.PI*2);
                            ctx.fill();
                        }

                        drawLily(ctx, landmarkX + 200, 90 + yOffset, 75, 'rgba(255, 255, 255, 0.04)');
                        drawLily(ctx, landmarkX + 900, 90 + yOffset, 75, 'rgba(255, 255, 255, 0.04)');

                        ctx.restore();
                    }

                    // 5. DRAW PLATFORMS
                    ctx.save();
                    for (let plat of this.platforms) {
                        let px = plat.x - this.camX;
                        
                        if (plat.type === 'start' || plat.type === 'end' || plat.type === 'island') {
                            // Grass layer
                            ctx.fillStyle = '#10b981';
                            ctx.fillRect(px, plat.y, plat.width, 10);
                            
                            // Stone layer down to the bottom
                            ctx.fillStyle = '#4b5563';
                            ctx.fillRect(px, plat.y + 10, plat.width, canvasHeight - plat.y - 10);
                            // Stone detail line
                            ctx.fillStyle = '#374151';
                            ctx.fillRect(px, plat.y + 10, plat.width, 3);

                            // Striped flags / Banners hanging off
                            let bannerSpacing = 80;
                            for (let bx = 40; bx < plat.width - 20; bx += bannerSpacing) {
                                ctx.fillStyle = '#3b82f6'; // Blue
                                ctx.fillRect(px + bx, plat.y + 10, 20, 30);
                                ctx.fillStyle = '#ffffff'; // White stripe
                                ctx.fillRect(px + bx + 6, plat.y + 10, 8, 30);
                                // Draw miniature Lily on the banner
                                drawLily(ctx, px + bx + 10, plat.y + 22, 10, '#3b82f6', 0.9);
                            }

                            // Write text "SCHLOSSGRABEN" / "MERCK-STADION" on platform edge (only on start / end)
                            if (plat.type === 'start' || plat.type === 'end') {
                                ctx.fillStyle = 'rgba(255, 255, 255, 0.15)';
                                ctx.font = 'black 14px Figtree';
                                if (plat.type === 'start') {
                                    ctx.fillText("SCHLOSSGRABEN", px + 20, plat.y + 60);
                                } else {
                                    ctx.fillText("MERCK-STADION", px + 50, plat.y + 60);
                                }
                            }
                        } else if (plat.type === 'block') {
                            // Stacked beer crates down to the bottom
                            let crateHeight = 30;
                            let blockTotalHeight = canvasHeight - plat.y;
                            let crateCount = Math.ceil(blockTotalHeight / crateHeight);
                            for (let c = 0; c < crateCount; c++) {
                                let cy = plat.y + c * crateHeight;
                                ctx.fillStyle = (c % 2 === 0) ? '#ea580c' : '#c2410c';
                                ctx.fillRect(px, cy, plat.width, crateHeight - 2);
                                
                                ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
                                ctx.lineWidth = 1.5;
                                ctx.strokeRect(px + 2, cy + 2, plat.width - 4, crateHeight - 6);
                                
                                ctx.fillStyle = '#0f172a';
                                ctx.fillRect(px + plat.width / 2 - 8, cy + crateHeight / 2 - 4, 16, 6);
                            }
                        }
                    }
                    ctx.restore();

                    // 6. DUST PARTICLES
                    ctx.save();
                    for (let pt of this.particles) {
                        ctx.fillStyle = `rgba(255, 255, 255, ${pt.alpha})`;
                        ctx.beginPath();
                        ctx.arc(pt.x - this.camX, pt.y, pt.size, 0, Math.PI*2);
                        ctx.fill();
                    }
                    ctx.restore();

                    // 7. DRAW PLAYER
                    if (this.gameState === 'playing' || this.gameState === 'success') {
                        ctx.save();
                        let px = p.x - this.camX;
                        let py = p.y;

                        // Draw Shadow under player when grounded
                        if (p.isGrounded) {
                            ctx.fillStyle = 'rgba(0, 0, 0, 0.2)';
                            ctx.beginPath();
                            ctx.ellipse(px + p.width/2, py + p.height, 12, 3, 0, 0, Math.PI*2);
                            ctx.fill();
                        }

                        // Leg running animation
                        let legAngle = 0;
                        let handAngle = 0;
                        if (p.isGrounded) {
                            legAngle = Math.sin(this.gameTime * 0.22) * 0.7;
                            handAngle = Math.cos(this.gameTime * 0.22) * 0.6;
                        } else {
                            legAngle = (p.vy < 0) ? -0.4 : 0.4;
                            handAngle = -0.5;
                        }

                        let hipX = px + p.width / 2;
                        let hipY = py + 22;
                        let shX = px + p.width / 2;
                        let shY = py + 12;

                        ctx.strokeStyle = '#1e3a8a';
                        ctx.lineWidth = 3.5;
                        ctx.lineCap = 'round';
                        ctx.beginPath();
                        ctx.moveTo(hipX, hipY);
                        ctx.lineTo(hipX + Math.sin(legAngle) * 12, hipY + Math.cos(legAngle) * 12);
                        ctx.stroke();

                        ctx.strokeStyle = '#0c1b40';
                        ctx.beginPath();
                        ctx.moveTo(hipX, hipY);
                        ctx.lineTo(hipX - Math.sin(legAngle) * 12, hipY - Math.cos(legAngle) * 12);
                        ctx.stroke();

                        ctx.fillStyle = '#3b82f6';
                        ctx.fillRect(px, py + 10, p.width, 12);
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(px + 4, py + 10, 3, 12);
                        ctx.fillRect(px + 12, py + 10, 3, 12);

                        ctx.strokeStyle = '#3b82f6';
                        ctx.beginPath();
                        ctx.moveTo(shX, shY);
                        ctx.lineTo(shX + Math.sin(handAngle) * 10, shY + Math.cos(handAngle) * 10);
                        ctx.stroke();
                        ctx.fillStyle = '#ffedd5';
                        ctx.beginPath();
                        ctx.arc(shX + Math.sin(handAngle) * 10, shY + Math.cos(handAngle) * 10, 2.5, 0, Math.PI*2);
                        ctx.fill();

                        ctx.fillStyle = '#ffedd5';
                        ctx.beginPath();
                        ctx.arc(px + p.width/2, py + 5, 5, 0, Math.PI*2);
                        ctx.fill();
                        
                        ctx.fillStyle = '#1e3a8a';
                        ctx.beginPath();
                        ctx.arc(px + p.width/2, py + 3, 5.5, Math.PI, 0);
                        ctx.fill();
                        ctx.fillRect(px + p.width/2, py + 1.5, 6, 2);

                        ctx.lineWidth = 4;
                        ctx.lineCap = 'round';
                        ctx.strokeStyle = '#3b82f6';
                        ctx.beginPath();
                        ctx.moveTo(shX, shY - 2);
                        let waveX = shX - 14 - Math.sin(this.gameTime * 0.25) * 4;
                        let waveY = shY - 1 + Math.cos(this.gameTime * 0.25) * 3;
                        ctx.quadraticCurveTo(shX - 6, shY - 4, waveX, waveY);
                        ctx.stroke();
                        ctx.strokeStyle = '#ffffff';
                        ctx.lineWidth = 2.5;
                        ctx.beginPath();
                        ctx.moveTo(shX - 3, shY - 2.8);
                        ctx.lineTo(shX - 10, shY - 2);
                        ctx.stroke();

                        ctx.restore();
                    }

                    // 8. DRAW WATER (Schlossgraben)
                    ctx.save();
                    ctx.fillStyle = '#1d4ed8';
                    ctx.fillRect(0, waterY, 800, canvasHeight - waterY);

                    ctx.fillStyle = 'rgba(56, 189, 248, 0.4)';
                    ctx.beginPath();
                    ctx.moveTo(0, canvasHeight);
                    for (let wx = 0; wx <= 800; wx += 20) {
                        let waveY = waterY + Math.sin(wx * 0.035 + this.gameTime * 0.09) * 4.5;
                        ctx.lineTo(wx, waveY);
                    }
                    ctx.lineTo(800, canvasHeight);
                    ctx.closePath();
                    ctx.fill();

                    ctx.fillStyle = 'rgba(30, 58, 138, 0.3)';
                    ctx.beginPath();
                    ctx.moveTo(0, canvasHeight);
                    for (let wx = 0; wx <= 800; wx += 25) {
                        let waveY = waterY + 1 + Math.cos(wx * 0.025 + this.gameTime * 0.07) * 3.5;
                        ctx.lineTo(wx, waveY);
                    }
                    ctx.lineTo(800, canvasHeight);
                    ctx.closePath();
                    ctx.fill();
                    ctx.restore();

                    // 9. SPLASH PARTICLES
                    ctx.save();
                    for (let sp of this.splashParticles) {
                        ctx.fillStyle = `rgba(56, 189, 248, ${sp.alpha})`;
                        ctx.beginPath();
                        ctx.arc(sp.x - this.camX, sp.y, sp.size, 0, Math.PI*2);
                        ctx.fill();
                    }
                    ctx.restore();
                }
            }));
        });
    