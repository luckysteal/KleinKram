<x-app-layout>
    <div class="flex-grow flex flex-col w-full relative">
        <div class="flex-grow flex flex-col w-full h-full p-6 sm:p-12 space-y-6 sm:space-y-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
            
            <x-game-header hide-players="true" :back-to-game="$lastGameRoute">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Game Setup</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">Manage players & track scores</p>
            </x-game-header>


            <div class="space-y-6">
                <!-- Player Management Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] sm:rounded-3xl shadow-xl shadow-black/5 dark:shadow-indigo-500/5 p-6 sm:p-8 border border-gray-100 dark:border-gray-700 transition-colors duration-300">
                    <x-player-manager 
                        data-initial-players="{{ json_encode($names) }}" 
                        data-initial-last-winner="{{ json_encode(session('last_winner')) }}" 
                        data-initial-shuffle="{{ $shuffleActive ? 'true' : 'false' }}"
                    />
                </div>


            </div>

        </div>
    </div>
</x-app-layout>
