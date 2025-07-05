<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Play to Pay Games') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Choose a Game</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($games as $game)
                            <a href="{{ route($game['route']) }}" class="block p-6 bg-gray-100 rounded-lg shadow hover:shadow-md transition-shadow duration-300">
                                <div class="text-2xl mb-2">
                                    <!-- Placeholder for icon -->
                                    <i class="fas fa-{{ $game['icon'] }}"></i>
                                </div>
                                <h4 class="text-xl font-bold mb-2">{{ $game['name'] }}</h4>
                                <p class="text-gray-700">{{ $game['description'] }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
