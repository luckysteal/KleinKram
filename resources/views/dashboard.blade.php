<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="flex-grow flex flex-col w-full relative">
        <div class="flex-grow flex flex-col w-full h-full p-6 sm:p-12">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 dark:border-gray-700 transition-colors duration-300">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-black mb-4">{{ __("Welcome back!") }}</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ __("You're logged in!") }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
