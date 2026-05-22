<x-app-layout>

    <div class="flex-grow flex flex-col w-full relative" x-data="crownGame()">
        <div class="flex-grow flex flex-col w-full">

            <div class="flex-grow flex flex-col bg-white dark:bg-gray-800 transition-colors duration-300">
                <style>
                    #game-container {
                        position: relative;
                        width: 100%;
                        flex-grow: 1;
                        min-height: 400px;
                        background: radial-gradient(circle at center, #1a1a2e 0%, #0d0d1a 100%);
                        font-family: 'Montserrat', sans-serif;
                        color: #fff;
                        overflow: hidden;
                    }

                    #canvas-container {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                    }

                    .hud {
                        position: absolute;
                        bottom: 40px;
                        left: 50%;
                        transform: translateX(-50%);
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        z-index: 10;
                    }

                    #spin-button {
                        padding: 15px 40px;
                        font-size: 24px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 2px;
                        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
                        border: none;
                        border-radius: 50px;
                        color: white;
                        cursor: pointer;
                        box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
                        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        outline: none;
                    }

                    #spin-button:hover {
                        transform: translateY(-5px) scale(1.05);
                        box-shadow: 0 15px 30px rgba(99, 102, 241, 0.6);
                    }

                    #spin-button:active {
                        transform: translateY(-2px) scale(0.98);
                    }

                    #spin-button:disabled {
                        opacity: 0.6;
                        cursor: not-allowed;
                        transform: none;
                    }

                    #winner-display {
                        margin-top: 20px;
                        font-size: 32px;
                        font-weight: 700;
                        text-align: center;
                        min-height: 48px;
                        text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
                        opacity: 0;
                        transform: translateY(10px);
                        transition: all 0.5s ease;
                    }

                    #winner-display.show {
                        opacity: 1;
                        transform: translateY(0);
                    }

                    .label {
                        color: #fff;
                        padding: 4px 12px;
                        background: rgba(0, 0, 0, 0.6);
                        backdrop-filter: blur(4px);
                        border-radius: 20px;
                        font-size: 14px;
                        font-weight: 600;
                        border: 1px solid rgba(255, 255, 255, 0.2);
                        pointer-events: none;
                        white-space: nowrap;
                        position: relative;
                        z-index: 1;
                    }

                    .crown-pointer {
                        width: 0;
                        height: 0;
                        border-left: 10px solid transparent;
                        border-right: 10px solid transparent;
                        border-top: 28px solid #ff0000;
                        filter: drop-shadow(0 0 12px rgba(255, 0, 0, 0.75));
                        transform: translateY(2px);
                        pointer-events: none;
                        position: relative;
                        z-index: 2;
                    }

                    .winner-crown {
                        position: absolute;
                        top: 20px;
                        left: 50%;
                        transform: translateX(-50%);
                        text-align: center;
                    }

                    .winner-crown h1 {
                        font-size: 18px;
                        letter-spacing: 4px;
                        color: rgba(255, 255, 255, 0.3);
                        text-transform: uppercase;
                        margin: 0;
                    }
                </style>

                <x-game-header reset="resetGame()">
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-[0.3em] italic">{{ __('Spinning Crown') }}</h3>
                </x-game-header>

                <div id="game-container" 
                    @winner-calculated.window="showWinner($event.detail)"
                    x-init="initGame()"
                >
                    <div id="canvas-container"></div>
                    
                    @if(empty($names))
                    <div id="no-players-overlay" class="absolute inset-0 z-30 bg-black/80 backdrop-blur-md overflow-y-auto p-4 sm:p-6 text-center">
                        <div class="min-h-full flex flex-col items-center justify-center py-8">
                        <div class="bg-indigo-600/20 border border-indigo-500/50 p-8 rounded-2xl max-w-md">
                            <svg class="w-16 h-16 text-indigo-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <div>
                                <h3 class="text-xl font-bold dark:text-white">{{ __('No Players Found') }}</h3>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('You need at least one player to spin the crown!') }}</p>
                            </div>
                            <a href="{{ route('tools.player-selection') }}" class="inline-block px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition-transform hover:scale-105">
                                {{ __('Add Players Now') }}
                            </a>
                        </div>
                    </div>
                </div>
                    @endif

                    <!-- Winner Overlay (Standard and LMS) -->
                    <div x-show="gameState === 'winner' || gameState === 'overall_winner' || gameState === 'eliminated' || gameState === 'picked'" 
                        x-transition:enter="transition ease-out duration-300" 
                        x-transition:enter-start="opacity-0 scale-95" 
                        x-transition:enter-end="opacity-100 scale-100" 
                        class="absolute inset-0 z-50 bg-indigo-950/95 overflow-y-auto p-4 sm:p-8 text-center shadow-2xl backdrop-blur-xl transition-colors duration-300">
                        <div class="min-h-full flex flex-col items-center justify-center py-8">
                        <div class="relative mb-8">
                            <template x-if="gameState === 'overall_winner'">
                                <div class="absolute inset-0 bg-amber-400 blur-3xl opacity-30 scale-150 rounded-full animate-pulse"></div>
                            </template>
                            <template x-if="gameState === 'winner' || gameState === 'picked'">
                                <div class="absolute inset-0 bg-indigo-400 blur-3xl opacity-20 scale-150 rounded-full animate-pulse"></div>
                            </template>
                            <template x-if="gameState === 'eliminated'">
                                <div class="absolute inset-0 bg-rose-600 blur-3xl opacity-20 scale-150 rounded-full animate-pulse"></div>
                            </template>
                            <div class="relative w-28 h-28 sm:w-40 sm:h-40 bg-white dark:bg-indigo-900 rounded-full flex items-center justify-center border-4 sm:border-8 border-indigo-700 shadow-[0_30px_60px_rgba(0,0,0,0.5)]">
                                <i class="fas fa-crown text-4xl sm:text-7xl text-amber-400 animate-bounce"></i>
                            </div>
                        </div>
                        
                        <div class="mb-8 sm:mb-12">
                            <template x-if="gameState === 'overall_winner'">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-amber-500 uppercase tracking-[0.2em] sm:tracking-[0.5em] mb-4">{{ __('ULTIMATE CHAMPION') }}</h2>
                                    <div class="text-4xl sm:text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_0_20px_rgba(251,191,36,0.4)]" x-text="winnerName"></div>
                                    <p class="text-amber-400 text-base sm:text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic">{{ __('Last Man Standing!') }}</p>
                                </div>
                            </template>
                            <template x-if="gameState === 'eliminated'">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-rose-500 uppercase tracking-[0.2em] sm:tracking-[0.5em] mb-4">{{ __('CHRONICLE OF FATE') }}</h2>
                                    <div class="text-4xl sm:text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_0_20px_rgba(225,29,72,0.4)]" x-text="eliminatedPlayer"></div>
                                    <p class="text-rose-400 text-sm sm:text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic">{{ __('You are Eliminated!') }}</p>
                                </div>
                            </template>
                            <template x-if="(gameState === 'picked' || gameState === 'winner') && !lmsActive">
                                <div>
                                    <h2 class="text-lg sm:text-xl font-bold text-amber-500 uppercase tracking-[0.2em] sm:tracking-[0.5em] mb-4">{{ __('CHRONICLE OF FATE') }}</h2>
                                    <div class="text-4xl sm:text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_0_20px_rgba(251,191,36,0.4)]" x-text="lastPickedPlayer"></div>
                                    <p class="text-amber-400 text-base sm:text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic">{{ __('The Chosen One!') }}</p>
                                </div>
                            </template>
                        </div>

                        <template x-if="gameState === 'overall_winner'">
                            <button @click="resetRound()" class="w-full max-w-sm py-4 sm:py-6 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black text-xl sm:text-2xl rounded-2xl sm:rounded-3xl shadow-[0_20px_40px_rgba(251,191,36,0.3)] transition-all hover:scale-105 uppercase tracking-tighter">
                                {{ __('NEW ROUND') }}
                            </button>
                        </template>
                        <template x-if="gameState === 'eliminated' || (gameState === 'picked' || gameState === 'winner')">
                            <button @click="resetGame()" class="w-full max-w-sm py-4 sm:py-6 bg-amber-400 hover:bg-amber-300 text-amber-950 font-black text-xl sm:text-2xl rounded-2xl sm:rounded-3xl shadow-[0_20px_40px_rgba(251,191,36,0.3)] transition-all hover:scale-105 uppercase tracking-tighter">
                                <span x-text="lmsActive ? '{{ __('NEXT ROUND') }}' : '{{ __('NEXT SPIN') }}'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                    <div class="hud" x-show="gameState === 'playing'" x-transition>
                        <button id="spin-button" {{ empty($names) ? 'disabled' : '' }}>{{ __('Spin') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simplified module imports for better mobile compatibility using esm.sh which handles resolution -->
    <script type="module">
        import * as THREE from 'https://esm.sh/three@0.128.0';
        import { GLTFLoader } from 'https://esm.sh/three@0.128.0/examples/jsm/loaders/GLTFLoader.js';
        import { CSS2DRenderer, CSS2DObject } from 'https://esm.sh/three@0.128.0/examples/jsm/renderers/CSS2DRenderer.js';
        import * as TWEEN from 'https://esm.sh/@tweenjs/tween.js@18.6.4'; 

        // Interface for Alpine to talk to Three.js
        window.crownScene = {
            spin: null,
            reset: null,
            init: null
        };

        let scene, camera, renderer, labelRenderer, crown, pivot, stickFigures = [];
        let pointer;
        let players = @json($names);
        let spinning = false;
        let isReadyState = false;
        const TOTAL_CROWN_TIPS = 9;
        const crownOffsetX = -0.4;
        const stickFigureRadius = 2.48;
        const stickFigureHeight = 1.3;
        const stickFigureStartAngleOffset = 3.14;

        function init() {
            const container = document.getElementById('canvas-container');
            const gameContainer = document.getElementById('game-container');

            // Scene
            scene = new THREE.Scene();

            // Camera - with robust sizing
            const width = gameContainer.clientWidth || window.innerWidth;
            const height = gameContainer.clientHeight || 600;
            camera = new THREE.PerspectiveCamera(60, width / height, 0.1, 1000);
            camera.position.set(0, 5, 12);
            camera.lookAt(0, 0, 0);

            const ambientLight = new THREE.AmbientLight(0xffffff, 10.0);
            scene.add(ambientLight);

            // "The Sun" - powerful directional light angled upwards from the bottom front
            const sunLight = new THREE.DirectionalLight(0xffffff, 20.0);
            sunLight.position.set(0, -5, 15);
            scene.add(sunLight);

            // Point lights encircling the crown for multi-angle flooding from below
            for (let i = 0; i < 6; i++) {
                const angle = (i / 6) * Math.PI * 2;
                const pl = new THREE.PointLight(0xffffff, 30.0, 100);
                pl.position.set(Math.cos(angle) * 15, -5, Math.sin(angle) * 15);
                scene.add(pl);
            }

            // Camera-positioned light for front-facing brightness (Flash)
            const camLight = new THREE.PointLight(0xffffff, 40.0, 100);
            camLight.position.set(0, -5, 12);
            scene.add(camLight);

            // Renderer with mobile fail-safes
            try {
                renderer = new THREE.WebGLRenderer({ 
                    antialias: window.devicePixelRatio < 2, // Disable antialias on high-DPI (retina) for performance
                    alpha: true,
                    powerPreference: "high-performance"
                });
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2)); // Cap pixel ratio for mobile performance
                
                const width = gameContainer.clientWidth || window.innerWidth;
                const height = gameContainer.clientHeight || 600;
                renderer.setSize(width, height);
                
                renderer.toneMapping = THREE.ACESFilmicToneMapping;
                renderer.toneMappingExposure = 1.0;
                renderer.shadowMap.enabled = false;
                renderer.domElement.style.zIndex = '1';
                container.appendChild(renderer.domElement);
            } catch (e) {
                console.error("WebGL failed", e);
                gameContainer.innerHTML = '<div class="absolute inset-0 flex items-center justify-center text-white p-8 text-center bg-black/50">Your device does not support WebGL or it is disabled.</div>';
                return;
            }

            // Label Renderer
            labelRenderer = new CSS2DRenderer();
            labelRenderer.setSize(gameContainer.clientWidth, gameContainer.clientHeight);
            labelRenderer.domElement.style.position = 'absolute';
            labelRenderer.domElement.style.top = '0px';
            labelRenderer.domElement.style.pointerEvents = 'none';
            labelRenderer.domElement.style.zIndex = '0';
            container.appendChild(labelRenderer.domElement);

            // Pivot Point
            pivot = new THREE.Group();
            scene.add(pivot);

            // Load Model
            const loader = new GLTFLoader();
            loader.load(
                '/models/golden_crown.glb',
                (gltf) => {
                    crown = gltf.scene;

                    // Center the crown model
                    const box = new THREE.Box3().setFromObject(crown);
                    const center = box.getCenter(new THREE.Vector3());
                    crown.position.sub(center);

                    // Refine materials
                    crown.traverse((child) => {
                        if (child.isMesh) {
                            child.material.metalness = 1.0;
                            child.material.roughness = 0.2;
                        }
                    });

                    // Offset the crown from the pivot point
                    crown.position.x = crownOffsetX;
                    pivot.add(crown);

                    setupPlayers();
                },
                undefined,
                (error) => console.error('Error loading model:', error)
            );

            animate();

            // Bright pure red arrow - rendered in the same overlay layer as the labels
            // so it can stay above the name tags reliably.
            const pointerDiv = document.createElement('div');
            pointerDiv.className = 'crown-pointer';
            pointer = new CSS2DObject(pointerDiv);
            pointer.renderOrder = 1000;
            pointer.position.set(0, 3.5, 3.8);
            scene.add(pointer);

            window.addEventListener('resize', onWindowResize, false);
            
            const spinBtn = document.getElementById('spin-button');
            if (spinBtn) {
                spinBtn.addEventListener('click', handleButtonClick);
                spinBtn.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    handleButtonClick();
                }, { passive: false });
            }

            // Expose logic to Alpine
            window.crownScene.spin = spin;
            window.crownScene.reset = () => {
                isReadyState = false;
                if (spinBtn) {
                   spinBtn.innerText = '{{ __('Spin') }}';
                   spinBtn.disabled = false;
                }
                // Show pointer again when resetting
                if (pointer) {
                    pointer.visible = true;
                }
            };

            // Force initial sizing to be correct on mobile
            setTimeout(onWindowResize, 100);
        }

        function getPlayerColor(index, totalPlayers) {
            // Using a more vibrant palette
            const hue = (index * 137.5) % 360; // Golden angle for better distribution
            return new THREE.Color(`hsl(${hue}, 85%, 60%)`);
        }

        function createStickFigure(name, color) {
            const group = new THREE.Group();

            const bodyColor = new THREE.MeshBasicMaterial({ color: color });
            const limbColor = new THREE.MeshBasicMaterial({ color: color.clone().multiplyScalar(0.85) });
            const headColor = new THREE.MeshBasicMaterial({ color: color.clone().offsetHSL(0, 0, 0.05) });

            const headGeometry = new THREE.SphereGeometry(0.12, 16, 16);
            const head = new THREE.Mesh(headGeometry, headColor);
            head.position.y = 0.45;
            group.add(head);

            const bodyGeometry = new THREE.CylinderGeometry(0.06, 0.06, 0.45, 16);
            const body = new THREE.Mesh(bodyGeometry, bodyColor);
            body.position.y = 0.15;
            group.add(body);

            const armGeometry = new THREE.CylinderGeometry(0.04, 0.04, 0.32, 10);
            const leftArm = new THREE.Mesh(armGeometry, limbColor);
            leftArm.position.set(-0.16, 0.24, 0);
            leftArm.rotation.z = 0.95;
            group.add(leftArm);

            const rightArm = new THREE.Mesh(armGeometry, limbColor);
            rightArm.position.set(0.16, 0.24, 0);
            rightArm.rotation.z = -0.95;
            group.add(rightArm);

            const legGeometry = new THREE.CylinderGeometry(0.042, 0.042, 0.4, 10);
            const leftLeg = new THREE.Mesh(legGeometry, limbColor);
            leftLeg.position.set(-0.1, -0.18, 0);
            leftLeg.rotation.z = 0.22;
            group.add(leftLeg);

            const rightLeg = new THREE.Mesh(legGeometry, limbColor);
            rightLeg.position.set(0.1, -0.18, 0);
            rightLeg.rotation.z = -0.22;
            group.add(rightLeg);

            const nameDiv = document.createElement('div');
            nameDiv.className = 'label';
            nameDiv.textContent = name;
            const label = new CSS2DObject(nameDiv);
            label.position.set(0, 0.75, 0);
            group.add(label);

            return group;
        }

        function setupPlayers() {
            // Clear existing figures from pivot safely
            if (pivot) {
                for (let i = pivot.children.length - 1; i >= 0; i--) {
                    const child = pivot.children[i];
                    // Keep the crown model, but remove stick figures
                    if (child !== crown) {
                        pivot.remove(child);
                    }
                }
            }
            stickFigures = [];

            // Explicitly remove any orphaned label DOM elements
            // This fixes the bug where labels become "static" when figures are removed from the scene
            document.querySelectorAll('.label').forEach(el => el.remove());

            if (!players || players.length === 0) return;

            const angleBetweenTips = (2 * Math.PI) / TOTAL_CROWN_TIPS;

            players.forEach((name, i) => {
                const tipIndex = Math.floor(i * (TOTAL_CROWN_TIPS / players.length));
                // Tip angle relative to crown center
                const angle = stickFigureStartAngleOffset + tipIndex * angleBetweenTips;

                const x = stickFigureRadius * Math.cos(angle);
                const z = stickFigureRadius * Math.sin(angle);

                const figure = createStickFigure(name, getPlayerColor(i, players.length));
                figure.position.set(x, stickFigureHeight, z);
                // Initial rotation - will be updated in animate() to face camera
                figure.rotation.y = 0; 
                
                // Store data for winner calculation
                figure.userData.angle = angle;
                figure.userData.name = name;

                pivot.add(figure);
                stickFigures.push(figure);
            });
        }

        function handleButtonClick() {
            if (spinning || players.length === 0) return;
            const btn = document.getElementById('spin-button');
            if (!btn) return;

            if (isReadyState) {
                isReadyState = false;
                btn.innerText = '{{ __('Spin') }}';
                return;
            }

            spin();
        }

        function spin() {
            spinning = true;
            const btn = document.getElementById('spin-button');
            if (btn) btn.disabled = true;

            const spinDuration = 14000; // Slower, more dramatic
            const randomRotation = (Math.random() * 4 + 8) * Math.PI * 2 + (Math.random() * Math.PI * 2);
            const targetFinalRotation = pivot.rotation.y + randomRotation;

            new TWEEN.Tween(pivot.rotation)
                .to({ y: targetFinalRotation }, spinDuration)
                .easing(TWEEN.Easing.Exponential.Out) // More dramatic slow-down
                .onComplete(() => {
                    spinning = false;
                    
                    // Determine which player is closest to the camera
                    let closestPlayer = null;
                    let minDistance = Infinity;

                    stickFigures.forEach(figure => {
                        const worldPos = new THREE.Vector3();
                        figure.getWorldPosition(worldPos);
                        const dist = worldPos.distanceTo(camera.position);
                        if (dist < minDistance) {
                            minDistance = dist;
                            closestPlayer = figure.userData.name;
                        }
                    });

                    const winner = closestPlayer;
                    
                    // Notify Alpine via standard DOM event
                    const gameEl = document.getElementById('game-container');
                    if (gameEl) {
                        window.dispatchEvent(new CustomEvent('winner-calculated', { detail: winner }));
                    }
                    
                    isReadyState = true;
                    if (btn) {
                        btn.innerText = 'Ready';
                        btn.disabled = false;
                    }
                    
                    // Hide pointer during winner screen
                    if (pointer) {
                        pointer.visible = false;
                    }
                    
                    saveWinner(winner);
                })
                .start();
        }

        function saveWinner(winner) {
            console.log('saveWinner called with winner:', winner);
            fetch('/tools/save-winner', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ winner: winner })
            }).then(response => response.json())
            .then(data => {
                console.log('Fetch request successful');
                if (data.success && data.names) {
                    // Only re-setup players if the list has actually changed (e.g. elimination)
                    const namesChanged = JSON.stringify(players) !== JSON.stringify(data.names);
                    players = data.names;
                    if (namesChanged && typeof setupPlayers === 'function') {
                        setupPlayers();
                    }
                }
                try {
                    const noWinsText = document.getElementById('no-fails-text');
                    console.log('noWinsText element:', noWinsText);
                    if (noWinsText) {
                        noWinsText.classList?.add('hidden');
                        if (noWinsText.style) {
                            noWinsText.style.display = 'none';
                        }
                    }
                    
                    let list = document.getElementById('scoreboard-list');
                    console.log('scoreboard-list element:', list);
                    if (!list) {
                        console.log('scoreboard-list not found, returning');
                        return;
                    }
                    
                    if (list.classList) {
                        list.classList.remove('hidden');
                    }

                    let existingLi = list.querySelector(`li[data-player="${winner}"]`);

                    if (existingLi) {
                        let countSpan = existingLi.querySelector('.win-count');
                        if (countSpan) {
                            let currentWins = parseInt(countSpan.innerText);
                            countSpan.innerText = (currentWins + 1) + ' {{ __('FAILS') }}';
                        }
                    } else {
                        let li = document.createElement('li');
                        li.className = 'flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600 group hover:border-amber-400 transition-all duration-300 hover:shadow-md';
                        li.setAttribute('data-player', winner);
                        let iteration = list.querySelectorAll('li').length + 1;
                        li.innerHTML = `
                            <div class="flex items-center gap-4">
                                <span class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-700 dark:text-amber-400 font-black text-sm">
                                    ${iteration}
                                </span>
                                <span class="text-gray-800 dark:text-gray-100 font-extrabold text-lg">${winner}</span>
                            </div>
                            <span class="bg-amber-500 text-white px-4 py-1.5 rounded-full text-xs font-black shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform win-count">1 {{ __('FAILS') }}</span>`;
                        list.appendChild(li);
                    }
                    
                    let items = Array.from(list.querySelectorAll('li'));
                    items.sort((a, b) => {
                        let countA = parseInt(a.querySelector('.win-count')?.innerText || 0);
                        let countB = parseInt(b.querySelector('.win-count')?.innerText || 0);
                        return countB - countA;
                    });
                    list.innerHTML = '';
                    items.forEach((item, index) => {
                        let spanElem = item.querySelector('div span:first-child');
                        if (spanElem) {
                            spanElem.innerText = index + 1;
                        }
                        list.appendChild(item);
                    });

                    console.log('Scoreboard updated but not opened automatically');
                } catch (err) {
                    console.error('Error updating scoreboard:', err);
                }

            }).catch(e => console.error('Failed to save winner', e));
        }

        function onWindowResize() {
            const gameContainer = document.getElementById('game-container');
            camera.aspect = gameContainer.clientWidth / gameContainer.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(gameContainer.clientWidth, gameContainer.clientHeight);
            labelRenderer.setSize(gameContainer.clientWidth, gameContainer.clientHeight);
        }

        function animate() {
            requestAnimationFrame(animate);
            if (TWEEN) TWEEN.update(); // Guard TWEEN update
            renderer.render(scene, camera);
            labelRenderer.render(scene, camera);
            
            // Make stick figures face the camera
            stickFigures.forEach(figure => {
                // The figure is a child of the pivot.
                // We want the figure's world rotation to face the camera.
                // A simple trick: set the figure's world rotation to match camera direction
                // Or just compensate for the pivot's rotation.
                
                // Get world position of figure
                const worldPos = new THREE.Vector3();
                figure.getWorldPosition(worldPos);
                
                // Vector from figure to camera (planar)
                const dx = camera.position.x - worldPos.x;
                const dz = camera.position.z - worldPos.z;
                const worldAngle = Math.atan2(dx, dz);
                
                // The figure's local rotation.y + pivot.rotation.y = worldAngle
                figure.rotation.y = worldAngle - pivot.rotation.y;
            });

            if (pointer) {
                pointer.position.y = 3.5 + Math.sin(Date.now() * 0.004) * 0.15;
            }

            // Idle rotation when not spinning or in ready state
            if (!spinning && !isReadyState) {
                pivot.rotation.y += 0.002;
            }
        }

        init();
    </script>

    <script>
        // Alpine Component for Crown Game UI (Decoupled from Three.js module to prevent blocking failures)
        document.addEventListener('alpine:init', () => {
            console.log('Alpine initialized');
            Alpine.data('crownGame', () => ({
                gameState: 'playing', // playing, winner, overall_winner
                winnerName: '',
                eliminatedPlayer: '',
                lastPickedPlayer: '',
                lmsActive: {{ $lmsActive ? 'true' : 'false' }},

                initGame() {
                    console.log('initGame called');
                    @if(isset($overallWinner))
                        this.winnerName = '{{ $overallWinner }}';
                        this.gameState = 'overall_winner';
                    @endif
                },

                showWinner(name) {
                    console.log('showWinner called with name:', name);
                    this.winnerName = name;
                    this.lastPickedPlayer = name;
                    this.eliminatedPlayer = name;
                    
                    if (this.lmsActive) {
                        this.gameState = 'eliminated';
                    } else {
                        this.gameState = 'winner';
                    }
                    
                    if (this.lmsActive) {
                        // After delay, reload to check for overall winner
                        setTimeout(() => {
                           // Actually, saveWinner logic in backend checks for overall winner.
                           // We need to fetch current round status.
                           this.checkLmsStatus();
                        }, 2000);
                    }
                },

                checkLmsStatus() {
                    // Simple approach: reload after saving.
                    // The saveWinner function is called in the module script.
                },

                resetGame() {
                    if (this.lmsActive) {
                        window.location.reload(); // Reload to get updated player list (minus eliminated)
                        return;
                    }
                    console.log('resetGame called');
                    this.gameState = 'playing';
                    this.winnerName = '';
                    this.lastPickedPlayer = '';
                    this.eliminatedPlayer = '';
                    if (window.crownScene && window.crownScene.reset) {
                        window.crownScene.reset();
                    }
                },

                resetRound() {
                    fetch('{{ route('tools.reset-lms') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => window.location.reload());
                }
            }));
        });
    </script>
</x-app-layout>

