@extends('sck.layouts.sck')

@section('content')
<div class="space-y-6" x-data="{ 
    openAddModal: false, 
    openEditModal: false, 
    openPrintModal: false,
    openZoomModal: false,
    addArtikelNr: '',
    addArtikelNrLoading: false,
    addArtikelNrError: '',
    editArtikelNrError: '',
    selectedItem: {
        id: '',
        bezeichnung: '',
        geraet: '',
        lieferant: '',
        ek_ohne_st: '',
        vk_ohne_st: '',
        alte_artikelnummer: '',
        neue_artikelnummer: '',
        stueckzahl: 0,
        kommentar: ''
    },
    printSize: 'small',
    printFields: {
        geraet: true,
        lieferant: true,
        ek: false,
        vk: false,
        alte_nr: false,
        neue_nr: true,
        kommentar: false
    },
    tplQuery: '',
    tplResults: [],
    tplLoading: false,
    searchTemplate() {
        if (this.tplQuery.length < 2) {
            this.tplResults = [];
            return;
        }
        this.tplLoading = true;
        fetch('{{ route('sck.lager.search-json') }}?q=' + encodeURIComponent(this.tplQuery))
            .then(res => res.json())
            .then(data => {
                this.tplResults = data;
                this.tplLoading = false;
            })
            .catch(() => {
                this.tplLoading = false;
            });
    },
    applyTemplate(item) {
        document.getElementById('bezeichnung').value = item.bezeichnung;
        document.getElementById('geraet').value = item.geraet;
        document.getElementById('lieferant').value = item.lieferant;
        document.getElementById('ek_ohne_st').value = item.ek_ohne_st;
        document.getElementById('vk_ohne_st').value = item.vk_ohne_st;
        document.getElementById('alte_artikelnummer').value = item.alte_artikelnummer || '';
        document.getElementById('kommentar').value = item.kommentar || '';
        this.tplQuery = '';
        this.tplResults = [];
    },
    async fetchNewArtikelNr() {
        this.addArtikelNrLoading = true;
        this.addArtikelNrError = '';
        try {
            const res = await fetch('{{ route('sck.lager.generate-number') }}');
            const data = await res.json();
            this.addArtikelNr = data.number;
        } catch (e) {
            this.addArtikelNrError = 'Fehler beim Generieren.';
        }
        this.addArtikelNrLoading = false;
    },
    async rerollEditArtikelNr() {
        this.editArtikelNrError = '';
        try {
            const res = await fetch('{{ route('sck.lager.generate-number') }}');
            const data = await res.json();
            this.selectedItem.neue_artikelnummer = data.number;
        } catch (e) {
            this.editArtikelNrError = 'Fehler beim Generieren.';
        }
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('sck.dashboard', ['no_redirect' => 1]) }}" class="text-gray-400 hover:text-cyan-400 transition-colors">
                    <i class="fa-solid fa-house-laptop"></i>
                </a>
                <span class="text-gray-600">/</span>
                <span class="text-gray-300 font-semibold">Lagersystem</span>
            </div>
            <h2 class="text-2xl font-black mt-1">Lagerbestand & Artikelliste</h2>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- QR Scanner button -->
            <a href="{{ route('sck.lager.scan') }}" class="btn-neon-purple text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center space-x-2 transition-all duration-200 has-tooltip">
                <i class="fa-solid fa-camera"></i>
                <span>QR-Scanner öffnen</span>
                <div class="tooltip-item tooltip-left">Öffnet die Smartphone-Kamera, um QR-Codes direkt ein- oder auszulesen und den Bestand anzupassen.</div>
            </a>

            <!-- Add new item button -->
            <button @click="openAddModal = true; fetchNewArtikelNr()" class="btn-neon-cyan text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center space-x-2 transition-all duration-200 has-tooltip">
                <i class="fa-solid fa-circle-plus"></i>
                <span>Neuer Artikel</span>
                <div class="tooltip-item">Fügt einen neuen Artikel im Lagersystem hinzu. Die 5-stellige Artikelnummer wird automatisch generiert.</div>
            </button>

            <!-- Export dropdown (using Alpine.js) -->
            <div x-data="{ openExport: false }" class="relative inline-block text-left">
                <button @click="openExport = !openExport" class="bg-gray-800 hover:bg-gray-700 text-gray-200 border border-gray-700 px-4 py-2 rounded-xl text-sm font-semibold flex items-center space-x-2 transition-colors has-tooltip">
                    <i class="fa-solid fa-file-export"></i>
                    <span>Exportieren</span>
                    <i class="fa-solid fa-chevron-down text-xxs"></i>
                    <div class="tooltip-item">Lädt die Produktliste als UTF-8 codierte CSV-Datei für Excel herunter.</div>
                </button>
                <div x-show="openExport" @click.away="openExport = false" class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-2xl bg-gray-900 border border-gray-800 focus:outline-none z-50 glass-panel" x-cloak>
                    <div class="py-1">
                        <a href="{{ route('sck.lager.export', ['include_stock' => 1]) }}" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors">
                            <i class="fa-solid fa-file-invoice-dollar text-cyan-400"></i>
                            <span>Mit aktuellem Lagerbestand</span>
                        </a>
                        <a href="{{ route('sck.lager.export', ['include_stock' => 0]) }}" class="flex items-center space-x-2 px-4 py-2.5 text-sm text-gray-300 hover:bg-cyan-500/10 hover:text-cyan-400 transition-colors">
                            <i class="fa-solid fa-file-lines text-purple-400"></i>
                            <span>Ohne Lagerbestand (nur Katalog)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Artikel im System</p>
                <h4 class="text-xl font-bold mt-1">{{ \App\Models\Sck\SckWarehouseItem::count() }}</h4>
            </div>
            <div class="text-cyan-400"><i class="fa-solid fa-box text-xl"></i></div>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Geringer Bestand</p>
                <h4 class="text-xl font-bold mt-1 text-amber-400">{{ \App\Models\Sck\SckWarehouseItem::where('stueckzahl', '<', 5)->count() }}</h4>
            </div>
            <div class="text-amber-400"><i class="fa-solid fa-circle-exclamation text-xl"></i></div>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Gesamtwert (EK)</p>
                <h4 class="text-xl font-bold mt-1 text-emerald-400">
                    {{ number_format(\App\Models\Sck\SckWarehouseItem::sum(\DB::raw('ek_ohne_st * stueckzahl')), 2, ',', '.') }} €
                </h4>
            </div>
            <div class="text-emerald-400"><i class="fa-solid fa-coins text-xl"></i></div>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-gray-800 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase">Gesamteinheiten</p>
                <h4 class="text-xl font-bold mt-1 text-purple-400">{{ \App\Models\Sck\SckWarehouseItem::sum('stueckzahl') }} Stk.</h4>
            </div>
            <div class="text-purple-400"><i class="fa-solid fa-warehouse text-xl"></i></div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="glass-panel p-4 rounded-xl border border-gray-800">
        <form action="{{ route('sck.lager.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-grow">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Suche nach Bezeichnung, Gerät, Lieferant oder Artikelnummer..." class="sck-input pl-10 pr-4 py-2 rounded-xl text-sm w-full">
            </div>
            <div class="flex gap-2">
                @if(request('search') || request('sort_by'))
                    <a href="{{ route('sck.lager.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-xl text-sm font-semibold flex items-center space-x-1.5 transition-colors">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                        <span>Zurücksetzen</span>
                    </a>
                @endif
                <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white px-5 py-2 rounded-xl text-sm font-bold transition-colors">
                    Filter anwenden
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="glass-panel rounded-2xl border border-gray-800 overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-950/65 text-gray-400 border-b border-gray-800 text-xs uppercase font-bold tracking-wider">
                        <th class="py-4 px-5">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'bezeichnung', 'sort_dir' => request('sort_by') === 'bezeichnung' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 hover:text-cyan-400 transition-colors">
                                <span>Bezeichnung</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                            </a>
                        </th>
                        <th class="py-4 px-4">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'geraet', 'sort_dir' => request('sort_by') === 'geraet' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>Gerät</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Der Gerätetyp oder die Kategorie des Artikels.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'lieferant', 'sort_dir' => request('sort_by') === 'lieferant' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 hover:text-cyan-400 transition-colors">
                                <span>Lieferant</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-right">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'ek_ohne_st', 'sort_dir' => request('sort_by') === 'ek_ohne_st' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-end space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>EK o. St.</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Einkaufspreis netto (ohne Steuer) pro Einheit.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-right">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'vk_ohne_st', 'sort_dir' => request('sort_by') === 'vk_ohne_st' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-end space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>VK o. St.</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Verkaufspreis netto (ohne Steuer) pro Einheit.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-center">Alte Nr.</th>
                        <th class="py-4 px-4 text-center">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'neue_artikelnummer', 'sort_dir' => request('sort_by') === 'neue_artikelnummer' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-center space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>Neue Nr.</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Systemgenerierte, eindeutige 5-stellige Artikelnummer.</div>
                            </a>
                        </th>
                        <th class="py-4 px-4 text-center">QR Code</th>
                        <th class="py-4 px-4 text-center">
                            <a href="{{ route('sck.lager.index', array_merge(request()->query(), ['sort_by' => 'stueckzahl', 'sort_dir' => request('sort_by') === 'stueckzahl' && request('sort_dir') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-center space-x-1 hover:text-cyan-400 transition-colors has-tooltip">
                                <span>Bestand</span>
                                <i class="fa-solid fa-sort text-[10px]"></i>
                                <div class="tooltip-item">Aktueller Lagerbestand. Kann über die Tasten direkt erhöht oder verringert werden.</div>
                            </a>
                        </th>
                        <th class="py-4 px-5">Kommentar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800 text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-900/30 transition-colors cursor-context-menu"
                            data-id="{{ $item->id }}"
                            data-bezeichnung="{{ $item->bezeichnung }}"
                            data-geraet="{{ $item->geraet }}"
                            data-lieferant="{{ $item->lieferant }}"
                            data-ek="{{ number_format($item->ek_ohne_st, 2, '.', '') }}"
                            data-vk="{{ number_format($item->vk_ohne_st, 2, '.', '') }}"
                            data-alte-nr="{{ $item->alte_artikelnummer ?? '' }}"
                            data-neue-nr="{{ $item->neue_artikelnummer }}"
                            data-stueckzahl="{{ $item->stueckzahl }}"
                            data-kommentar="{{ $item->kommentar ?? '' }}">
                            <td class="py-4 px-5 font-semibold text-gray-200">
                                <a href="{{ route('sck.lager.artikel', $item->neue_artikelnummer) }}" class="hover:text-cyan-400 transition-colors">
                                    {{ $item->bezeichnung }}
                                </a>
                            </td>
                            <td class="py-4 px-4 text-gray-300">{{ $item->geraet }}</td>
                            <td class="py-4 px-4 text-gray-400">{{ $item->lieferant }}</td>
                            <td class="py-4 px-4 text-right text-gray-300 font-mono">{{ number_format($item->ek_ohne_st, 2, ',', '.') }} €</td>
                            <td class="py-4 px-4 text-right text-gray-300 font-mono">{{ number_format($item->vk_ohne_st, 2, ',', '.') }} €</td>
                            <td class="py-4 px-4 text-center text-gray-500 font-mono">{{ $item->alte_artikelnummer ?? '-' }}</td>
                            <td class="py-4 px-4 text-center text-cyan-400 font-mono font-bold">{{ $item->neue_artikelnummer }}</td>
                            <td class="py-3 px-4 text-center">
                                <!-- Trigger click for modal zoom -->
                                <div class="inline-block">
                                    <div @click="selectedItem = {
                                        id: '{{ $item->id }}',
                                        bezeichnung: '{{ addslashes($item->bezeichnung) }}',
                                        geraet: '{{ addslashes($item->geraet) }}',
                                        lieferant: '{{ addslashes($item->lieferant) }}',
                                        ek_ohne_st: '{{ number_format($item->ek_ohne_st, 2, '.', '') }}',
                                        vk_ohne_st: '{{ number_format($item->vk_ohne_st, 2, '.', '') }}',
                                        alte_artikelnummer: '{{ $item->alte_artikelnummer ?? '' }}',
                                        neue_artikelnummer: '{{ $item->neue_artikelnummer }}',
                                        stueckzahl: '{{ $item->stueckzahl }}',
                                        kommentar: '{{ addslashes($item->kommentar ?? '') }}'
                                     };
                                     openZoomModal = true;
                                     renderGlobalZoomQR();" 
                                     class="cursor-pointer bg-white p-1 rounded inline-block shadow hover:scale-105 transition-transform has-tooltip">
                                        <canvas class="qr-canvas inline-block" data-url="{{ route('sck.lager.artikel', $item->neue_artikelnummer) }}" width="40" height="40"></canvas>
                                        <div class="tooltip-item">Klicken, um den Barcode vergrößert anzuzeigen oder herunterzuladen.</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center justify-center">
                                    <form action="{{ route('sck.lager.update-stock') }}" method="POST" class="flex items-center space-x-1.5 bg-gray-900/60 p-1 rounded-lg border border-gray-800">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        
                                        <!-- Decrement button -->
                                        <button type="submit" name="action" value="remove" class="w-6 h-6 rounded bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white flex items-center justify-center transition-all font-bold text-xs">
                                            -
                                        </button>
                                        
                                        <!-- Editable quantity input -->
                                        <input type="number" name="quantity" value="1" min="1" class="sck-input w-9 text-center text-xs py-0.5 px-0 rounded border-0 bg-transparent text-gray-200 font-bold font-mono">
                                        
                                        <!-- Increment button -->
                                        <button type="submit" name="action" value="add" class="w-6 h-6 rounded bg-emerald-500/10 hover:bg-emerald-600 text-emerald-400 hover:text-white flex items-center justify-center transition-all font-bold text-xs">
                                            +
                                        </button>
                                    </form>
                                    
                                    <!-- Stock label -->
                                    <span class="ml-3 font-mono font-bold w-12 text-left" :class="{{ $item->stueckzahl }} < 5 ? 'text-amber-400 font-black animate-pulse' : 'text-gray-200'">
                                        {{ $item->stueckzahl }} Stk.
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-5 text-gray-500 text-xs italic max-w-xs truncate" title="{{ $item->kommentar }}">
                                {{ $item->kommentar ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-gray-500">
                                <i class="fa-solid fa-ban text-3xl mb-2 block"></i>
                                <span>Keine Artikel im Lager gefunden.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="bg-gray-950/45 px-5 py-4 border-t border-gray-800">
                {{ $items->links() }}
            </div>
        @endif
    </div>



    <!-- Create Product Modal Overlay -->
    <div x-show="openAddModal" @keydown.escape.window="openAddModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75" x-cloak>
        <div @click.away="openAddModal = false" class="glass-panel max-w-lg w-full rounded-2xl border border-gray-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/40">
                <h3 class="text-lg font-black flex items-center space-x-2">
                    <i class="fa-solid fa-circle-plus text-cyan-400"></i>
                    <span>Neuen Artikel anlegen</span>
                </h3>
                <button @click="openAddModal = false" class="text-gray-500 hover:text-gray-300 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="{{ route('sck.lager.store') }}" method="POST" class="p-6 overflow-y-auto space-y-4 flex-grow text-left">
                @csrf
                
                <!-- Autocomplete Template Search -->
                <div class="bg-cyan-950/20 border border-cyan-800/30 rounded-xl p-3.5 space-y-2 relative" x-data="{ tplOpen: false }">
                    <label class="text-xxs font-black text-cyan-400 uppercase tracking-widest block">
                        Bestehenden Artikel als Vorlage verwenden
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                        <input type="text" x-model="tplQuery" @input.debounce.300ms="searchTemplate()" @focus="tplOpen = true" placeholder="Tippe zur Suche nach Name, alter/neuer Nummer..." class="sck-input text-xs rounded-lg pl-9 pr-4 py-2 w-full">
                    </div>
                    
                    <div x-show="tplOpen && tplResults.length > 0" @click.away="tplOpen = false" class="absolute left-3 right-3 top-full mt-1 bg-gray-950 border border-gray-800 rounded-xl shadow-2xl z-50 max-h-48 overflow-y-auto divide-y divide-gray-850 glass-panel" x-cloak>
                        <template x-for="item in tplResults" :key="item.id">
                            <button type="button" @click="applyTemplate(item); tplOpen = false" class="w-full text-left px-4 py-2.5 hover:bg-cyan-500/10 text-xs flex justify-between items-center transition-colors">
                                <div>
                                    <span class="font-bold text-gray-200 block" x-text="item.bezeichnung"></span>
                                    <span class="text-gray-500 text-xxs block" x-text="'Lieferant: ' + item.lieferant + ' | Gerät: ' + item.geraet"></span>
                                </div>
                                <span class="text-cyan-400 font-mono font-bold" x-text="item.neue_artikelnummer"></span>
                            </button>
                        </template>
                    </div>
                    <div x-show="tplLoading" class="absolute right-6 bottom-5">
                        <i class="fa-solid fa-circle-notch animate-spin text-cyan-400 text-xs"></i>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="bezeichnung" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Bezeichnung *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Der offizielle Name des Artikels oder Materials (z. B. 'Akkuschrauber GSR 18V').</div>
                        </label>
                        <input type="text" name="bezeichnung" id="bezeichnung" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="geraet" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Gerätetyp / Kategorie *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Zweck oder Art (z. B. 'Verbrauchsmaterial', 'Elektrowerkzeug', 'Schutzkleidung').</div>
                        </label>
                        <input type="text" name="geraet" id="geraet" required list="category-list" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                        <datalist id="category-list">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="lieferant" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Lieferant *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Unternehmen, das den Artikel liefert (z. B. 'Würth GmbH', 'Bosch Professional').</div>
                        </label>
                        <input type="text" name="lieferant" id="lieferant" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="ek_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>EK Netto (in €) *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Einkaufspreis ohne Mehrwertsteuer. Format: Zahl mit Punkt oder Komma (z. B. '45.50').</div>
                        </label>
                        <input type="number" name="ek_ohne_st" id="ek_ohne_st" step="0.01" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="vk_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>VK Netto (in €) *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Verkaufspreis ohne Mehrwertsteuer. Format: Zahl (z. B. '89.90').</div>
                        </label>
                        <input type="number" name="vk_ohne_st" id="vk_ohne_st" step="0.01" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="alte_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Alte Artikelnummer</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Die Artikelnummer aus dem vorherigen Softwaresystem zur Rückverfolgung.</div>
                        </label>
                        <input type="text" name="alte_artikelnummer" id="alte_artikelnummer" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <!-- Neue Artikelnummer field -->
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="add_neue_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1">
                            <span>Neue Artikelnummer</span>
                            <span class="ml-1 text-[10px] font-bold text-amber-400 uppercase tracking-wider">5-stellig</span>
                        </label>
                        <!-- Warning Banner -->
                        <div class="sck-artNr-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>
                                <strong>Wichtig:</strong> Die Artikelnummer ist dauerhaft mit dem QR-Code verknüpft. Nach dem Anlegen sollte sie <strong>nicht mehr geändert werden</strong>, da sonst alle gedruckten Etiketten ungültig werden.
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 mt-1">
                            <input type="text" name="neue_artikelnummer" id="add_neue_artikelnummer"
                                   x-model="addArtikelNr"
                                   maxlength="5" minlength="5" pattern="[0-9]{5}"
                                   required
                                   placeholder="z. B. 48291"
                                   class="sck-input text-sm rounded-lg px-3 py-2 font-mono font-bold flex-grow">
                            <button type="button" @click="fetchNewArtikelNr()"
                                    :disabled="addArtikelNrLoading"
                                    class="sck-reroll-btn"
                                    title="Neue zufällige Nummer generieren">
                                <i class="fa-solid fa-dice" :class="addArtikelNrLoading ? 'animate-spin' : ''"></i>
                                <span x-show="!addArtikelNrLoading">Neu würfeln</span>
                                <span x-show="addArtikelNrLoading" x-cloak>...</span>
                            </button>
                        </div>
                        <p x-show="addArtikelNrError" x-text="addArtikelNrError" class="text-red-400 text-xs mt-1" x-cloak></p>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="stueckzahl" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Anfangsbestand *</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Die anfänglich verfügbare Stückzahl, die sich im Lager befindet.</div>
                        </label>
                        <input type="number" name="stueckzahl" id="stueckzahl" value="0" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="kommentar" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1 has-tooltip">
                            <span>Kommentar</span>
                            <i class="fa-solid fa-circle-question text-cyan-400 text-[10px]"></i>
                            <div class="tooltip-item">Zusätzliche Notizen (z. B. Lagerfach, Maße, Verpackungseinheiten).</div>
                        </label>
                        <textarea name="kommentar" id="kommentar" rows="3" class="sck-input text-sm rounded-lg px-3 py-2 mt-1"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-850">
                    <button type="button" @click="openAddModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Abbrechen
                    </button>
                    <button type="submit" class="btn-neon-cyan text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                        Artikel speichern
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal Overlay -->
    <div x-show="openEditModal" @keydown.escape.window="openEditModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75" x-cloak>
        <div @click.away="openEditModal = false" class="glass-panel max-w-lg w-full rounded-2xl border border-gray-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/40">
                <h3 class="text-lg font-black flex items-center space-x-2">
                    <i class="fa-solid fa-pen text-cyan-400"></i>
                    <span>Artikel bearbeiten</span>
                </h3>
                <button @click="openEditModal = false" class="text-gray-500 hover:text-gray-300 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form action="{{ route('sck.lager.update') }}" method="POST" class="p-6 overflow-y-auto space-y-4 flex-grow text-left">
                @csrf
                <input type="hidden" name="id" :value="selectedItem.id">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="edit_bezeichnung" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bezeichnung *</label>
                        <input type="text" name="bezeichnung" id="edit_bezeichnung" x-model="selectedItem.bezeichnung" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_geraet" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gerätetyp / Kategorie *</label>
                        <input type="text" name="geraet" id="edit_geraet" x-model="selectedItem.geraet" required list="category-list" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_lieferant" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lieferant *</label>
                        <input type="text" name="lieferant" id="edit_lieferant" x-model="selectedItem.lieferant" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_ek_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">EK Netto (in €) *</label>
                        <input type="number" name="ek_ohne_st" id="edit_ek_ohne_st" step="0.01" min="0" x-model="selectedItem.ek_ohne_st" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_vk_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">VK Netto (in €) *</label>
                        <input type="number" name="vk_ohne_st" id="edit_vk_ohne_st" step="0.01" min="0" x-model="selectedItem.vk_ohne_st" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_alte_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Alte Artikelnummer</label>
                        <input type="text" name="alte_artikelnummer" id="edit_alte_artikelnummer" x-model="selectedItem.alte_artikelnummer" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <!-- Neue Artikelnummer field (editable) -->
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="edit_neue_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1">
                            <span>Neue Artikelnummer</span>
                            <span class="ml-1 text-[10px] font-bold text-amber-400 uppercase tracking-wider">5-stellig</span>
                        </label>
                        <!-- Warning Banner -->
                        <div class="sck-artNr-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>
                                <strong>Achtung:</strong> Das Ändern der Artikelnummer macht alle bestehenden QR-Etiketten ungültig. Nur ändern, wenn wirklich nötig und alle Etiketten neu gedruckt werden.
                            </span>
                        </div>
                        <div class="flex items-center space-x-2 mt-1">
                            <input type="text" name="neue_artikelnummer" id="edit_neue_artikelnummer"
                                   x-model="selectedItem.neue_artikelnummer"
                                   maxlength="5" minlength="5" pattern="[0-9]{5}"
                                   required
                                   class="sck-input text-sm rounded-lg px-3 py-2 font-mono font-bold flex-grow">
                            <button type="button" @click="rerollEditArtikelNr()"
                                    class="sck-reroll-btn"
                                    title="Neue zufällige Nummer generieren">
                                <i class="fa-solid fa-dice"></i>
                                <span>Neu würfeln</span>
                            </button>
                        </div>
                        <p x-show="editArtikelNrError" x-text="editArtikelNrError" class="text-red-400 text-xs mt-1" x-cloak></p>
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_stueckzahl" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bestand *</label>
                        <input type="number" name="stueckzahl" id="edit_stueckzahl" x-model="selectedItem.stueckzahl" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="edit_kommentar" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kommentar</label>
                        <textarea name="kommentar" id="edit_kommentar" rows="3" x-model="selectedItem.kommentar" class="sck-input text-sm rounded-lg px-3 py-2 mt-1"></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-850">
                    <button type="button" @click="openEditModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                        Abbrechen
                    </button>
                    <button type="submit" class="btn-neon-cyan text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Label Print Modal Overlay -->
    <div x-show="openPrintModal" @keydown.escape.window="openPrintModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75" x-cloak x-init="$watch('printSize', () => renderPrintQR()); $watch('openPrintModal', val => { if (val) $nextTick(() => renderPrintQR()); })">
        <div @click.away="openPrintModal = false" class="glass-panel max-w-2xl w-full rounded-2xl border border-gray-800 overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-gray-850 flex items-center justify-between bg-gray-950/40">
                <h3 class="text-lg font-black flex items-center space-x-2">
                    <i class="fa-solid fa-print text-cyan-400"></i>
                    <span>Label drucken - Konfiguration</span>
                </h3>
                <button @click="openPrintModal = false" class="text-gray-500 hover:text-gray-300 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-6 flex-grow text-left grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Settings Panel -->
                <div class="space-y-4">
                    <!-- Size Selector -->
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">Label-Größe</span>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="printSize = 'small'; renderPrintQR();" class="px-4 py-2 rounded-lg text-xs font-bold border transition-all"
                                    :class="printSize === 'small' ? 'bg-cyan-500/20 border-cyan-500 text-cyan-400' : 'bg-gray-900 border-gray-800 text-gray-400 hover:text-gray-200'">
                                Klein (Space-efficient)
                            </button>
                            <button type="button" @click="printSize = 'big'; renderPrintQR();" class="px-4 py-2 rounded-lg text-xs font-bold border transition-all"
                                    :class="printSize === 'big' ? 'bg-cyan-500/20 border-cyan-500 text-cyan-400' : 'bg-gray-900 border-gray-800 text-gray-400 hover:text-gray-200'">
                                Groß (Besser lesbar)
                            </button>
                        </div>
                    </div>

                    <!-- Fields Selector -->
                    <div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-2">Zu druckende Felder</span>
                        <div class="space-y-2 bg-gray-950/40 p-3 rounded-lg border border-gray-850">
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.geraet" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Gerät / Kategorie</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.lieferant" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Lieferant</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.ek" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>EK o. St.</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.vk" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>VK o. St.</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.alte_nr" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Alte Artikelnummer</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.neue_nr" disabled class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span class="text-gray-500">Neue Artikelnummer (Immer gedruckt)</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs text-gray-300 cursor-pointer">
                                <input type="checkbox" x-model="printFields.kommentar" class="rounded bg-gray-900 border-gray-800 text-cyan-500 focus:ring-0 focus:ring-offset-0">
                                <span>Kommentar / Lagerhinweis</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="flex flex-col items-center justify-center space-y-4">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block text-center">Live-Vorschau</span>
                    
                    <div class="border border-gray-800 p-4 rounded-xl bg-gray-950 flex flex-col items-center justify-center overflow-auto max-w-full min-h-[220px] w-full">
                        <!-- Printed label frame -->
                        <div id="print-preview-container" class="print-label-container" :class="printSize === 'small' ? 'size-small' : 'size-big'">
                            <div class="flex items-center w-full h-full space-x-4">
                                <div class="flex-shrink-0 bg-white p-1 rounded border border-gray-200">
                                    <canvas id="print-label-qr"></canvas>
                                </div>
                                <div class="flex-grow flex flex-col justify-between h-full overflow-hidden text-left text-black">
                                    <div>
                                        <h3 class="font-black leading-tight text-black" :class="printSize === 'small' ? 'text-sm' : 'text-lg'" x-text="selectedItem.bezeichnung"></h3>
                                        
                                        <div class="grid grid-cols-2 gap-x-2 mt-1 text-[9px] text-gray-700" :class="printSize === 'small' ? '' : 'text-xs'">
                                            <div x-show="printFields.geraet" class="truncate font-medium"><span class="font-bold">Kat:</span> <span x-text="selectedItem.geraet"></span></div>
                                            <div x-show="printFields.lieferant" class="truncate font-medium"><span class="font-bold">Lief:</span> <span x-text="selectedItem.lieferant"></span></div>
                                            <div x-show="printFields.ek" class="truncate font-medium"><span class="font-bold">EK:</span> <span x-text="selectedItem.ek_ohne_st"></span> €</div>
                                            <div x-show="printFields.vk" class="truncate font-medium"><span class="font-bold">VK:</span> <span x-text="selectedItem.vk_ohne_st"></span> €</div>
                                            <div x-show="printFields.alte_nr" class="truncate font-medium"><span class="font-bold">Alt:</span> <span x-text="selectedItem.alte_artikelnummer || '-'"></span></div>
                                            <div x-show="printFields.neue_nr" class="truncate font-medium"><span class="font-bold">Neu:</span> <span x-text="selectedItem.neue_artikelnummer"></span></div>
                                        </div>
                                    </div>
                                    <div x-show="printFields.kommentar" class="border-t border-gray-300 pt-1 mt-1 text-gray-600 leading-tight truncate text-[8px]" :class="printSize === 'small' ? '' : 'text-[9px]'" x-text="selectedItem.kommentar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 p-6 border-t border-gray-850 bg-gray-950/20">
                <button type="button" @click="openPrintModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    Schließen
                </button>
                <button type="button" @click="printLabel(selectedItem.neue_artikelnummer, printSize)" class="btn-neon-cyan text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all duration-200">
                    <i class="fa-solid fa-print mr-1"></i>
                    Jetzt drucken
                </button>
            </div>
        </div>
    </div>

    <!-- Global QR Zoom Modal -->
    <div x-show="openZoomModal" @keydown.escape.window="openZoomModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80" x-cloak>
        <div @click.away="openZoomModal = false" class="glass-panel p-8 rounded-2xl border border-gray-800 text-center max-w-sm w-full space-y-6">
            <h3 class="text-xl font-bold" x-text="selectedItem.bezeichnung"></h3>
            <p class="text-xs text-gray-400">Artikelnummer: <span x-text="selectedItem.neue_artikelnummer"></span></p>
            <div class="bg-white p-4 rounded-xl inline-block">
                <canvas id="global-zoom-qr" width="180" height="180"></canvas>
            </div>
            <div class="flex justify-center space-x-3 pt-2">
                <button @click="openZoomModal = false" class="bg-gray-800 hover:bg-gray-700 text-gray-300 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">
                    Schließen
                </button>
                <button @click="openPrintModal = true; openZoomModal = false" class="btn-neon-cyan text-white px-4 py-2 rounded-xl text-sm font-bold transition-colors">
                    Drucken
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Left- & Right-Click Context Menu -->
    <div id="custom-context-menu" class="context-menu hidden" x-cloak>
        <a :href="'{{ route('sck.lager.artikel', '') }}/' + selectedItem.neue_artikelnummer" class="context-menu-item">
            <i class="fa-solid fa-circle-info text-cyan-400"></i>
            <span>Details anzeigen</span>
        </a>
        <button type="button" @click="openEditModal = true; document.getElementById('custom-context-menu').classList.add('hidden')" class="context-menu-item">
            <i class="fa-solid fa-pen text-purple-400"></i>
            <span>Artikel bearbeiten</span>
        </button>
        <button type="button" @click="openPrintModal = true; document.getElementById('custom-context-menu').classList.add('hidden'); setTimeout(() => renderPrintQR(), 100);" class="context-menu-item">
            <i class="fa-solid fa-print text-purple-400"></i>
            <span>Label drucken</span>
        </button>
        <button type="button" @click="openZoomModal = true; document.getElementById('custom-context-menu').classList.add('hidden'); renderGlobalZoomQR();" class="context-menu-item">
            <i class="fa-solid fa-qrcode text-emerald-400"></i>
            <span>QR-Code vergrößern</span>
        </button>
        <div class="context-menu-divider"></div>
        <form action="{{ route('sck.lager.update-stock') }}" method="POST" class="block">
            @csrf
            <input type="hidden" name="item_id" :value="selectedItem.id">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" name="action" value="add" class="context-menu-item">
                <i class="fa-solid fa-plus text-emerald-500"></i>
                <span>Bestand +1</span>
            </button>
            <button type="submit" name="action" value="remove" class="context-menu-item">
                <i class="fa-solid fa-minus text-red-500"></i>
                <span>Bestand -1</span>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Render table row QR codes using QRious
            document.querySelectorAll('.qr-canvas').forEach(canvas => {
                new QRious({
                    element: canvas,
                    value: canvas.dataset.url,
                    size: 40,
                    background: '#ffffff',
                    foreground: '#000000',
                    level: 'M'
                });
            });

            // Global Tooltip system
            let activeTooltip = null;
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'global-tooltip-item';
            document.body.appendChild(tooltipEl);

            document.addEventListener('mouseover', (e) => {
                const target = e.target.closest('.has-tooltip');
                if (!target) return;
                if (activeTooltip === target) return;
                
                const textContainer = target.querySelector('.tooltip-item');
                const text = textContainer ? textContainer.innerHTML : target.title;
                if (!text) return;

                tooltipEl.innerHTML = text;
                tooltipEl.classList.add('show');
                activeTooltip = target;
                
                positionTooltip(target);
            });

            document.addEventListener('mouseout', (e) => {
                if (!activeTooltip) return;
                const related = e.relatedTarget;
                if (!related || !activeTooltip.contains(related)) {
                    tooltipEl.classList.remove('show');
                    activeTooltip = null;
                }
            });

            function positionTooltip(target) {
                if (!activeTooltip) return;
                const rect = target.getBoundingClientRect();
                const tooltipRect = tooltipEl.getBoundingClientRect();
                
                let top = rect.top - tooltipRect.height - 8 + window.scrollY;
                let left = rect.left + (rect.width - tooltipRect.width) / 2 + window.scrollX;
                
                if (rect.top - tooltipRect.height - 8 < 0) {
                    top = rect.bottom + 8 + window.scrollY;
                }
                
                if (left < 10) left = 10;
                if (left + tooltipRect.width > window.innerWidth - 10) {
                    left = window.innerWidth - tooltipRect.width - 10;
                }
                
                tooltipEl.style.top = `${top}px`;
                tooltipEl.style.left = `${left}px`;
            }

            window.addEventListener('scroll', () => { if (activeTooltip) positionTooltip(activeTooltip); }, true);
            window.addEventListener('resize', () => { if (activeTooltip) positionTooltip(activeTooltip); });

            // Right-Click Context Menu Logic
            const contextMenu = document.getElementById('custom-context-menu');

            function positionContextMenu(clientX, clientY) {
                // Temporarily show off-screen to measure
                contextMenu.style.visibility = 'hidden';
                contextMenu.style.left = '0px';
                contextMenu.style.top  = '0px';
                contextMenu.classList.remove('hidden');

                const menuW = contextMenu.offsetWidth;
                const menuH = contextMenu.offsetHeight;

                contextMenu.classList.add('hidden');
                contextMenu.style.visibility = '';

                const viewW = window.innerWidth;
                const viewH = window.innerHeight;

                // Flip horizontally if overflows right
                let left = clientX + window.scrollX;
                if (clientX + menuW + 12 > viewW) {
                    left = clientX - menuW + window.scrollX;
                }
                // Clamp left
                left = Math.max(8 + window.scrollX, left);

                // Flip vertically if overflows bottom
                let top = clientY + window.scrollY;
                if (clientY + menuH + 12 > viewH) {
                    top = clientY - menuH + window.scrollY;
                }
                // Clamp top
                top = Math.max(8 + window.scrollY, top);

                contextMenu.style.left = `${left}px`;
                contextMenu.style.top  = `${top}px`;
                contextMenu.classList.remove('hidden');
            }

            document.querySelectorAll('.cursor-context-menu').forEach(row => {
                row.addEventListener('contextmenu', (e) => {
                    e.preventDefault();

                    const id = row.dataset.id;
                    const bezeichnung = row.dataset.bezeichnung;
                    const geraet = row.dataset.geraet;
                    const lieferant = row.dataset.lieferant;
                    const ek = row.dataset.ek;
                    const vk = row.dataset.vk;
                    const alteNr = row.dataset.alteNr;
                    const neueNr = row.dataset.neueNr;
                    const stueckzahl = parseInt(row.dataset.stueckzahl);
                    const kommentar = row.dataset.kommentar;

                    const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                    alpineData.selectedItem = {
                        id, bezeichnung, geraet, lieferant,
                        ek_ohne_st: ek, vk_ohne_st: vk,
                        alte_artikelnummer: alteNr, neue_artikelnummer: neueNr,
                        stueckzahl, kommentar
                    };

                    positionContextMenu(e.clientX, e.clientY);
                });
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#custom-context-menu')) {
                    contextMenu.classList.add('hidden');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    contextMenu.classList.add('hidden');
                }
            });


            // Client-Side Instant Filtering
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    const rows = document.querySelectorAll('.cursor-context-menu');
                    
                    rows.forEach(row => {
                        const bezeichnung = (row.dataset.bezeichnung || '').toLowerCase();
                        const geraet = (row.dataset.geraet || '').toLowerCase();
                        const lieferant = (row.dataset.lieferant || '').toLowerCase();
                        const alteNr = (row.dataset.alteNr || '').toLowerCase();
                        const neueNr = (row.dataset.neueNr || '').toLowerCase();
                        
                        const matches = bezeichnung.includes(query) ||
                                        geraet.includes(query) ||
                                        lieferant.includes(query) ||
                                        alteNr.includes(query) ||
                                        neueNr.includes(query);
                                        
                        if (matches) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Global helper functions
            window.renderGlobalZoomQR = function() {
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                const neueNr = alpineData.selectedItem.neue_artikelnummer;
                const targetUrl = `{{ route('sck.lager.artikel', '') }}/${neueNr}`;
                
                setTimeout(() => {
                    const canvas = document.getElementById('global-zoom-qr');
                    if (canvas) {
                        new QRious({
                            element: canvas,
                            value: targetUrl,
                            size: 180,
                            background: '#ffffff',
                            foreground: '#000000',
                            level: 'H'
                        });
                    }
                }, 50);
            };

            window.renderPrintQR = function() {
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                const neueNr = alpineData.selectedItem.neue_artikelnummer;
                if (!neueNr) return;
                const size = alpineData.printSize;
                const qrSize = size === 'small' ? 80 : 120;
                const targetUrl = `{{ route('sck.lager.artikel', '') }}/${neueNr}`;
                
                setTimeout(() => {
                    const canvas = document.getElementById('print-label-qr');
                    if (canvas) {
                        new QRious({
                            element: canvas,
                            value: targetUrl,
                            size: qrSize,
                            background: '#ffffff',
                            foreground: '#000000',
                            level: 'H'
                        });
                    }
                }, 50);
            };

            window.printLabel = function(neueNr, size) {
                // Gather Alpine state
                const alpineData = Alpine.$data(document.querySelector('[x-data]'));
                const item = alpineData.selectedItem;
                const fields = alpineData.printFields;

                // Label dimensions (matching CSS preview sizes)
                const labelW = size === 'small' ? 320 : 480;
                const labelH = size === 'small' ? 180 : 300;
                const qrSize = size === 'small' ? 80 : 120;
                const padding = size === 'small' ? 14 : 22;

                // Generate QR code as data URL via a temp canvas
                const tmpCanvas = document.createElement('canvas');
                const targetUrl = `{{ route('sck.lager.artikel', '') }}/${neueNr}`;
                new QRious({
                    element: tmpCanvas,
                    value: targetUrl,
                    size: qrSize,
                    background: '#ffffff',
                    foreground: '#000000',
                    level: 'H'
                });
                const qrDataUrl = tmpCanvas.toDataURL('image/png');

                // Build field rows HTML
                let fieldsHtml = '';
                if (fields.geraet && item.geraet)      fieldsHtml += `<div class="field-row"><b>Kat:</b> ${esc(item.geraet)}</div>`;
                if (fields.lieferant && item.lieferant) fieldsHtml += `<div class="field-row"><b>Lief:</b> ${esc(item.lieferant)}</div>`;
                if (fields.ek && item.ek_ohne_st)       fieldsHtml += `<div class="field-row"><b>EK:</b> ${esc(item.ek_ohne_st)} €</div>`;
                if (fields.vk && item.vk_ohne_st)       fieldsHtml += `<div class="field-row"><b>VK:</b> ${esc(item.vk_ohne_st)} €</div>`;
                if (fields.alte_nr && item.alte_artikelnummer) fieldsHtml += `<div class="field-row"><b>Alt:</b> ${esc(item.alte_artikelnummer)}</div>`;
                if (fields.neue_nr)                     fieldsHtml += `<div class="field-row"><b>Neu:</b> ${esc(item.neue_artikelnummer)}</div>`;
                let kommentarHtml = '';
                if (fields.kommentar && item.kommentar) {
                    kommentarHtml = `<div style="border-top:1px solid #ccc;padding-top:4px;margin-top:4px;font-size:8px;color:#555;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">${esc(item.kommentar)}</div>`;
                }

                const titleFontSize = size === 'small' ? '13px' : '18px';
                const fieldFontSize = size === 'small' ? '9px'  : '11px';

                const html = `<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Label: ${esc(item.bezeichnung)}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  @page {
    size: ${labelW}px ${labelH}px;
    margin: 0;
  }
  body {
    width: ${labelW}px;
    height: ${labelH}px;
    font-family: 'Figtree', Arial, sans-serif;
    background: white;
    color: black;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .label {
    width: ${labelW}px;
    height: ${labelH}px;
    padding: ${padding}px;
    display: flex;
    align-items: center;
    gap: 14px;
    overflow: hidden;
  }
  .qr-wrap {
    flex-shrink: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .qr-wrap img {
    display: block;
    width: ${qrSize}px;
    height: ${qrSize}px;
  }
  .info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    overflow: hidden;
    min-width: 0;
  }
  .title {
    font-size: ${titleFontSize};
    font-weight: 900;
    line-height: 1.2;
    color: #000;
    word-break: break-word;
  }
  .fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2px 8px;
    margin-top: 5px;
  }
  .field-row {
    font-size: ${fieldFontSize};
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .field-row b {
    font-weight: 700;
    color: #111;
  }
</style>
</head>
<body>
<div class="label">
  <div class="qr-wrap">
    <img src="${qrDataUrl}" alt="QR Code">
  </div>
  <div class="info">
    <div>
      <div class="title">${esc(item.bezeichnung)}</div>
      <div class="fields">${fieldsHtml}</div>
    </div>
    ${kommentarHtml}
  </div>
</div>
<script>window.onload = function() { window.print(); window.onafterprint = function() { window.close(); }; };<\/script>
</body>
</html>`;

                function esc(str) {
                    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                }

                const win = window.open('', '_blank', `width=${labelW + 40},height=${labelH + 120},menubar=no,toolbar=no,status=no`);
                if (win) {
                    win.document.write(html);
                    win.document.close();
                } else {
                    alert('Bitte erlauben Sie Pop-ups für diese Seite, um den Druckdialog zu öffnen.');
                }
            };
        });
    </script>
</div>
@endsection
