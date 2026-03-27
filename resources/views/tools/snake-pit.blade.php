<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Snake Pit') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="snakePitGame()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-2xl sm:rounded-[2.5rem] overflow-hidden relative min-h-[600px] flex flex-col border border-gray-100 dark:border-gray-700">

                
                <x-game-header reset="resetGame()">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tighter italic">{{ __('Snake Pit') }}</h3>
                    <p class="text-[8px] sm:text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] font-bold">{{ __('Avoid the hidden vipers') }}</p>
                </x-game-header>

                <div class="flex-grow flex flex-col items-center justify-center p-8 space-y-10 relative overflow-hidden bg-emerald-50/50 dark:bg-emerald-950/20">
                    <!-- Subtle Theme Background Decoration -->
                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    
                    <template x-if="players.length === 0">
                        <div class="text-center space-y-6">
                             <i class="fas fa-biohazard text-6xl text-gray-400"></i>
                              <h3 class="text-xl font-bold dark:text-white">{{ __('No Adventurers Found') }}</h3>
                              <p class="text-gray-500 dark:text-gray-400 text-sm italic">{{ __('You need a party to enter the pit...') }}</p>
                              <a href="{{ route('tools.player-selection') }}" class="inline-block px-8 py-3 bg-emerald-600 text-white font-bold rounded-xl">{{ __('Add Players') }}</a>
                         </div>
                    </template>

                    <template x-if="players.length > 0 && gameState === 'playing'">
                        <div class="w-full flex flex-col items-center space-y-12">
                            <!-- Current Turn Indicator -->
                            <div class="text-center space-y-2">
                                <span class="text-[10px] font-black uppercase tracking-[0.4em] text-emerald-500">{{ __('Active Turn:') }}</span>
                                <div class="text-4xl font-black dark:text-white uppercase italic tracking-tighter" x-text="players[currentPlayerIndex]"></div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ __('Pick a tile carefully') }}</p>
                            </div>

                            <!-- The Grid -->
                            <div class="grid grid-cols-4 gap-3 lg:gap-4 p-4 bg-emerald-950/50 rounded-3xl border-2 border-emerald-800 shadow-2xl shadow-black/50 overflow-hidden relative">
                                <template x-for="(cell, index) in grid" :key="index">
                                    <div 
                                        @click="reveal(index)"
                                        :class="{
                                            'bg-emerald-700 border-2 border-emerald-600 shadow-[inset_0_2px_10px_rgba(255,255,255,0.1)]': !cell.revealed,
                                            'bg-emerald-950 cursor-default scale-95 opacity-50': cell.revealed && !cell.isSnake,
                                            'bg-rose-600 animate-wiggle-fast shadow-[0_0_50px_rgba(225,29,72,0.8)] border-4 border-white': cell.revealed && cell.isSnake
                                        }"
                                        class="w-16 h-16 lg:w-20 lg:h-20 rounded-2xl flex items-center justify-center cursor-pointer transition-all duration-300 hover:scale-105 active:scale-95 group overflow-hidden"
                                    >
                                        <template x-if="!cell.revealed">
                                            <div class="w-2 h-2 bg-emerald-900/50 rounded-full group-hover:scale-150 transition-transform"></div>
                                        </template>
                                        <template x-if="cell.revealed">
                                            <span>
                                                <template x-if="cell.isSnake">
                                                    <i class="fas fa-skull-crossbones text-3xl text-white"></i>
                                                </template>
                                                <template x-if="!cell.isSnake">
                                                    <i class="fas fa-times text-emerald-800 text-2xl"></i>
                                                </template>
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- Footer Instruction -->
                            <div class="flex items-center gap-3 text-emerald-700 font-bold uppercase tracking-widest text-[8px] italic animate-pulse">
                                <i class="fas fa-chevron-left animate-bounce-horizontal"></i>
                                Pick a tile carefully
                                <i class="fas fa-chevron-right animate-bounce-horizontal-rev"></i>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Bitten State (End Screen) - Moved out to cover whole card -->
                <template x-if="gameState === 'bitten' || gameState === 'overall_winner'">
                    <div class="absolute inset-0 z-50 bg-emerald-950 flex flex-col items-center justify-center p-8 text-center animate-fade-in shadow-2xl backdrop-blur-xl">
                        <div class="relative mb-12">
                            <template x-if="gameState === 'overall_winner'">
                                <div class="absolute inset-0 bg-amber-400 blur-3xl opacity-30 scale-150 rounded-full animate-pulse"></div>
                            </template>
                            <template x-if="gameState === 'bitten'">
                                <div class="absolute inset-0 bg-emerald-400 blur-3xl opacity-20 scale-150 rounded-full"></div>
                            </template>
                            <div class="relative w-40 h-40 bg-white dark:bg-emerald-900 rounded-full flex items-center justify-center border-8 border-emerald-800 shadow-[0_30px_60px_rgba(0,0,0,0.5)]">
                                <i class="fas" :class="gameState === 'overall_winner' ? 'fa-crown text-amber-500 text-7xl' : 'fa-biohazard text-6xl text-rose-600 animate-spin-slow'"></i>
                            </div>
                        </div>
                        
                        <div class="mb-12">
                            <template x-if="gameState === 'overall_winner'">
                                 <div>
                                     <h2 class="text-xs font-black uppercase tracking-[1em] text-amber-500 mb-4">{{ __('ULTIMATE CHAMPION') }}</h2>
                                     <div class="text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_5px_15px_rgba(251,191,36,0.8)]" x-text="winnerName"></div>
                                     <h2 class="text-3xl font-black text-amber-600 italic tracking-widest mt-2 uppercase tracking-tighter">{{ __('Last Man Standing!') }}</h2>
                                 </div>
                            </template>
                            <template x-if="gameState === 'bitten'">
                                <div>
                                    <h2 class="text-xl font-bold text-emerald-500 uppercase tracking-[0.5em] mb-4" x-text="lmsActive ? 'You are Eliminated!' : 'You\'ve been bitten!'"></h2>
                                    <div class="text-7xl font-black text-rose-600 italic tracking-tighter uppercase drop-shadow-[0_0_20px_rgba(225,29,72,0.4)]" x-text="players[currentPlayerIndex]"></div>
                                    <p class="text-emerald-400 text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic">Prepare your wallet!</p>
                                </div>
                            </template>
                        </div>

                        <template x-if="gameState === 'overall_winner'">
                             <button @click="resetRound()" class="group relative w-full max-w-sm py-6 bg-amber-600 hover:bg-amber-500 text-white font-black text-2xl rounded-3xl shadow-[0_20px_60px_rgba(251,191,36,0.4)] transition-all hover:scale-105 active:scale-95 overflow-hidden flex items-center justify-center">
                                 <span class="relative z-10 uppercase tracking-tighter text-white">{{ __('NEW ROUND') }}</span>
                             </button>
                         </template>
                         <template x-if="gameState === 'bitten'">
                             <button @click="resetGame()" class="group relative w-full max-w-sm py-6 bg-rose-600 hover:bg-rose-500 text-white font-black text-2xl rounded-3xl shadow-[0_20px_60px_rgba(225,29,72,0.4)] transition-all hover:scale-105 active:scale-95 overflow-hidden flex items-center justify-center">
                                 <span class="relative z-10 uppercase tracking-tighter" x-text="lmsActive ? '{{ __('NEXT ROUND') }}' : '{{ __('RESET PIT') }}'"></span>
                             </button>
                         </template>
                    </div>
                </template>




            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('snakePitGame', () => ({
                players: @json($names),
                currentPlayerIndex: 0,
                gameState: 'playing', // playing, bitten, overall_winner
                winnerName: '',
                lmsActive: {{ $lmsActive ? 'true' : 'false' }},
                grid: [],
                snakes: [],
                totalCells: 16,
                snakeCount: 2,

                init() {
                    this.setupGrid();
                    @if(isset($overallWinner))
                        this.winnerName = '{{ $overallWinner }}';
                        this.gameState = 'overall_winner';
                    @endif
                },

                setupGrid() {
                    this.grid = Array.from({length: this.totalCells}, () => ({
                        revealed: false,
                        isSnake: false
                    }));

                    // Place snakes
                    let placed = 0;
                    while(placed < this.snakeCount) {
                        let idx = Math.floor(Math.random() * this.totalCells);
                        if (!this.grid[idx].isSnake) {
                            this.grid[idx].isSnake = true;
                            placed++;
                        }
                    }
                },

                reveal(index) {
                    if (this.grid[index].revealed || this.gameState !== 'playing') return;
                    
                    this.grid[index].revealed = true;

                    if (this.grid[index].isSnake) {
                        this.gameState = 'bitten';
                        this.saveWinner(this.players[this.currentPlayerIndex]);
                    } else {
                        // Small vibration-like delay before turn passes?
                        this.nextPlayer();
                    }
                },

                nextPlayer() {
                    this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length;
                },

                reset() {
                    if (this.lmsActive) {
                       window.location.reload();
                       return;
                    }
                    this.gameState = 'playing';
                    this.setupGrid();
                    // Starter advances
                    this.nextPlayer();
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
                    }).then(() => {
                        this.updateScoreboard(winner);
                    }).catch(e => console.error('Snake bit the server:', e));
                },

                updateScoreboard(winner) {
                    let list = document.getElementById('scoreboard-list');
                    if (!list) return;

                    let existingLi = list.querySelector(`li[data-player="${winner}"]`);
                    if (existingLi) {
                        let countSpan = existingLi.querySelector('.win-count');
                        let currentWins = parseInt(countSpan.innerText);
                        countSpan.innerText = (currentWins + 1) + ' WINS';
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
                            <span class="bg-amber-500 text-white px-4 py-1.5 rounded-full text-xs font-black shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform win-count">1 WINS</span>`;
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
        @keyframes fade-in {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        .animate-fade-in { animation: fade-in 0.3s forwards; }

        @keyframes wiggle-fast {
            0%, 100% { transform: scale(1) rotate(0); }
            25% { transform: scale(1.1) rotate(5deg); }
            75% { transform: scale(1.1) rotate(-5deg); }
        }
        .animate-wiggle-fast { animation: wiggle-fast 0.2s infinite; }

        @keyframes spin-slow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .animate-spin-slow { animation: spin-slow 3s linear infinite; }

        @keyframes bounce-horizontal {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(-5px); }
        }
        .animate-bounce-horizontal { animation: bounce-horizontal 1s infinite ease-in-out; }

        @keyframes bounce-horizontal-rev {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(5px); }
        }
        .animate-bounce-horizontal-rev { animation: bounce-horizontal-rev 1s infinite ease-in-out; }
    </style>
</x-app-layout>
