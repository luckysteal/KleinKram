<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Play to Pay Games') }}
        </h2>
        <div class="mt-4 px-6 py-8 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl">
            <h1 class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tighter italic">{{ __('Play to Pay Games') }}</h1>
            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-[0.3em]">{{ __('Mini Game Suite') }}</p>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-8 pb-12">
                <div class="mb-12 text-center max-w-2xl mx-auto">
                    <p class="text-lg text-gray-600 dark:text-gray-400 font-medium leading-relaxed italic">
                        {{ __('Choose a game to play with your friends and decide who deals with the bill!') }}
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($games as $game)
                    <a href="{{ route($game['route']) }}" class="group relative bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm hover:shadow-2xl transition-all duration-500 border border-gray-100 dark:border-gray-700 hover:-translate-y-2 flex flex-col items-center text-center overflow-hidden">
                        <!-- Decorative Background Gradient (Hidden by default, shows on hover) -->
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

                        <!-- Icon Container -->
                        <div class="w-20 h-20 mb-6 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-4xl group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 relative z-10">
                            <i class="fas fa-{{ $game['icon'] }}"></i>
                        </div>

                        <!-- Content -->
                        <div class="relative z-10">
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $game['name'] }}</h4>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">{{ $game['description'] }}</p>
                        </div>
                        
                        <!-- CTA -->
                        <div class="mt-auto relative z-10">
                            <span class="inline-flex items-center text-indigo-600 dark:text-indigo-400 font-bold group-hover:translate-x-2 transition-transform duration-300">
                                <span class="text-xs font-black uppercase tracking-widest">{{ __('Play Now') }}</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

</x-app-layout>
