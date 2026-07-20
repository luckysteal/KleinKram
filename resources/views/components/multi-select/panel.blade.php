@props([
    'show' => 'selectedIds.length > 0',
    'count' => 'selectedIds.length',
    'itemName' => 'Einträge',
    'clearAction' => 'clearSelection()',
])

<aside x-data="{ statusOpen: false }" x-show="{{ $show }}"
    {{ $attributes->merge(['class' => 'fixed left-3 top-1/2 z-50 flex w-16 -translate-y-1/2 flex-col items-center gap-3 rounded-2xl border border-gray-800 bg-gray-900/95 py-3 shadow-2xl backdrop-blur-xl']) }}
    x-transition:enter="transition-all ease-out duration-500"
    x-transition:enter-start="opacity-0 -translate-x-full scale-95"
    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
    x-transition:leave="transition-all ease-in duration-250"
    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-x-full scale-95"
    style="display: none;">
    <div class="group relative">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10 text-sm font-black text-cyan-400 ring-1 ring-cyan-500/30">
            <span x-text="{{ $count }}"></span>
        </div>
        <div class="pointer-events-none absolute left-14 top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-lg border border-gray-700 bg-gray-950 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-cyan-300 opacity-0 shadow-xl transition-opacity group-hover:opacity-100">
            <span x-text="{{ $count }}"></span> {{ $itemName }} ausgewählt
        </div>
    </div>
    <div class="h-px w-8 bg-gray-800"></div>
    <div class="flex flex-col items-center gap-2">
        {{ $slot }}
    </div>
    <div class="h-px w-8 bg-gray-800"></div>
    <div class="group relative">
        <button type="button" @click="{{ $clearAction }}" class="flex h-9 w-9 items-center justify-center rounded-xl text-rose-400 transition hover:bg-rose-500/10 hover:text-rose-300" aria-label="Auswahl aufheben">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="pointer-events-none absolute left-14 top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-lg border border-gray-700 bg-gray-950 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-200 opacity-0 shadow-xl transition-opacity group-hover:opacity-100">Auswahl aufheben</div>
    </div>
</aside>
