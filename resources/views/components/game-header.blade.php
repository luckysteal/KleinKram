@props(['route' => 'games.index', 'reset' => false, 'hidePlayers' => false])

<div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700/50 flex items-center justify-between bg-white/50 dark:bg-gray-800/50 backdrop-blur-md sticky top-0 z-40">
    <div class="flex items-center gap-4">
        <a href="{{ route($route) }}" class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-xl transition-all duration-300 group" title="Back">
            <i class="fas fa-chevron-left text-lg group-hover:-translate-x-1 transition-transform"></i>
        </a>
        <div class="flex flex-col">
            {{ $slot }}
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        @if($reset)
            <button @click="{{ $reset }}" class="p-2.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-xl transition-all duration-300" title="Reset Game">
                <i class="fas fa-redo-alt"></i>
            </button>
        @endif

        <!-- LMS Toggle -->
        <div x-data="{ 
            lmsActive: {{ session('lms_active', false) ? 'true' : 'false' }},
            toggleLms() {
                this.lmsActive = !this.lmsActive;
                fetch('{{ route('tools.toggle-lms') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ active: this.lmsActive })
                }).then(() => window.location.reload());
            }
        }" class="flex items-center">
            <button @click="toggleLms()" :class="lmsActive ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600' : 'text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800'" class="flex items-center gap-2 p-2 sm:px-3 rounded-xl transition-all duration-300" :title="lmsActive ? 'Disable Last Man Standing' : 'Enable Last Man Standing'">
                <i class="fas fa-skull-crossbones text-lg"></i>
                <span class="hidden sm:inline font-bold text-xs uppercase tracking-wider" x-text="lmsActive ? 'LMS ON' : 'LMS OFF'"></span>
            </button>
        </div>

        <!-- Scoreboard Toggle -->
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('toggle-scoreboard'))" class="p-2.5 text-gray-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-xl transition-all duration-300" title="Toggle Scoreboard">
            <i class="fas fa-trophy text-lg"></i>
        </button>
        
        @unless($hidePlayers)
            <a href="{{ route('tools.player-selection') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0 text-sm">
                <i class="fas fa-users-cog mr-2"></i>
                <span class="hidden sm:inline">Players</span>
            </a>
        @endunless
    </div>
</div>

