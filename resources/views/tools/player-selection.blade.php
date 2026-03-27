<x-app-layout>
    <div class="py-6 sm:py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">
            
            <x-game-header hide-players="true">
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">Game Setup</h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest">Manage players & track scores</p>
            </x-game-header>


            <div class="space-y-6">
                <!-- Player Management Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] sm:rounded-3xl shadow-xl shadow-black/5 dark:shadow-indigo-500/5 p-6 sm:p-8 border border-gray-100 dark:border-gray-700">
                    <x-player-manager data-initial-players="{{ json_encode($names) }}" data-initial-last-winner="{{ json_encode(session('last_winner')) }}" />
                </div>


            </div>

        </div>
    </div>
</x-app-layout>
