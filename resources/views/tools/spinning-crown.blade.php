<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Spinning Crown') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <livewire:player-manager :initial-players="json_encode($names)" />

                    <div x-data="spinningCrownGame($wire.entangle('players'))" x-init="init()" class="mt-8 text-center">
                        <h3 class="text-lg font-semibold mb-4">Spin the Crown!</h3>
                        <div id="three-js-container" class="relative w-full h-96 mx-auto" style="min-height: 400px;"></div>
                        <button @click="spin()" :disabled="spinning" class="px-6 py-3 bg-purple-600 text-white rounded-md mt-4 text-xl">
                            <span x-show="!spinning">Spin!</span>
                            <span x-show="spinning">Spinning...</span>
                        </button>

                        <div x-show="winner" class="mt-4 text-2xl font-bold text-green-700">
                            Winner: <span x-text="winner"></span> has to pay!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script type="importmap">
    {
        "imports": {
            "three": "https://unpkg.com/three@0.128.0/build/three.module.js",
            "three/examples/jsm/loaders/GLTFLoader.js": "https://unpkg.com/three@0.128.0/examples/jsm/loaders/GLTFLoader.js",
            "three/examples/jsm/renderers/CSS2DRenderer.js": "https://unpkg.com/three@0.128.0/examples/jsm/renderers/CSS2DRenderer.js"
        }
    }
    </script>
    <script src="https://unpkg.com/@tweenjs/tween.js@latest/dist/tween.umd.js"></script>
    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
        import { CSS2DRenderer, CSS2DObject } from 'three/examples/jsm/renderers/CSS2DRenderer.js';

        document.addEventListener('alpine:init', () => {
            Alpine.data('spinningCrownGame', (initialNames) => ({
                players: initialNames || [],
                spinning: false,
                winner: null,
                spinDuration: 5, // seconds
                scene: null,
                camera: null,
                renderer: null,
                labelRenderer: null,
                crown: null,
                pivot: null, // Add pivot to Alpine data
                directionalLight: null, // Add directionalLight to Alpine data
                stickFigures: [],
                pointer: null,

                // Fixed crown and stick figure settings
                TOTAL_CROWN_TIPS: 9,
                crownOffsetX: -0.4,
                lightIntensity: 4.0,
                lightX: 0,
                lightY: 5,
                lightZ: 10,
                stickFigureRadius: 2.48,
                stickFigureHeight: 1.3,
                stickFigureStartAngleOffset: 3.14,

                async init() {
                    this.setupThreeJs();
                    await this.loadCrownModel(); // Ensure model is loaded before creating stick figures
                    this.updatePlayers(this.players);

                    // Watch for changes in the players array from Livewire
                    this.$watch('players', (newPlayers) => this.updatePlayers(newPlayers));

                    this.animate();
                },

                setupThreeJs() {
                    const container = document.getElementById('three-js-container');
                    const width = container.clientWidth;
                    const height = container.clientHeight;

                    this.scene = new THREE.Scene();

                    this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
                    this.camera.position.set(0, 2, 10);
                    this.camera.lookAt(0, 0, 0);

                    // Lighting
                    const ambientLight = new THREE.AmbientLight(0xffffff, 1.0);
                    this.scene.add(ambientLight);

                    this.directionalLight = new THREE.DirectionalLight(0xffffff, this.lightIntensity);
                    this.directionalLight.position.set(this.lightX, this.lightY, this.lightZ);
                    this.scene.add(this.directionalLight);

                    this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
                    this.renderer.setPixelRatio(window.devicePixelRatio);
                    this.renderer.setSize(width, height);
                    container.appendChild(this.renderer.domElement);

                    this.labelRenderer = new CSS2DRenderer();
                    this.labelRenderer.setSize(width, height);
                    this.labelRenderer.domElement.style.position = 'absolute';
                    this.labelRenderer.domElement.style.top = '0px';
                    this.labelRenderer.domElement.style.pointerEvents = 'none';
                    container.appendChild(this.labelRenderer.domElement);

                    // Create a pivot point
                    this.pivot = new THREE.Group();
                    this.scene.add(this.pivot);
                },

                loadCrownModel() {
                    return new Promise((resolve, reject) => {
                        const loader = new GLTFLoader();
                        loader.load(
                            '/models/golden_crown.glb',
                            (gltf) => {
                                this.crown = gltf.scene;

                                // Center the crown model first
                                const box = new THREE.Box3().setFromObject(this.crown);
                                const center = box.getCenter(new THREE.Vector3());
                                this.crown.position.sub(center);

                                // Offset the crown from the pivot point
                                this.crown.position.x = this.crownOffsetX;
                                this.pivot.add(this.crown);
                                resolve();
                            },
                            undefined,
                            (error) => {
                                console.error('An error occurred while loading the crown model:', error);
                                reject(error);
                            }
                        );
                    });
                },

                createStickFigure(name) {
                    const group = new THREE.Group();

                    const headGeometry = new THREE.SphereGeometry(0.1, 16, 16);
                    const headMaterial = new THREE.MeshBasicMaterial({ color: 0x333333 });
                    const head = new THREE.Mesh(headGeometry, headMaterial);
                    head.position.y = 0.4;
                    group.add(head);

                    const bodyGeometry = new THREE.CylinderGeometry(0.05, 0.05, 0.4, 16);
                    const bodyMaterial = new THREE.MeshBasicMaterial({ color: 0x333333 });
                    const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
                    body.position.y = 0.15;
                    group.add(body);

                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'label';
                    nameDiv.textContent = name;
                    const label = new CSS2DObject(nameDiv);
                    label.position.set(0, 0.6, 0);
                    group.add(label);

                    return group;
                },

                updatePlayers(names) {
                    this.players = names;
                    // Remove existing stick figures and their labels
                    this.stickFigures.forEach(figure => {
                        figure.children.forEach(child => {
                            if (child instanceof CSS2DObject) {
                                figure.remove(child);
                            }
                        });
                        this.pivot.remove(figure); // Remove the stick figure group from the pivot
                    });
                    this.stickFigures = [];

                    if (!names || names.length === 0) return;

                    const angleBetweenTips = (2 * Math.PI) / this.TOTAL_CROWN_TIPS;

                    names.forEach((name, playerIndex) => {
                        // Calculate which of the 9 tips this player should occupy
                        const tipToOccupy = Math.floor(playerIndex * (this.TOTAL_CROWN_TIPS / names.length));

                        // Calculate the angle for this specific tip
                        const angle = this.stickFigureStartAngleOffset + tipToOccupy * angleBetweenTips;

                        const x = this.stickFigureRadius * Math.cos(angle);
                        const z = this.stickFigureRadius * Math.sin(angle);

                        const stickFigure = this.createStickFigure(name);
                        stickFigure.position.set(x, this.stickFigureHeight, z);
                        stickFigure.rotation.y = -angle; // Make them face outwards from the center

                        this.pivot.add(stickFigure);
                        this.stickFigures.push(stickFigure);
                    });
                },

                spin() {
                    if (this.spinning || this.players.length === 0) return;

                    this.spinning = true;
                    this.winner = null;

                    const randomSpins = Math.floor(Math.random() * 5) + 3; // 3 to 7 full spins
                    const targetPlayerIndex = Math.floor(Math.random() * this.players.length);

                    // Calculate the target angle based on the actual tip positions
                    const angleBetweenTips = (2 * Math.PI) / this.TOTAL_CROWN_TIPS;
                    const targetTipIndex = Math.floor(targetPlayerIndex * (this.TOTAL_CROWN_TIPS / this.players.length));
                    const targetAngle = this.stickFigureStartAngleOffset + targetTipIndex * angleBetweenTips;

                    const currentPivotRotationY = this.pivot.rotation.y;
                    const finalPivotRotationY = currentPivotRotationY + (randomSpins * 2 * Math.PI) + (targetAngle - (currentPivotRotationY % (2 * Math.PI)));

                    new TWEEN.Tween(this.pivot.rotation)
                        .to({ y: finalPivotRotationY }, this.spinDuration * 1000)
                        .easing(TWEEN.Easing.Quadratic.Out)
                        .onComplete(() => {
                            this.spinning = false;
                            this.determineWinner();
                        })
                        .start();
                },

                determineWinner() {
                    const normalizedPivotRotationY = (this.pivot.rotation.y % (2 * Math.PI) + (2 * Math.PI)) % (2 * Math.PI);

                    let closestPlayer = null;
                    let minDiff = Math.PI * 2;

                    this.players.forEach((player, playerIndex) => {
                        const angleBetweenTips = (2 * Math.PI) / this.TOTAL_CROWN_TIPS;
                        const tipToOccupy = Math.floor(playerIndex * (this.TOTAL_CROWN_TIPS / this.players.length));
                        const playerTargetAngle = this.stickFigureStartAngleOffset + tipToOccupy * angleBetweenTips;

                        const effectivePlayerAngle = (playerTargetAngle - normalizedPivotRotationY + (2 * Math.PI)) % (2 * Math.PI);

                        let diff = Math.abs(effectivePlayerAngle - 0); // Pointer is at 0 degrees
                        if (diff > Math.PI) {
                            diff = (2 * Math.PI) - diff;
                        }

                        if (diff < minDiff) {
                            minDiff = diff;
                            closestPlayer = player;
                        }
                    });

                    this.winner = closestPlayer || 'No one';
                    fetch('/tools/save-winner', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ winner: this.winner })
                    });
                },

                animate() {
                    requestAnimationFrame(() => this.animate());
                    this.renderer.render(this.scene, this.camera);
                    this.labelRenderer.render(this.scene, this.camera);
                    TWEEN.update();
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>