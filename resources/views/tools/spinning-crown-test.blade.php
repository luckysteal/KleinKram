<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Spinning Crown Test') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <livewire:player-manager :initial-players="json_encode($names)" />

                    <div id="three-js-container" class="relative w-full h-96 mx-auto" style="min-height: 400px;"></div>
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
    <script src="https://unpkg.com/dat.gui@0.7.9/build/dat.gui.module.js"></script>
    <script type="module">
        import * as THREE from 'three';
        import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
        import { CSS2DRenderer, CSS2DObject } from 'three/examples/jsm/renderers/CSS2DRenderer.js';

        // Import dat.GUI
        import { GUI } from 'https://unpkg.com/dat.gui@0.7.9/build/dat.gui.module.js';

        let scene, camera, renderer, labelRenderer, crown, pivot, directionalLight, stickFigures = [];

        const TOTAL_CROWN_TIPS = 9; // Fixed number of tips on the crown

        // GUI controls object
        const controls = {
            crownOffsetX: -0.4, // Initial offset for crown rotation pivot
            lightIntensity: 4.0, // Increased light intensity
            lightX: 0,
            lightY: 5,
            lightZ: 10,
            stickFigureRadius: 2.48,
            stickFigureHeight: 1.3,
            stickFigureStartAngleOffset: 3.14, // This is the fixed offset for the first tip
            numPlayers: TOTAL_CROWN_TIPS // Number of active players, defaults to total tips
        };

        function init() {
            const container = document.getElementById('three-js-container');

            // Scene
            scene = new THREE.Scene();

            // Camera
            camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            camera.position.set(0, 2, 10); // Moved camera further away
            camera.lookAt(0, 0, 0);

            // Lighting
            const ambientLight = new THREE.AmbientLight(0xffffff, 1.0); // Increased ambient light
            scene.add(ambientLight);

            directionalLight = new THREE.DirectionalLight(0xffffff, controls.lightIntensity);
            directionalLight.position.set(controls.lightX, controls.lightY, controls.lightZ);
            scene.add(directionalLight);

            // Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setPixelRatio(window.devicePixelRatio);
            renderer.setSize(window.innerWidth, window.innerHeight);
            container.appendChild(renderer.domElement);

            // Label Renderer
            labelRenderer = new CSS2DRenderer();
            labelRenderer.setSize(window.innerWidth, window.innerHeight);
            labelRenderer.domElement.style.position = 'absolute';
            labelRenderer.domElement.style.top = '0px';
            labelRenderer.domElement.style.pointerEvents = 'none';
            container.appendChild(labelRenderer.domElement);

            // Create a pivot point
            pivot = new THREE.Group();
            scene.add(pivot);

            // Load Model
            const loader = new GLTFLoader();
            loader.load(
                '/models/golden_crown.glb',
                (gltf) => {
                    crown = gltf.scene;

                    // Center the crown model first
                    const box = new THREE.Box3().setFromObject(crown);
                    const center = box.getCenter(new THREE.Vector3());
                    crown.position.sub(center);

                    // Offset the crown from the pivot point
                    crown.position.x = controls.crownOffsetX;
                    pivot.add(crown);

                    createStickFigures();
                    setupGUI(); // Setup GUI after model is loaded
                    animate();
                },
                undefined,
                (error) => {
                    console.error('An error occurred while loading the crown model:', error);
                }
            );

            window.addEventListener('resize', onWindowResize, false);
        }

        function createStickFigure(name) {
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
        }

        function createStickFigures() {
            // Remove existing stick figures and their labels
            stickFigures.forEach(figure => {
                figure.children.forEach(child => {
                    if (child instanceof CSS2DObject) {
                        figure.remove(child);
                    }
                });
                pivot.remove(figure); // Remove the stick figure group from the pivot
            });
            stickFigures = [];

            const names = Array.from({length: controls.numPlayers}, (_, i) => `Player ${i + 1}`); // Dynamically generate names
            const angleBetweenTips = (2 * Math.PI) / TOTAL_CROWN_TIPS;

            names.forEach((name, playerIndex) => {
                // Calculate which of the 9 tips this player should occupy
                const tipToOccupy = Math.floor(playerIndex * (TOTAL_CROWN_TIPS / names.length));

                // Calculate the angle for this specific tip
                const angle = controls.stickFigureStartAngleOffset + tipToOccupy * angleBetweenTips;

                const x = controls.stickFigureRadius * Math.cos(angle);
                const z = controls.stickFigureRadius * Math.sin(angle);

                const stickFigure = createStickFigure(name);
                stickFigure.position.set(x, controls.stickFigureHeight, z);
                stickFigure.rotation.y = -angle; // Make them face outwards from the center

                pivot.add(stickFigure);
                stickFigures.push(stickFigure);
            });
        }

        function setupGUI() {
            const gui = new GUI();

            const crownFolder = gui.addFolder('Crown Rotation');
            crownFolder.add(controls, 'crownOffsetX', -2, 2).step(0.01).onChange((value) => {
                if (crown) crown.position.x = value;
                console.log(`Crown Offset X: ${value}`);
            });
            crownFolder.open();

            const lightFolder = gui.addFolder('Directional Light');
            lightFolder.add(controls, 'lightIntensity', 0, 5).step(0.1).onChange((value) => {
                directionalLight.intensity = value;
                console.log(`Light Intensity: ${value}`);
            });
            lightFolder.add(controls, 'lightX', -20, 20).step(0.1).onChange((value) => {
                directionalLight.position.x = value;
                console.log(`Light X: ${value}`);
            });
            lightFolder.add(controls, 'lightY', -20, 20).step(0.1).onChange((value) => {
                directionalLight.position.y = value;
                console.log(`Light Y: ${value}`);
            });
            lightFolder.add(controls, 'lightZ', -20, 20).step(0.1).onChange((value) => {
                directionalLight.position.z = value;
                console.log(`Light Z: ${value}`);
            });
            lightFolder.open();

            const stickFigureFolder = gui.addFolder('Stick Figures');
            stickFigureFolder.add(controls, 'stickFigureRadius', 0, 5).step(0.01).onChange(createStickFigures);
            stickFigureFolder.add(controls, 'stickFigureHeight', 0, 5).step(0.01).onChange(createStickFigures);
            stickFigureFolder.add(controls, 'stickFigureStartAngleOffset', 0, Math.PI * 2).step(0.01).onChange(createStickFigures);
            stickFigureFolder.add(controls, 'numPlayers', 1, TOTAL_CROWN_TIPS).step(1).onChange(createStickFigures); // Control for number of players
            stickFigureFolder.open();

            // Initial console log of values
            console.log("Initial Crown Offset X: " + controls.crownOffsetX);
            console.log("Initial Light Intensity: " + controls.lightIntensity);
            console.log("Initial Light Position: X=" + controls.lightX + ", Y=" + controls.lightY + ", Z=" + controls.lightZ);
            console.log("Initial Stick Figure Radius: " + controls.stickFigureRadius);
            console.log("Initial Stick Figure Height: " + controls.stickFigureHeight);
            console.log("Initial Stick Figure Start Angle Offset: " + controls.stickFigureStartAngleOffset);
            console.log("Initial Number of Players: " + controls.numPlayers);
        }

        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
            labelRenderer.setSize(window.innerWidth, window.innerHeight);
        }

        function animate() {
            requestAnimationFrame(animate);

            if (pivot) {
                pivot.rotation.y += 0.005;
            }

            renderer.render(scene, camera);
            labelRenderer.render(scene, camera);
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
    @endpush
</x-app-layout>