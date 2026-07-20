@props(['pageCount' => 0, 'allCount' => 0, 'itemName' => 'Artikel'])

<x-multi-select.panel :item-name="$itemName">
    <div class="group relative">
        <button type="button" @click="togglePageSelection()" class="flex h-9 w-9 items-center justify-center rounded-xl text-purple-400 transition hover:bg-purple-500/10 hover:text-purple-300" aria-label="Diese Seite auswählen">
            <i class="fa-solid fa-check-double"></i>
        </button>
        <div class="pointer-events-none absolute left-14 top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-lg border border-gray-700 bg-gray-950 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-purple-300 opacity-0 shadow-xl transition-opacity group-hover:opacity-100">
            Alle {{ $pageCount }} {{ $itemName }} dieser Seite auswählen
        </div>
    </div>
    <div class="group relative">
        <button type="button" @click="selectAllMatching()" class="flex h-9 w-9 items-center justify-center rounded-xl text-cyan-400 transition hover:bg-cyan-500/10 hover:text-cyan-300" aria-label="Alle gefilterten Einträge auswählen">
            <i class="fa-solid fa-layer-group"></i>
        </button>
        <div class="pointer-events-none absolute left-14 top-1/2 z-50 -translate-y-1/2 whitespace-nowrap rounded-lg border border-gray-700 bg-gray-950 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-cyan-300 opacity-0 shadow-xl transition-opacity group-hover:opacity-100">
            Alle {{ $allCount }} gefilterten {{ $itemName }} auswählen
        </div>
    </div>
    {{ $slot }}
</x-multi-select.panel>
