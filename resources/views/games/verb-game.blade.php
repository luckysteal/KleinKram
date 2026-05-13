<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verb-Spiel</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fredoka:500,600,700&display=swap" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: 'Fredoka', sans-serif;
            background-color: #f6d32d;
            background-image: radial-gradient(#e5bc1a 1px, transparent 1px);
            background-size: 20px 20px;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .notebook-card {
            box-shadow: 4px 4px 0px rgba(0,0,0,0.2);
            border-radius: 8px;
            position: relative;
            transition: all 0.3s ease;
            z-index: 30;
        }

        .notebook-card::before {
            content: '';
            position: absolute;
            top: 5px;
            left: -5px;
            height: calc(100% - 10px);
            width: 15px;
            background-image: radial-gradient(circle, #888 2px, transparent 2px);
            background-size: 10px 10px;
            z-index: 31;
        }

        .die {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            font-weight: bold;
            box-shadow: 4px 4px 0px rgba(0,0,0,0.1);
            cursor: pointer;
            user-select: none;
        }

        .die-peach { background: #ff7722; color: white; }
        .die-red { background: #ef4444; color: white; }

        .dice-icon {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 2px;
            width: 24px;
            height: 24px;
            background: white;
            padding: 2px;
            border-radius: 4px;
            border: 1px solid #ddd;
            flex-shrink: 0;
        }

        .dot { width: 5px; height: 5px; background: black; border-radius: 50%; visibility: hidden; }
        .dot.active { visibility: visible; }

        @keyframes roll {
            0% { transform: rotate(0deg) scale(1); }
            25% { transform: rotate(90deg) scale(1.1); }
            50% { transform: rotate(180deg) scale(1); }
            75% { transform: rotate(270deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1); }
        }
        .rolling { animation: roll 0.2s infinite linear; }
        [x-cloak] { display: none !important; }

        .tense-select {
            appearance: none;
            background-color: white;
            border: 3px solid #f59e0b;
            border-radius: 1rem;
            padding: 0.5rem 2rem 0.5rem 1rem;
            font-weight: 900;
            font-size: 0.9rem;
            color: #1f2937;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            outline: none;
            width: 100%;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23f59e0b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
        }

        .side-panel {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(8px);
            border-radius: 2rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 2px solid rgba(255,255,255,0.5);
            height: fit-content;
        }
    </style>
</head>
<body x-data="game()">

    <div class="w-full min-h-screen flex flex-col items-center p-4">
        
        <!-- Top Title -->
        <h1 class="text-6xl font-black tracking-[0.2em] text-gray-800 drop-shadow-lg mb-4 mt-2">SPIEL</h1>

        <!-- Main Layout Container (3 Columns) -->
        <div class="w-full max-w-7xl grid grid-cols-1 lg:grid-cols-[280px_1fr_280px] gap-8 items-start">
            
            <!-- Left Side: Players & Controls -->
            <div class="flex flex-col gap-6 order-2 lg:order-1">
                <div class="side-panel flex flex-col gap-4">
                    <h2 class="text-xs font-black uppercase text-gray-500 tracking-widest text-center">Spieler</h2>
                    <div class="flex flex-col gap-2">
                        <template x-for="(player, index) in players" :key="index">
                            <div class="flex items-center gap-3 px-4 py-3 bg-white rounded-2xl shadow-sm border-2 transition-all" :class="currentPlayerIndex === index ? 'border-orange-400 scale-105 shadow-md' : 'border-transparent'">
                                <div class="w-4 h-4 rounded-full border-2 border-white shadow-sm" :style="'background-color: ' + player.color"></div>
                                <span x-text="player.name" class="font-bold text-sm text-gray-700"></span>
                            </div>
                        </template>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <button @click="addPlayer()" class="bg-green-500 text-white p-2 rounded-xl text-[10px] font-black uppercase shadow-sm hover:bg-green-600 transition">+ Spieler</button>
                        <button @click="removePlayer()" class="bg-gray-400 text-white p-2 rounded-xl text-[10px] font-black uppercase shadow-sm hover:bg-gray-500 transition">- Spieler</button>
                    </div>
                </div>

                <div class="side-panel flex flex-col gap-3">
                    <h2 class="text-xs font-black uppercase text-gray-500 tracking-widest text-center">Steuerung</h2>
                    <button @click="resetGame()" class="bg-red-500 text-white py-3 rounded-2xl font-black text-xs uppercase shadow-md hover:bg-red-600 transition">Reset Game</button>
                    <button @click="toggleEditMode()" class="bg-blue-500 text-white py-3 rounded-2xl font-black text-xs uppercase shadow-md hover:bg-blue-600 transition" x-text="editMode ? 'Speichern' : 'Bearbeiten'"></button>
                </div>
            </div>

            <!-- Center: The Board -->
            <div class="flex flex-col items-center order-1 lg:order-2">
                <div class="relative grid grid-cols-4 grid-rows-4 gap-4 w-full aspect-square max-w-[600px] p-6 bg-white/10 rounded-3xl">
                    <template x-for="(field, index) in fields" :key="index">
                        <div class="notebook-card flex flex-col items-center justify-start pt-3 p-2 text-base font-black text-center" :class="field.class" :style="getFieldStyle(index)">
                            <template x-if="!editMode">
                                <span x-text="field.name" class="z-30 uppercase break-all leading-tight"></span>
                            </template>
                            <template x-if="editMode">
                                <input type="text" x-model="field.name" class="z-40 bg-white/50 border-none text-center font-black uppercase text-[10px] w-full py-1 rounded">
                            </template>
                            <template x-if="index === 0">
                                <div class="absolute -top-12 left-1/2 -translate-x-1/2 z-50">
                                    <svg width="32" height="40" viewBox="0 0 24 24" fill="none" stroke="#7f1d1d" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="drop-shadow-sm">
                                        <path d="M12 5v14M19 12l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </template>
                            <div class="mt-auto mb-1 flex justify-center gap-1 w-full z-30">
                                <template x-for="(player, pIndex) in players" :key="pIndex">
                                    <div x-show="player.position === index" class="w-5 h-5 rounded-full border-2 border-white shadow-md transition-all duration-500" :style="'background-color: ' + player.color"></div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Center Content (Dice & Task) -->
                    <div class="col-start-2 col-span-2 row-start-2 row-span-2 flex flex-col items-center justify-center gap-4">
                        <div class="h-32 flex flex-col items-center justify-center text-center">
                            <template x-if="!rolling && requiredPerson">
                                <div @click="showAnswer = !showAnswer" class="bg-white p-4 rounded-[2rem] shadow-2xl border-4 border-orange-400 cursor-help transform hover:scale-105 transition-all active:scale-95 min-w-[180px]">
                                    <template x-if="!showAnswer">
                                        <div>
                                            <p class="text-gray-400 text-[8px] uppercase font-black tracking-widest mb-1">Konjugiere für</p>
                                            <p class="text-4xl font-black text-red-600 drop-shadow-sm" x-text="requiredPerson"></p>
                                            <div class="mt-2 flex items-center justify-center gap-1">
                                                <div class="w-1 h-1 rounded-full bg-orange-400 animate-pulse"></div>
                                                <p class="text-[8px] text-gray-400 font-bold uppercase" x-text="loading ? 'Suche...' : 'Klick Lösung'"></p>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="showAnswer">
                                        <div class="px-1">
                                            <p class="text-gray-400 text-[8px] uppercase font-black mb-1" x-text="currentTense.replace('_', ' ')"></p>
                                            <p class="text-2xl font-black leading-tight py-1" :style="'color: ' + players[currentPlayerIndex].color" x-text="getAnswer()"></p>
                                            <p class="text-[7px] text-gray-400 font-bold mt-2 uppercase">Klick Schließen</p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="rolling">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="w-6 h-6 border-3 border-orange-400 border-t-transparent rounded-full animate-spin"></div>
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Würfeln...</p>
                                </div>
                            </template>
                        </div>
                        <div class="flex gap-6">
                            <div class="die die-peach shadow-lg" :class="{'rolling': rolling}" @click="rollDice()"><span x-show="!rolling" x-text="die1"></span></div>
                            <div class="die die-red shadow-lg" :class="{'rolling': rolling}" @click="rollDice()"><span x-show="!rolling" x-text="die2"></span></div>
                        </div>
                        <button x-show="!rolling && lastRoll > 0" @click="nextTurn()" class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-2 rounded-2xl font-black shadow-lg hover:scale-105 transition-all text-[10px] uppercase tracking-widest">Nächster ➔</button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Tense & Rules -->
            <div class="flex flex-col gap-6 order-3">
                <div class="side-panel flex flex-col gap-3">
                    <h2 class="text-xs font-black uppercase text-gray-500 tracking-widest text-center">Zeitform</h2>
                    <select x-model="currentTense" @change="saveState()" class="tense-select">
                        <option value="PRASENS">Präsens</option>
                        <option value="PRATERITUM">Präteritum</option>
                        <option value="PERFEKT">Perfekt</option>
                        <option value="PLUSQUAMPERFEKT">Plusquamperfekt</option>
                        <option value="FUTUR1">Futur I</option>
                        <option value="FUTUR2">Futur II</option>
                        <option value="KONJUNKTIV1_PRASENS">Konj. I (Präs.)</option>
                        <option value="KONJUNKTIV1_PERFEKT">Konj. I (Perf.)</option>
                        <option value="KONJUNKTIV1_FUTUR1">Konj. I (Fut. I)</option>
                        <option value="KONJUNKTIV2_PRATERITUM">Konj. II (Prät.)</option>
                        <option value="KONJUNKTIV2_FUTUR1">Konj. II (Fut. I)</option>
                        <option value="KONJUNKTIV2_FUTUR2">Konj. II (Fut. II)</option>
                    </select>
                </div>

                <div class="side-panel flex flex-col gap-4">
                    <h2 class="text-xs font-black uppercase text-gray-500 tracking-widest text-center">Pronomen</h2>
                    <div class="grid grid-cols-1 gap-y-2">
                        <template x-for="(label, i) in ['Я', 'Ты', 'Он/Она/Оно', 'Мы', 'Вы (друзья)', 'Они/Вы']" :key="i">
                            <div class="flex items-center gap-3 bg-white/50 p-2 rounded-xl">
                                <div class="dice-icon" x-html="getDiceIconHtml(i + 1)"></div>
                                <span class="font-bold text-base text-gray-700" x-text="label"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function game() {
            return {
                players: [{ name: 'Spieler 1', position: 0, color: '#22c55e' }, { name: 'Spieler 2', position: 0, color: '#a855f7' }],
                currentPlayerIndex: 0, die1: 1, die2: 1, lastRoll: 0, requiredPerson: null, rolling: false, editMode: false, showAnswer: false, loading: false, currentTense: 'PRASENS',
                fields: [{ name: 'SEIN', class: 'col-start-4 row-start-1 bg-green-200' }, { name: 'BACKEN', class: 'col-start-3 row-start-1 bg-orange-200' }, { name: 'KOMMEN', class: 'col-start-2 row-start-1 bg-blue-200' }, { name: 'BEDEUTEN', class: 'col-start-1 row-start-1 bg-pink-200' }, { name: 'HABEN', class: 'col-start-1 row-start-2 bg-yellow-100' }, { name: 'SPIELEN', class: 'col-start-1 row-start-3 bg-green-200' }, { name: 'ARBEITEN', class: 'col-start-1 row-start-4 bg-blue-100' }, { name: 'MACHEN', class: 'col-start-2 row-start-4 bg-green-100' }, { name: 'LESEN', class: 'col-start-3 row-start-4 bg-pink-200' }, { name: 'SEIN', class: 'col-start-4 row-start-4 bg-blue-200' }, { name: 'HEISSEN', class: 'col-start-4 row-start-3 bg-yellow-100' }, { name: 'GEHEN', class: 'col-start-4 row-start-2 bg-orange-100' }],
                solutions: {},
                init() {
                    const savedState = @json($gameState);
                    if (savedState && Object.keys(savedState).length > 0) {
                        if (savedState.players) this.players = savedState.players;
                        if (savedState.fields) this.fields = savedState.fields;
                        if (savedState.currentPlayerIndex !== undefined) this.currentPlayerIndex = savedState.currentPlayerIndex;
                        if (savedState.currentTense) {
                            let t = savedState.currentTense.toUpperCase();
                            if (t === 'PRÄSENS') t = 'PRASENS';
                            this.currentTense = t;
                        }
                        if (savedState.solutions) this.solutions = savedState.solutions;
                    }
                    this.fields.forEach(f => this.fetchConjugation(f.name));
                },
                async saveState() {
                    const state = { players: this.players, fields: this.fields, currentPlayerIndex: this.currentPlayerIndex, currentTense: this.currentTense, solutions: this.solutions };
                    try {
                        await fetch('{{ route('games.verb-game.save') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: JSON.stringify(state)
                        });
                    } catch (e) {}
                },
                async fetchConjugation(verbName) {
                    const v = verbName.trim().toUpperCase();
                    if (!v || (this.solutions[v] && this.solutions[v][this.currentTense])) return;
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/conjugate?verb=${v.toLowerCase()}`);
                        const result = await response.json();
                        if (result.success && result.data) {
                            const d = result.data;
                            if (!this.solutions[v]) this.solutions[v] = {};
                            Object.keys(d).forEach(tenseKey => {
                                const tenseData = d[tenseKey];
                                const getVal = (field) => (tenseData[field] && Array.isArray(tenseData[field])) ? tenseData[field].join(' ') : '...';
                                this.solutions[v][tenseKey] = [getVal('S1'), getVal('S2'), getVal('S3'), getVal('P1'), getVal('P2'), getVal('P3')];
                            });
                            this.saveState();
                        }
                    } catch (e) {} finally { this.loading = false; }
                },
                getAnswer() {
                    const player = this.players[this.currentPlayerIndex];
                    const verb = this.fields[player.position].name.trim().toUpperCase();
                    if (this.solutions[verb] && this.solutions[verb][this.currentTense]) {
                        return this.solutions[verb][this.currentTense][this.die1 - 1];
                    }
                    this.fetchConjugation(verb);
                    return '...';
                },
                getDiceIconHtml(num) {
                    const positions = { 1: [4], 2: [0, 8], 3: [0, 4, 8], 4: [0, 2, 6, 8], 5: [0, 2, 4, 6, 8], 6: [0, 2, 3, 5, 6, 8] };
                    let dots = '';
                    for (let i = 0; i < 9; i++) dots += `<div class="dot ${positions[num].includes(i) ? 'active' : ''}"></div>`;
                    return dots;
                },
                toggleEditMode() {
                    this.editMode = !this.editMode;
                    if (!this.editMode) {
                        this.fields.forEach(f => this.fetchConjugation(f.name));
                        this.saveState();
                    }
                },
                addPlayer() {
                    const colors = ['#22c55e', '#a855f7', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'];
                    const newIndex = this.players.length;
                    this.players.push({ name: 'Spieler ' + (newIndex + 1), position: 0, color: colors[newIndex % colors.length] });
                    this.saveState();
                },
                removePlayer() { if (this.players.length > 1) { this.players.pop(); this.saveState(); } },
                rollDice() {
                    if (this.rolling) return;
                    this.rolling = true; this.lastRoll = 0; this.requiredPerson = null; this.showAnswer = false;
                    setTimeout(() => {
                        this.die1 = Math.floor(Math.random() * 6) + 1;
                        this.die2 = Math.floor(Math.random() * 3) + 1;
                        this.requiredPerson = ['Я', 'Ты', 'Он/Она/Оно', 'Мы', 'Вы (друзья)', 'Они/Вы'][this.die1 - 1];
                        this.lastRoll = this.die2; this.rolling = false;
                        this.movePlayer(); this.saveState();
                    }, 800);
                },
                movePlayer() { this.players[this.currentPlayerIndex].position = (this.players[this.currentPlayerIndex].position + this.lastRoll) % this.fields.length; this.saveState(); },
                nextTurn() { this.showAnswer = false; this.lastRoll = 0; this.requiredPerson = null; this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length; this.saveState(); },
                resetGame() { this.players.forEach(p => p.position = 0); this.currentPlayerIndex = 0; this.requiredPerson = null; this.lastRoll = 0; this.saveState(); },
                getFieldStyle(index) { return `transform: rotate(${(index % 3 - 1) * 2}deg);`; }
            }
        }
    </script>
</body>
</html>
