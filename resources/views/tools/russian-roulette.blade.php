<x-app-layout>

    <div class="flex-grow flex flex-col w-full relative" x-data="rouletteGame()">
        <div class="flex-grow flex flex-col w-full">
            <div class="flex-grow flex flex-col bg-white dark:bg-gray-800 transition-colors duration-300 relative overflow-hidden min-h-[500px]">
                
                <x-game-header reset="resetGame()">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter italic">{{ __('Deadly Roulette') }}</h3>
                    <p class="text-[8px] sm:text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] font-bold">{{ __('6 Chambers. 1 Slug.') }}</p>
                </x-game-header>

                <!-- Game Body -->
                <div class="flex-grow flex flex-col items-center justify-center p-8 space-y-12">
                     <template x-if="players.length === 0">
                        <div class="text-center space-y-6">
                            <i class="fas fa-skull text-6xl text-gray-400"></i>
                             <h3 class="text-xl font-bold dark:text-white">{{ __('Empty List') }}</h3>
                             <a href="{{ route('tools.player-selection') }}" class="inline-block px-8 py-3 bg-gray-900 text-white font-bold rounded-xl border border-gray-700">{{ __('Add Targets') }}</a>
                        </div>
                    </template>

                    <!-- Game Content Wrapper -->
                    <div x-show="players.length > 0" :class="gameState !== 'ready' ? 'invisible pointer-events-none' : ''" class="w-full max-w-sm flex flex-col items-center space-y-12 text-center transition-opacity duration-300">
                        <!-- Turning Player -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-500">{{ __('In the Crosshairs:') }}</span>
                            <div class="text-4xl font-black dark:text-white uppercase italic" x-text="players[currentPlayerIndex]"></div>
                        </div>

                        <!-- Cylinder Visual (Fixed Circle Layout) -->
                        <div class="relative w-64 h-64 flex items-center justify-center">
                            <!-- Main Cylinder Background -->
                            <div class="absolute inset-4 bg-gray-100 dark:bg-gray-700/50 rounded-full shadow-inner border border-gray-200 dark:border-gray-600"></div>
                            
                            <!-- Rotating Chamber Group -->
                            <div 
                                class="relative w-48 h-48 transition-transform duration-700 cubic-bezier(0.34, 1.56, 0.64, 1)" 
                                :style="'transform: rotate(' + (chambersCompleted * 60) + 'deg)'"
                            >
                                <template x-for="i in 6">
                                    <div 
                                        class="absolute top-1/2 left-1/2 w-12 h-12 -mt-6 -ml-6 rounded-full flex items-center justify-center transition-colors duration-300 shadow-sm border"
                                        :class="(7 - i) % 6 < chambersCompleted ? 'bg-gray-800 border-gray-900' : 'bg-white dark:bg-gray-600 border dark:border-gray-500'"
                                        :style="'transform: rotate(' + ((i-1) * 60) + 'deg) translateY(-60px)'"
                                    >
                                        <template x-if="(7 - i) % 6 < chambersCompleted">
                                             <i class="fas fa-circle text-[6px] text-gray-600 opacity-50"></i>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- Center Hub -->
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <div class="w-12 h-12 bg-gray-900 rounded-full border-4 border-gray-800 shadow-xl flex items-center justify-center">
                                    <span class="text-xl font-black text-rose-600" x-text="(6 - chambersCompleted)"></span>
                                </div>
                                <span class="text-[8px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">{{ __('Ready') }}</span>
                            </div>
                        </div>

                        <!-- Trigger & Handover Buttons -->
                        <div class="w-full flex flex-col space-y-3">
                            <button 
                                @click="pullTrigger()"
                                :disabled="busy"
                                class="w-full py-6 bg-gray-900 hover:bg-black text-white text-2xl font-black uppercase tracking-widest rounded-2xl shadow-2xl transition-all active:scale-95 disabled:opacity-50 group overflow-hidden relative"
                            >
                                <span class="relative z-10" x-text="busy ? '...' : '{{ __('PULL TRIGGER') }}'"></span>
                                <div class="absolute inset-0 bg-white/5 -translate-x-full group-hover:translate-x-0 transition-transform"></div>
                            </button>
                            
                            <button 
                                @click="handover()"
                                :disabled="busy || chambersCompleted === 0"
                                class="w-full py-4 bg-white dark:bg-gray-800 border-2 border-gray-100 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 text-gray-900 dark:text-white text-sm font-black uppercase tracking-widest rounded-2xl transition-all active:scale-95 disabled:opacity-30"
                            >
                                <i class="fas fa-hand-holding mr-2"></i> {{ __('Handover Turn') }}
                            </button>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter italic">{{ __('Risk the 1 in') }} <span x-text="6-chambersCompleted"></span> {{ __('chance or pass it on') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Fatal State (End Screen Redesign) - Standard and LMS -->
                <div x-show="gameState === 'deadly' || gameState === 'overall_winner'" 
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0 scale-95" 
                    x-transition:enter-end="opacity-100 scale-100" 
                    class="absolute inset-0 z-50 bg-black overflow-y-auto p-4 sm:p-8 text-white text-center transition-colors duration-300">
                    <div class="min-h-full flex flex-col items-center justify-center py-8">
                    <!-- Cracked Screen Effect / Overlays -->
                    <div class="absolute inset-0 pointer-events-none opacity-40 bg-[radial-gradient(circle_at_center,_transparent_0%,_black_100%)]"></div>
                    <div class="absolute inset-0 pointer-events-none opacity-20 bg-[url('https://www.transparenttextures.com/patterns/broken-noise.png')]"></div>
                    
                    <!-- Blood Spatter / Glow - Contained -->
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,_rgba(220,38,38,0.3)_0%,_transparent_70%)] animate-pulse shadow-[inset_0_0_100px_rgba(0,0,0,1)]"></div>
                    
                    <div class="relative mb-8 sm:mb-12">
                        <template x-if="gameState === 'overall_winner'">
                            <div class="absolute inset-0 bg-amber-400 blur-3xl opacity-40 animate-pulse scale-150"></div>
                        </template>
                        <template x-if="gameState === 'deadly'">
                            <div class="absolute inset-0 bg-rose-600 blur-3xl opacity-40 animate-pulse scale-150"></div>
                        </template>
                        <div class="relative w-28 h-28 sm:w-40 sm:h-40 bg-rose-950 rounded-full border-4 sm:border-8 border-rose-600 flex items-center justify-center shadow-[0_30px_60px_rgba(225,29,72,0.6)]">
                            <i class="fas fa-crosshairs text-4xl sm:text-6xl" :class="gameState === 'overall_winner' ? 'text-amber-500' : 'text-rose-500 animate-spin-slow'"></i>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-2 h-2 sm:w-4 sm:h-4 rounded-full bg-rose-500 shadow-[0_0_15px_rgba(225,29,72,1)]"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-8 sm:mb-12">
                        <template x-if="gameState === 'overall_winner'">
                            <div>
                                <h2 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.5em] sm:tracking-[1em] text-amber-500 mb-4">{{ __('ULTIMATE CHAMPION') }}</h2>
                                <div class="text-4xl sm:text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_5px_15px_rgba(251,191,36,0.8)]" x-text="winnerName"></div>
                                <h2 class="text-2xl sm:text-3xl font-black text-amber-600 italic tracking-widest mt-2 uppercase tracking-tighter">{{ __('Last Man Standing!') }}</h2>
                            </div>
                        </template>
                        <template x-if="gameState === 'deadly'">
                            <div>
                                <h2 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.5em] sm:tracking-[1em] text-rose-500 animate-pulse mb-4">{{ __('CRITICAL HIT') }}</h2>
                                <div class="text-4xl sm:text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_5px_15px_rgba(225,29,72,0.8)]" x-text="players[currentPlayerIndex]"></div>
                                <h2 class="text-2xl sm:text-3xl font-black text-rose-600 italic tracking-widest mt-2 uppercase tracking-tighter" x-text="lmsActive ? '{{ __('IS ELIMINATED!') }}' : '{{ __('IS DOWN!') }}'"></h2>
                            </div>
                        </template>
                    </div>

                    <template x-if="gameState === 'overall_winner'">
                        <button @click="resetRound()" class="group relative w-full max-w-sm py-4 sm:py-6 bg-amber-600 hover:bg-amber-500 text-white font-black text-xl sm:text-2xl rounded-2xl sm:rounded-3xl shadow-[0_20px_60px_rgba(251,191,36,0.4)] transition-all hover:scale-105 active:scale-95 overflow-hidden flex items-center justify-center">
                            <span class="relative z-10 uppercase tracking-tighter text-white">{{ __('NEW ROUND') }}</span>
                        </button>
                    </template>
                    <template x-if="gameState === 'deadly'">
                        <button @click="resetGame()" class="group relative w-full max-w-sm py-4 sm:py-6 bg-rose-600 hover:bg-rose-500 text-white font-black text-xl sm:text-2xl rounded-2xl sm:rounded-3xl shadow-[0_20px_60px_rgba(225,29,72,0.4)] transition-all hover:scale-105 active:scale-95 overflow-hidden flex items-center justify-center">
                            <span class="relative z-10 uppercase tracking-tighter" x-text="lmsActive ? '{{ __('NEXT ROUND') }}' : '{{ __('RELOAD CHAMBER') }}'"></span>
                            <div class="absolute inset-0 bg-white/10 translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                        </button>
                    </template>
                </div>
            </div>


                <!-- History -->
                <div class="p-6 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex flex-col space-y-2">
                        <span class="text-[8px] font-black uppercase tracking-widest text-gray-500">{{ __('Live Feed:') }}</span>
                        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none">
                            <template x-for="log in logs">
                                <div class="whitespace-nowrap px-3 py-1 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 text-[10px] font-bold tracking-tight">
                                    <span class="text-gray-400" x-text="log.name + ': '"></span>
                                    <span :class="log.result === 'Safe' ? 'text-emerald-500' : 'text-rose-600'" x-text="log.result"></span>
                                </div>
                            </template>
                           <template x-if="logs.length === 0">
                                <span class="text-[10px] italic text-gray-400">{{ __('Waiting for first trigger pull...') }}</span>
                           </template>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('rouletteGame', () => ({
                players: @json($names),
                currentPlayerIndex: 0,
                gameState: 'ready', // ready, deadly, overall_winner
                winnerName: '',
                lmsActive: {{ $lmsActive ? 'true' : 'false' }},
                secretBullet: 1, // Physical chamber 1-6
                chambersCompleted: 0,
                busy: false,
                logs: [],

                init() {
                    this.loadedSlug = 0; // Legacy ref just in case
                    this.reloadCylinder();
                    @if(isset($overallWinner))
                        this.winnerName = '{{ $overallWinner }}';
                        this.gameState = 'overall_winner';
                    @endif
                },

                reloadCylinder() {
                    // Using better entropy if available
                    if (window.crypto && window.crypto.getRandomValues) {
                        const arr = new Uint32Array(1);
                        window.crypto.getRandomValues(arr);
                        this.secretBullet = (arr[0] % 6) + 1;
                    } else {
                        this.secretBullet = Math.floor(Math.random() * 6) + 1;
                    }
                    console.log('--- RELOADED ---');
                    console.log('Secret Bullet is in Physical Chamber:', this.secretBullet);
                    console.log('It will fire on pull number:', (7 - this.secretBullet) % 6 + 1);
                },

                pullTrigger() {
                    if (this.busy) return;
                    this.busy = true;

                    // Calculate which physical chamber is currently at the top
                    // P1 (C=0) -> Top is i=1
                    // P2 (C=1) -> Top is i=6
                    // P3 (C=2) -> Top is i=5 ...
                    const currentTopChamber = (6 - (this.chambersCompleted % 6)) % 6 + 1;
                    
                    setTimeout(() => {
                        if (currentTopChamber === this.secretBullet) {
                            this.killShot();
                        } else {
                            this.safeClick();
                        }
                        this.busy = false;
                    }, 500);
                },

                safeClick() {
                    this.logs.unshift({ name: this.players[this.currentPlayerIndex], result: 'Safe' });
                    if (this.logs.length > 5) this.logs.pop();
                    this.chambersCompleted++;
                },

                handover() {
                    if (this.busy) return;
                    this.nextPlayer();
                },

                killShot() {
                    this.gameState = 'deadly';
                    this.saveWinner(this.players[this.currentPlayerIndex]);
                },

                nextPlayer() {
                    this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length;
                },

                resetGame() {
                    if (this.lmsActive) {
                        window.location.reload();
                        return;
                    }
                    this.gameState = 'ready';
                    this.chambersCompleted = 0;
                    this.reloadCylinder();
                    this.logs = [];
                    // Start from the beginning of the list for the fresh round
                    this.currentPlayerIndex = 0;
                },

                resetRound() {
                    fetch('{{ route('tools.reset-lms') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => window.location.reload());
                },

                saveWinner(winner) {
                    fetch('/tools/save-winner', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ winner: winner })
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success && data.names) {
                            this.players = data.names;
                        }
                        this.updateScoreboard(winner);
                    }).catch(e => console.error('Error saving', e));
                },

                updateScoreboard(winner) {
                    let list = document.getElementById('scoreboard-list');
                    if (!list) return;

                    let existingLi = list.querySelector(`li[data-player="${winner}"]`);
                    if (existingLi) {
                        let countSpan = existingLi.querySelector('.win-count');
                        let currentWins = parseInt(countSpan.innerText);
                        countSpan.innerText = (currentWins + 1) + ' {{ __('FAILS') }}';
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
                        let countA = parseInt(a.querySelector('.win-count').innerText);
                        let countB = parseInt(b.querySelector('.win-count').innerText);
                        return countB - countA;
                    });
                    list.innerHTML = '';
                    items.forEach((item, index) => {
                        item.querySelector('div span:first-child').innerText = index + 1;
                        list.appendChild(item);
                    });
                    
                }
            }));
        });
    </script>

    <style>
        @keyframes ping-slow {
            0% { transform: scale(1); opacity: 0.2; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .animate-ping-slow {
            animation: ping-slow 2s infinite;
        }

        /* Hide scrollbar */
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
        
        .cubic-bezier {
            transition-timing-function: cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>
</x-app-layout>
