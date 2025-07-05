<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $page->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ $page->content }}

                    @if($page->badges)
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Tools & Badges</h3>
                            <div class="flex flex-wrap gap-4">
                                @foreach($page->badges as $badge)
                                    <a href="{{ $badge['url'] }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition ease-in-out duration-150">
                                        {{ $badge['name'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
