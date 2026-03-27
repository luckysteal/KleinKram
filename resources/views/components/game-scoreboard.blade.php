@props([
    'winnersTally' => [],
    'playersList' => [],
    'lmsActive' => false,
    'eliminatedPlayers' => []
])

<div 
    x-data="{ show: false }" 
    @toggle-scoreboard.window="(() => { console.log('toggle-scoreboard event received'); show = !show; })()"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-[100] overflow-hidden"
    aria-labelledby="slide-over-title" role="dialog" aria-modal="true"
>
    <!-- Backdrop -->
    <div 
        x-show="show"
        x-transition:enter="ease-in-out duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="show = false"
        class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
    ></div>

    <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
        <div 
            x-show="show"
            x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen max-w-md"
        >
            <div class="h-full flex flex-col bg-white dark:bg-gray-800 shadow-2xl border-l border-gray-100 dark:border-gray-700">
                <!-- Header -->
                <div class="px-6 py-8 flex items-center justify-between border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                    <div class="flex flex-col">
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter flex items-center gap-3">
                            <i class="fas fa-trophy text-amber-500"></i> {{ __('Scoreboard') }}
                        </h3>
                        @if($lmsActive)
                        <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-1">
                            <i class="fas fa-skull-crossbones mr-1"></i> LAST MAN STANDING MODE
                        </span>
                        @endif
                    </div>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-2 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-grow overflow-y-auto p-6 space-y-6">
                    
                    @if($lmsActive && !empty($playersList))
                        <!-- LMS Section -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">{{ __('Round Status') }}</h4>
                            <div class="grid gap-3">
                                @foreach($playersList as $playerName)
                                    @php
                                        $isEliminated = in_array($playerName, $eliminatedPlayers);
                                    @endphp
                                    <div class="flex justify-between items-center p-4 {{ $isEliminated ? 'bg-rose-50/50 dark:bg-rose-900/10 grayscale opacity-60' : 'bg-emerald-50/50 dark:bg-emerald-900/10' }} rounded-2xl border {{ $isEliminated ? 'border-rose-100 dark:border-rose-900/30' : 'border-emerald-100 dark:border-emerald-900/30' }} transition-all duration-300">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $isEliminated ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-600' : 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600' }}">
                                                <i class="fas {{ $isEliminated ? 'fa-skull' : 'fa-heartbeat' }} text-sm"></i>
                                            </div>
                                            <span class="text-gray-800 dark:text-gray-100 font-extrabold text-lg">{{ $playerName }}</span>
                                        </div>
                                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg {{ $isEliminated ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white' }}">
                                            {{ $isEliminated ? __('Eliminated') : __('Alive') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Winners Tally Section -->
                    <div class="space-y-4 {{ $lmsActive ? 'mt-12 pt-12 border-t border-gray-100 dark:border-gray-700' : '' }}">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4">{{ __('Global Wins') }}</h4>
                        
                        <div id="no-wins-text" class="flex flex-col items-center justify-center py-12 text-center space-y-4 opacity-50 {{ !empty($winnersTally) ? 'hidden' : '' }}">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <i class="fas fa-medal text-gray-300 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 font-bold uppercase tracking-widest text-[10px]">{{ __('No winners recorded yet') }}</p>
                        </div>

                        <ul id="scoreboard-list" class="space-y-3 {{ empty($winnersTally) ? 'hidden' : '' }}">
                            @foreach(collect($winnersTally)->sortByDesc(function ($tally) { return $tally; }) as $playerName => $wins)
                                <li class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600 group hover:border-amber-400 transition-all duration-300 hover:shadow-md" data-player="{{ $playerName }}">
                                    <div class="flex items-center gap-4">
                                        <span class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-700 dark:text-amber-400 font-black text-sm">
                                            {{ $loop->iteration }}
                                        </span>
                                        <span class="text-gray-800 dark:text-gray-100 font-extrabold text-lg">{{ $playerName }}</span>
                                    </div>
                                    <span class="bg-amber-500 text-white px-4 py-1.5 rounded-full text-xs font-black shadow-lg shadow-amber-500/20 group-hover:scale-110 transition-transform win-count">
                                        {{ $wins }} {{ __('WINS') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-8 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 text-center">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">{{ __('Winners are tracked per session') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
