<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Hi-Low Game') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="hiLowGame()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl relative">
                
                <x-game-header reset="resetGame()">
                     <h3 class="text-xl sm:text-2xl font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600 uppercase tracking-tighter">{{ __('Hi-Low') }}</h3>
                     <p class="text-[8px] sm:text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] font-bold">{{ __('Guess Next Number (0-27)') }}</p>
                </x-game-header>

                <!-- Main Game Area -->
                <div class="p-8 min-h-[400px] flex flex-col items-center justify-center space-y-8">
                    
                    <template x-if="players.length === 0">
                        <div class="text-center space-y-6">
                            <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto">
                                <i class="fas fa-user-plus text-indigo-600 text-3xl"></i>
                            </div>
                            <div>
                            <div>
                                <h3 class="text-xl font-bold dark:text-white">{{ __('No Players Found') }}</h3>
                                <p class="text-gray-500 dark:text-gray-400">{{ __('You need at least one player to start the game.') }}</p>
                            </div>
                            <a href="{{ route('tools.player-selection') }}" class="inline-block px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-transform hover:scale-105">
                                {{ __('Add Players') }}
                            </a>
                        </div>
                    </template>

                    <template x-if="players.length > 0 && gameState === 'playing'">
                        <div class="w-full max-w-md space-y-12 text-center">
                            <!-- Current Player -->
                            <div class="space-y-2">
                                <span class="text-xs font-bold uppercase tracking-widest text-indigo-500">{{ __('Current Turn') }}</span>
                                <div class="text-4xl font-extrabold dark:text-white" x-text="players[currentPlayerIndex]"></div>
                            </div>

                            <!-- Big Number -->
                            <div class="relative">
                                <div class="absolute inset-0 bg-indigo-500 blur-3xl opacity-20 rounded-full"></div>
                                <div class="relative bg-white dark:bg-gray-700 w-48 h-48 rounded-3xl shadow-inner border border-gray-100 dark:border-gray-600 flex items-center justify-center mx-auto">
                                    <span class="text-8xl font-black text-gray-800 dark:text-white" x-text="currentNumber"></span>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="grid grid-cols-2 gap-4">
                                <button 
                                    @click="guess('high')"
                                    class="group relative overflow-hidden px-8 py-6 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl shadow-lg transition-all hover:-translate-y-1 active:translate-y-0"
                                >
                                    <div class="relative z-10 flex flex-col items-center">
                                         <i class="fas fa-chevron-up text-2xl mb-1 group-hover:bounce"></i>
                                         <span class="font-bold text-lg uppercase">{{ __('Higher') }}</span>
                                     </div>
                                    <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                </button>

                                <button 
                                    @click="guess('low')"
                                    class="group relative overflow-hidden px-8 py-6 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl shadow-lg transition-all hover:-translate-y-1 active:translate-y-0"
                                >
                                    <div class="relative z-10 flex flex-col items-center">
                                         <i class="fas fa-chevron-down text-2xl mb-1 group-hover:bounce"></i>
                                         <span class="font-bold text-lg uppercase">{{ __('Lower') }}</span>
                                     </div>
                                    <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Picked State (End Screen Redesign) - Standard and LMS -->
                <template x-if="gameState === 'picked' || gameState === 'overall_winner'">
                    <div class="absolute inset-0 z-50 bg-white/95 dark:bg-gray-800/95 flex flex-col items-center justify-center p-8 text-center animate-bounce-in shadow-2xl backdrop-blur-md sm:rounded-3xl">
                        <div class="relative mb-8">
                            <template x-if="gameState === 'overall_winner'">
                                <div class="absolute inset-0 bg-amber-400 blur-2xl opacity-20 rounded-full animate-pulse scale-150"></div>
                            </template>
                            <template x-if="gameState === 'picked'">
                                <div class="absolute inset-0 bg-rose-500 blur-2xl opacity-20 rounded-full animate-pulse scale-150"></div>
                            </template>
                            <div class="relative w-40 h-40 bg-white dark:bg-gray-700 rounded-full flex items-center justify-center border-8 border-rose-500 shadow-[0_30px_60px_rgba(225,29,72,0.3)]">
                                <i class="fas" :class="gameState === 'overall_winner' ? 'fa-crown text-amber-500 text-7xl' : 'fa-times text-7xl text-rose-500'"></i>
                            </div>
                        </div>

                        <div class="mb-12">
                            <template x-if="gameState === 'overall_winner'">
                                <div>
                                    <h2 class="text-xs font-black uppercase tracking-[1em] text-amber-500 mb-4 px-2">{{ __('ULTIMATE CHAMPION') }}</h2>
                                    <div class="text-7xl font-black text-amber-600 italic tracking-tighter uppercase drop-shadow-[0_5px_15px_rgba(251,191,36,0.3)]" x-text="winnerName"></div>
                                    <p class="text-gray-500 dark:text-gray-400 text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic">{{ __('Last Man Standing!') }}</p>
                                </div>
                            </template>
                            <template x-if="gameState === 'picked'">
                                <div>
                                    <h2 class="text-xs font-black uppercase tracking-[1em] text-rose-500 mb-4 px-2">{{ __('Wrong Guess') }}</h2>
                                    <div class="text-7xl font-black text-rose-500 italic tracking-tighter uppercase drop-shadow-[0_5px_15px_rgba(225,29,72,0.3)]" x-text="lastPickedPlayer"></div>
                                    <p class="text-gray-500 dark:text-gray-400 text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic" x-text="lmsActive ? '{{ __('You are Eliminated!') }}' : '{{ __('Prepare your wallet!') }}'"></p>
                                </div>
                            </template>
                        </div>

                        <div class="w-full max-w-sm space-y-4">
                            <template x-if="gameState === 'overall_winner'">
                                <button @click="resetRound()" class="w-full py-6 bg-amber-500 text-white font-black text-2xl rounded-3xl shadow-[0_20px_40px_rgba(79,70,229,0.3)] transition-all hover:scale-105 uppercase tracking-tighter">
                                    {{ __('NEW ROUND') }}
                                </button>
                            </template>
                            <template x-if="gameState === 'picked'">
                                <div>
                                    <div class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">
                                        {{ __('The number was') }} <span class="text-gray-800 dark:text-white" x-text="nextNumber"></span>
                                    </div>
                                    <button @click="resetGame()" class="w-full py-6 bg-indigo-600 hover:bg-indigo-500 text-white font-black text-2xl rounded-3xl shadow-[0_20px_40px_rgba(79,70,229,0.3)] transition-all hover:scale-105 uppercase tracking-tighter">
                                        <span x-text="lmsActive ? 'NEXT ROUND' : '{{ __('NEXT SACRIFICE') }}'"></span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>


                <!-- History / Footer -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-6 border-t border-gray-100 dark:border-gray-700">
                    <h4 class="text-xs font-bold uppercase text-gray-400 mb-4 tracking-widest">{{ __('Number History') }}</h4>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(num, index) in history" :key="index">
                            <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex items-center justify-center font-bold text-gray-600 dark:text-gray-300 text-sm shadow-sm" x-text="num"></div>
                        </template>
                        <template x-if="history.length === 0">
                            <span class="text-gray-400 text-sm italic">{{ __('No history yet') }}</span>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('hiLowGame', () => ({
                players: @json($names),
                currentPlayerIndex: 0,
                currentNumber: Math.floor(Math.random() * 28),
                nextNumber: null,
                lastPickedPlayer: '',
                winnerName: '',
                lmsActive: {{ $lmsActive ? 'true' : 'false' }},
                gameState: 'playing', // 'playing', 'picked', 'overall_winner'
                history: [],

                init() {
                    this.history = [this.currentNumber];
                    @if(isset($overallWinner))
                        this.winnerName = '{{ $overallWinner }}';
                        this.gameState = 'overall_winner';
                    @endif
                },

                guess(choice) {
                    this.nextNumber = Math.floor(Math.random() * 28);
                    
                    // Avoid same number for better gameplay (optional)
                    while(this.nextNumber === this.currentNumber) {
                        this.nextNumber = Math.floor(Math.random() * 28);
                    }

                    let isCorrect = false;
                    if (choice === 'high') {
                        isCorrect = this.nextNumber > this.currentNumber;
                    } else {
                        isCorrect = this.nextNumber < this.currentNumber;
                    }

                    if (isCorrect) {
                        this.currentNumber = this.nextNumber;
                        this.history.unshift(this.currentNumber); // Newest at start
                        if (this.history.length > 10) this.history.pop();
                        
                        this.nextPlayer();
                    } else {
                        // Remember this player was picked
                        this.lastPickedPlayer = this.players[this.currentPlayerIndex];
                        this.gameState = 'picked';
                        this.saveWinner(this.lastPickedPlayer);
                        
                        // Advance to next player for the next turn
                        this.nextPlayer();
                    }
                },

                nextPlayer() {
                    this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length;
                },

                resetGame() {
                    if (this.lmsActive) {
                        window.location.reload();
                        return;
                    }
                    this.gameState = 'playing';
                    this.currentNumber = Math.floor(Math.random() * 28);
                    this.history = [this.currentNumber];
                    this.nextNumber = null;
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
                    }).catch(e => console.error('Failed to save winner', e));
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
                        li.className = 'flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600 group hover:border-amber-400 transition-colors';
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
        .animate-bounce-in {
            animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); opacity: 1; }
        }

        .bounce {
            animation: bounceUpDown 1s infinite;
        }

        @keyframes bounceUpDown {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
    </style>
</x-app-layout>
