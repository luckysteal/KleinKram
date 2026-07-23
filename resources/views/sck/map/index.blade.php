@extends('sck.layouts.sck')

@section('content')
<div
    class="sck-map-page space-y-4"
    x-data="sckMap(@js([
        'apiKey' => config('services.tomtom.key'),
        'configured' => $tomTomConfigured,
        'initialMode' => $initialMode,
        'initialWeek' => $initialWeek,
        'initialTourId' => $initialTourId,
        'initialLayers' => $initialLayers,
        'initialLegendOpen' => $initialLegendOpen,
        'dataUrl' => route('sck.map.data'),
        'layersUrl' => route('sck.map.layers.update'),
        'tourSearchUrl' => route('sck.map.tour-search'),
        'addressSearchUrl' => route('sck.address-search'),
        'reverseUrl' => route('sck.map.reverse-geocode'),
        'pointsUrl' => route('sck.map-points.store'),
    ]))"
    @keydown.escape.window="pointModalOpen = false; tourResults = []"
>
    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.25em] text-purple-400">TomTom Kartenansicht</p>
            <h1 class="text-3xl font-black">Karte & Touren</h1>
            <p class="mt-1 text-gray-400">RouteXL-Touren, Kunden und eigene Einsatzpunkte auf einer Karte.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" class="action-chip" @click="reload()"><i class="fa-solid fa-rotate-right mr-2"></i>Neu laden</button>
            <button type="button" class="action-chip" @click="openAddressPoint()"><i class="fa-solid fa-magnifying-glass-location mr-2"></i>Adresse speichern</button>
            <button type="button" class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-black text-white shadow-lg shadow-purple-600/20 hover:bg-purple-500" @click="startPlacement()">
                <i class="fa-solid fa-location-dot mr-2"></i>Punkt setzen
            </button>
        </div>
    </div>

    <section class="sck-map-filter glass-panel relative z-30 grid gap-3 rounded-2xl border border-gray-800 p-4 xl:grid-cols-[auto_auto_1fr] xl:items-center">
        <div class="flex flex-wrap gap-2">
            <button type="button" class="sck-map-mode" :class="mode === 'next' && 'is-active'" @click="setMode('next')">Nächste Tour</button>
            <button type="button" class="sck-map-mode" :class="mode === 'week' && 'is-active'" @click="setMode('week')">Woche</button>
            <button type="button" class="sck-map-mode" :class="mode === 'tour' && 'is-active'" @click="setMode('tour')">Tour suchen</button>
        </div>

        <form x-show="mode === 'week'" x-cloak @submit.prevent="loadData()">
            <x-sck.week-picker name="map_week" :value="$initialWeek" accent="purple" />
        </form>

        <div x-show="mode === 'tour'" x-cloak class="relative min-w-0 max-w-xl" @click.outside="tourResults = []">
            <label class="sr-only" for="map-tour-search">Tour suchen</label>
            <input id="map-tour-search" x-model="tourQuery" @input.debounce.350ms="searchTours()" class="sck-input w-full rounded-xl" placeholder="Tournummer oder Titel suchen …" autocomplete="off">
            <div x-show="tourResults.length" x-cloak class="sck-map-search-results absolute left-0 right-0 top-full z-50 mt-1 max-h-72 overflow-auto rounded-xl border border-gray-700 bg-gray-900 shadow-2xl">
                <template x-for="tour in tourResults" :key="tour.id">
                    <button type="button" class="block w-full border-b border-gray-800 px-4 py-3 text-left last:border-0 hover:bg-purple-500/10" @click="chooseTour(tour)">
                        <strong class="block text-sm" x-text="tour.title"></strong>
                        <span class="text-xs text-gray-400" x-text="`${tour.number} · ${tour.date || 'ohne Datum'} · ${statusLabel(tour.status)}`"></span>
                    </button>
                </template>
            </div>
        </div>
    </section>

    @unless($tomTomConfigured)
        <div class="sck-map-alert sck-map-alert--warning rounded-2xl border border-amber-500/40 bg-amber-500/10 p-4 text-amber-200">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i>TomTom ist nicht konfiguriert. Bitte <code>TOMTOM_API_KEY</code> setzen, um die Karte zu laden.
        </div>
    @endunless
    <div x-show="error" x-cloak class="sck-map-alert sck-map-alert--error rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-red-200" x-text="error"></div>

    <div class="sck-map-shell glass-panel relative overflow-hidden rounded-[2rem] border border-gray-800 shadow-2xl" :class="placing && 'is-placing'">
        <div id="sck-map" class="absolute inset-0" x-ref="map"></div>

        <div x-show="loading" class="sck-map-loading absolute inset-0 z-40 grid place-items-center bg-gray-950/60 backdrop-blur-sm">
            <div class="sck-map-loading-card rounded-2xl border border-gray-700 bg-gray-900/90 px-5 py-4 font-bold"><i class="fa-solid fa-spinner fa-spin mr-2 text-purple-400"></i>Kartendaten werden geladen …</div>
        </div>

        <div x-show="placing" x-cloak class="absolute left-1/2 top-4 z-30 -translate-x-1/2 rounded-full bg-purple-600 px-5 py-2 text-sm font-black text-white shadow-xl">
            Auf die gewünschte Position klicken <button type="button" class="ml-3 opacity-80 hover:opacity-100" @click="placing=false">Abbrechen</button>
        </div>

        <aside class="sck-map-legend absolute left-4 top-4 z-20 rounded-2xl border border-white/10 bg-gray-950/85 p-3 shadow-2xl backdrop-blur-md" :class="legendOpen ? 'is-open' : 'is-closed'">
            <div class="flex items-center justify-between gap-3">
                <div x-show="legendOpen" x-transition class="flex items-center gap-2 overflow-hidden whitespace-nowrap">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-purple-500/15 text-purple-300"><i class="fa-solid fa-layer-group"></i></span>
                    <div><strong class="block text-sm">Legende</strong><small class="text-gray-400">Ebenen ein-/ausblenden</small></div>
                </div>
                <button type="button" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white/10 hover:bg-purple-600" @click="legendOpen = !legendOpen" :aria-label="legendOpen ? 'Legende schließen' : 'Legende öffnen'">
                    <i class="fa-solid fa-chevron-right transition-transform" :class="legendOpen && 'rotate-180'"></i>
                </button>
            </div>

            <div class="mt-3 space-y-2">
                <label class="sck-map-layer"><input type="checkbox" x-model="layers.home"><span class="sck-map-swatch bg-amber-500"><i class="fa-solid fa-house"></i></span><span x-show="legendOpen"><strong>Home</strong><small>Persönlicher Startpunkt</small></span></label>
                <label class="sck-map-layer"><input type="checkbox" x-model="layers.customers"><span class="sck-map-swatch bg-cyan-600"><i class="fa-solid fa-address-book"></i></span><span x-show="legendOpen"><strong>Kunden</strong><small>Gespeicherte Kundenadressen</small></span></label>
                <label class="sck-map-layer"><input type="checkbox" x-model="layers.points"><span class="sck-map-swatch bg-pink-600"><i class="fa-solid fa-location-dot"></i></span><span x-show="legendOpen"><strong>Eigene Punkte</strong><small>Gemeinsame SCK-Orte</small></span></label>
                <label class="sck-map-layer"><input type="checkbox" x-model="layers.tours"><span class="sck-map-swatch bg-purple-600"><i class="fa-solid fa-route"></i></span><span x-show="legendOpen"><strong>Touren</strong><small>Route und nummerierte Stopps</small></span></label>
            </div>

            <div x-show="legendOpen && layers.tours && data.tours.length" x-cloak class="mt-3 border-t border-white/10 pt-3">
                <template x-for="(tour, index) in data.tours" :key="tour.id">
                    <button type="button" class="mb-1 flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs hover:bg-white/10" @click="focusTour(tour.id)">
                        <span class="h-1.5 w-7 shrink-0 rounded-full" :style="`background:${tourColor(index)}`"></span>
                        <span class="min-w-0"><strong class="block truncate" x-text="tour.title"></strong><small class="text-gray-400" x-text="`${tour.date || 'ohne Datum'} · ${tour.stops.length} Stopps`"></small></span>
                    </button>
                </template>
            </div>
        </aside>

        <div x-show="!loading && !data.tours.length && (mode === 'next' || (mode === 'tour' && selectedTourId))" x-cloak class="sck-map-empty absolute bottom-5 left-1/2 z-20 -translate-x-1/2 rounded-xl border border-gray-700 bg-gray-950/90 px-4 py-3 text-sm text-gray-300 shadow-xl">
            Keine passende Tour gefunden.
        </div>
    </div>

    <div x-show="pointModalOpen" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-4">
        <form class="sck-map-modal glass-panel max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-gray-700 bg-gray-900 p-6" @submit.prevent="savePoint()" @click.outside="pointModalOpen=false">
            <div class="mb-5 flex items-center justify-between">
                <div><p class="text-xs font-bold uppercase tracking-widest text-pink-400">Eigener Kartenpunkt</p><h2 class="text-2xl font-black" x-text="draft.id ? 'Punkt bearbeiten' : 'Punkt speichern'"></h2></div>
                <button type="button" @click="pointModalOpen=false" aria-label="Dialog schließen"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="relative mb-4" @click.outside="addressResults=[]">
                <label class="text-sm font-bold">Adresse suchen
                    <input x-model="addressQuery" @input.debounce.400ms="searchAddresses(false)" class="sck-input mt-1 w-full rounded-xl" placeholder="Gespeicherte Adresse oder TomTom-Suche" autocomplete="off">
                </label>
                <div x-show="addressResults.length || addressCanSearchOnline" x-cloak class="sck-map-search-results absolute left-0 right-0 z-20 mt-1 max-h-64 overflow-auto rounded-xl border border-gray-700 bg-gray-950 shadow-2xl">
                    <template x-for="item in addressResults" :key="`${item.source}-${item.id}-${item.label}`"><button type="button" class="block w-full px-3 py-2 text-left hover:bg-pink-500/10" @click="chooseAddress(item)"><small class="font-bold text-pink-400" x-text="sourceLabel(item.source)"></small><span class="block text-sm" x-text="item.label"></span></button></template>
                    <button x-show="addressCanSearchOnline && !addressSearchedOnline" type="button" class="w-full border-t border-gray-700 px-3 py-3 text-left text-sm font-bold text-cyan-400" @click="searchAddresses(true)"><i class="fa-solid fa-globe mr-2"></i>Online mit TomTom suchen</button>
                </div>
                <p class="sck-map-help mt-1 text-xs text-gray-500">Adresse auswählen oder die Koordinaten direkt eintragen.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="text-sm font-bold md:col-span-2">Name *<input required x-model="draft.name" maxlength="255" class="sck-input mt-1 w-full rounded-xl"></label>
                <label class="text-sm font-bold md:col-span-2">Notiz<textarea x-model="draft.note" maxlength="5000" rows="3" class="sck-input mt-1 w-full rounded-xl"></textarea></label>
                <label class="text-sm font-bold md:col-span-2">Adresse<input x-model="draft.formatted_address" maxlength="500" class="sck-input mt-1 w-full rounded-xl"></label>
                <label class="text-sm font-bold">Breitengrad *<input required type="number" step="any" min="-90" max="90" x-model.number="draft.latitude" class="sck-input mt-1 w-full rounded-xl"></label>
                <label class="text-sm font-bold">Längengrad *<input required type="number" step="any" min="-180" max="180" x-model.number="draft.longitude" class="sck-input mt-1 w-full rounded-xl"></label>
            </div>
            <p x-show="pointError" x-text="pointError" class="sck-map-validation mt-3 text-sm text-red-300"></p>
            <div class="mt-5 flex justify-between gap-3">
                <button x-show="draft.id" type="button" class="sck-map-delete rounded-xl border border-red-500/30 px-4 py-2 font-bold text-red-300 hover:bg-red-500/10" @click="deletePoint()">Löschen</button>
                <span x-show="!draft.id"></span>
                <div class="flex gap-2"><button type="button" class="action-chip" @click="pointModalOpen=false">Abbrechen</button><button class="rounded-xl bg-pink-600 px-5 py-2 font-black text-white hover:bg-pink-500" :disabled="pointSaving"><i x-show="pointSaving" class="fa-solid fa-spinner fa-spin mr-2"></i>Speichern</button></div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.25.0/maps/maps.css">
@endpush

@push('scripts')
<script src="https://api.tomtom.com/maps-sdk-for-web/cdn/6.x/6.25.0/maps/maps-web.min.js"></script>
@endpush
