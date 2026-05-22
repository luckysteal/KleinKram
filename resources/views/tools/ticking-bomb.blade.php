<x-app-layout>

    <div class="flex-grow flex flex-col w-full relative" x-data="bombGame()">
        <div class="flex-grow flex flex-col w-full h-full">
            <div class="flex-grow flex flex-col bg-white dark:bg-gray-800 transition-colors duration-300 relative min-h-[500px]">
                
                <x-game-header reset="resetGame()">
                    <h3 class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-500 uppercase tracking-tighter italic">{{ __('Ticking Bomb') }}</h3>
                    <p class="text-[8px] sm:text-[10px] text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] font-bold">{{ __('Don\'t be holding it!') }}</p>
                </x-game-header>

                <!-- Game Body -->
                <div class="flex-grow flex flex-col items-center justify-center p-8 space-y-12 overflow-hidden">
                    
                    <template x-if="players.length === 0">
                        <div class="text-center space-y-6">
                            <div class="w-20 h-20 bg-rose-100 dark:bg-rose-900/30 rounded-full flex items-center justify-center mx-auto">
                                <div class="text-center space-y-6">
                                    <i class="fas fa-bomb text-6xl text-gray-400"></i>
                                    <h3 class="text-xl font-bold dark:text-white">{{ __('Add Players First') }}</h3>
                                    <a href="{{ route('tools.player-selection') }}" class="inline-block px-8 py-3 bg-rose-600 text-white font-bold rounded-xl">{{ __('Manage Players') }}</a>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Game Content Wrapper -->
                    <div x-show="players.length > 0" :class="(gameState === 'exploded' || gameState === 'overall_winner') ? 'invisible pointer-events-none' : ''" class="w-full flex flex-col items-center justify-center space-y-12 transition-opacity duration-300">
                        
                        <div x-show="gameState === 'ready'" class="text-center space-y-8">
                            <div class="relative group cursor-pointer" @click="startGame()">
                                <div class="absolute inset-0 bg-rose-500 blur-2xl opacity-20 group-hover:opacity-40 transition-opacity rounded-full"></div>
                                <div class="relative w-48 h-48 bg-gray-900 rounded-full flex items-center justify-center border-8 border-gray-800 shadow-2xl transition-transform hover:scale-105">
                                    <i class="fas fa-bomb text-7xl text-rose-600"></i>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-2xl font-bold dark:text-white">{{ __('Starting Player:') }}</h3>
                                <h2 class="text-4xl font-black text-rose-500 dark:text-rose-400 mb-2" x-text="players[currentPlayerIndex]"></h2>
                                <p class="text-gray-400 uppercase tracking-widest text-xs font-bold">{{ __('Prepare your wallet!') }}</p>
                            </div>
                        </div>

                        <div x-show="gameState === 'ticking'" class="w-full max-w-md flex flex-col items-center space-y-12">
                            <!-- Current Player -->
                            <div class="text-center animate-pulse">
                                <span class="text-xs font-bold uppercase tracking-widest text-rose-500">{{ __('Hold & Pass!') }}</span>
                                <div class="text-5xl font-black dark:text-white mt-2" x-text="players[currentPlayerIndex]"></div>
                            </div>

                            <!-- Animated Bomb -->
                            <div class="relative">
                                <div class="relative w-64 h-64 bg-gray-900 rounded-full flex items-center justify-center shadow-2xl overflow-visible tick-constant">
                                    <i class="fas fa-bomb text-9xl text-white"></i>
                                    <!-- Fuse Spark -->
                                    <div class="absolute -top-4 -right-2 w-8 h-8">
                                        <div class="w-full h-full bg-orange-500 rounded-full animate-ping opacity-75"></div>
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full scale-50 shadow-[0_0_20px_rgba(251,191,36,0.8)]"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- PASS Button -->
                            <button 
                                @click="passBomb()"
                                class="w-full py-8 bg-rose-600 hover:bg-rose-700 text-white text-4xl font-black rounded-3xl shadow-[0_15px_30px_rgba(225,29,72,0.4)] transition-all active:scale-95 active:translate-y-2 uppercase tracking-tighter"
                            >
                                {{ __('Pass IT!') }}
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Explosion State (End Screen Redesign) - Standard and LMS -->
                <div x-show="gameState === 'exploded' || gameState === 'overall_winner'" 
                    x-transition:enter="transition ease-out duration-300" 
                    x-transition:enter-start="opacity-0 scale-95" 
                    x-transition:enter-end="opacity-100 scale-100" 
                    class="absolute inset-0 z-50 bg-rose-600 overflow-y-auto p-4 sm:p-8 text-white text-center shadow-2xl backdrop-blur-xl transition-colors duration-300">
                    <div class="min-h-full flex flex-col items-center justify-center py-8">
                    <div class="relative mb-8 sm:mb-12">
                        <template x-if="gameState === 'overall_winner'">
                            <div class="absolute inset-0 bg-amber-400 blur-3xl opacity-40 rounded-full animate-pulse scale-150"></div>
                        </template>
                        <template x-if="gameState === 'exploded'">
                            <div class="absolute inset-0 bg-white blur-3xl opacity-30 rounded-full animate-pulse scale-150"></div>
                        </template>
                        <div class="relative w-28 h-28 sm:w-40 sm:h-40 bg-white rounded-full flex items-center justify-center border-4 sm:border-8 border-rose-500 shadow-[0_30px_60px_rgba(0,0,0,0.3)]">
                            <i class="fas" :class="gameState === 'overall_winner' ? 'fa-crown text-amber-500 text-4xl sm:text-7xl' : 'fa-bomb text-4xl sm:text-7xl text-rose-600'"></i>
                        </div>
                    </div>

                    <div class="mb-8 sm:mb-12">
                        <template x-if="gameState === 'overall_winner'">
                            <div>
                                <h1 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.5em] sm:tracking-[1em] text-white/50 mb-4 px-2">{{ __('ULTIMATE CHAMPION') }}</h1>
                                <div class="text-4xl sm:text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_5px_15px_rgba(0,0,0,0.3)]" x-text="winnerName"></div>
                                <p class="text-amber-200 text-base sm:text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic">{{ __('LAST MAN STANDING!') }}</p>
                            </div>
                        </template>
                        <template x-if="gameState === 'exploded'">
                            <div>
                                <h1 class="text-[10px] sm:text-xs font-black uppercase tracking-[0.5em] sm:tracking-[1em] text-white/50 mb-4 px-2">{{ __('Detonated') }}</h1>
                                <div class="text-4xl sm:text-7xl font-black text-white italic tracking-tighter uppercase drop-shadow-[0_5px_15px_rgba(0,0,0,0.3)]" x-text="players[currentPlayerIndex]"></div>
                                <p class="text-rose-200 text-base sm:text-lg mt-4 font-bold opacity-80 uppercase tracking-widest italic" x-text="lmsActive ? '{{ __('You are Eliminated!') }}' : '{{ __('Prepare your wallet!') }}'"></p>
                            </div>
                        </template>
                    </div>

                    <template x-if="gameState === 'overall_winner'">
                        <button @click="resetRound()" class="w-full max-w-sm py-4 sm:py-6 bg-amber-500 text-white font-black text-xl sm:text-2xl rounded-2xl sm:rounded-3xl shadow-[0_20px_40px_rgba(0,0,0,0.2)] transition-all hover:scale-105 uppercase tracking-tighter">
                            {{ __('NEW ROUND') }}
                        </button>
                    </template>
                    <template x-if="gameState === 'exploded'">
                        <button @click="resetGame()" class="w-full max-w-sm py-4 sm:py-6 bg-white text-rose-600 font-black text-xl sm:text-2xl rounded-2xl sm:rounded-3xl shadow-[0_20px_40px_rgba(0,0,0,0.2)] transition-all hover:scale-105 uppercase tracking-tighter">
                            <span x-text="lmsActive ? '{{ __('NEXT ROUND') }}' : '{{ __('NEXT SACRIFICE') }}'"></span>
                        </button>
                    </template>
                </div>
            </div>



            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bombGame', () => ({
                players: @json($names),
                currentPlayerIndex: 0,
                gameState: 'ready', // ready, ticking, exploded, overall_winner
                winnerName: '',
                lmsActive: {{ $lmsActive ? 'true' : 'false' }},
                timer: null,
                fuseTime: 0,
                round: 1,
                tickRate: 1000,
                startTime: 0,

                init() {
                    @if(isset($overallWinner))
                        this.winnerName = '{{ $overallWinner }}';
                        this.gameState = 'overall_winner';
                    @endif
                },

                startGame() {
                    const duration = (Math.floor(Math.random() * 20) + 10) * 1000; // 10-30 seconds
                    this.fuseTime = duration;
                    this.gameState = 'ticking';
                    this.startTime = Date.now();
                    this.tickRate = 800;
                    
                    this.startFuse();
                },

                startFuse() {
                    if (this.timer) clearTimeout(this.timer);
                    
                    const timeRemaining = this.fuseTime - (Date.now() - this.startTime);
                    
                    if (timeRemaining <= 0) {
                        this.explode();
                        return;
                    }

                    // Tick rate is constant to keep time secret
                    this.tickRate = 500;

                    this.timer = setTimeout(() => {
                        this.startFuse();
                    }, 50); // Small interval for smooth logic, tickRate controls UI feel
                },

                passBomb() {
                    if (this.gameState !== 'ticking') return;
                    this.currentPlayerIndex = (this.currentPlayerIndex + 1) % this.players.length;
                },

                explode() {
                    this.gameState = 'exploded';
                    this.saveWinner(this.players[this.currentPlayerIndex]);
                },

                resetGame() {
                    if (this.lmsActive) {
                        window.location.reload();
                        return;
                    }
                    if (this.timer) clearTimeout(this.timer);
                    this.gameState = 'ready';
                    this.round++;
                    // Start from the beginning of the list for the fresh bomb
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
                    }).catch(e => console.error('Failed to save', e));
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
                        li.className = 'flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-700 group hover:border-amber-400 transition-all duration-300 hover:shadow-md';
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
        @keyframes explosion {
            0% { transform: scale(0.1); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        .animate-explosion {
            animation: explosion 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .tick-constant { animation: pulse 0.6s infinite; }

        @keyframes pulse {
            0%, 100% { transform: scale(1); filter: brightness(1); }
            50% { transform: scale(1.05); filter: brightness(1.3) drop-shadow(0 0 15px rgba(225,29,72,0.4)); }
        }
    </style>
</x-app-layout>
