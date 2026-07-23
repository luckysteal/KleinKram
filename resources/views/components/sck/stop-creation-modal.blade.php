@props(['customers', 'items', 'openModel' => 'stopModalOpen'])

<div x-show="{{ $openModel }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" @keydown.escape.window="{{ $openModel }}=false">
    <form method="POST" action="{{ route('sck.stopps.store') }}" x-data="sckStopCreation(@js($customers->map(fn ($customer) => $customer->only(['id', 'street', 'house_number', 'postal_code', 'city', 'country_code']))->values()))" class="glass-panel bg-gray-900 border border-gray-700 rounded-3xl p-6 w-full max-w-4xl max-h-[92vh] overflow-y-auto" @click.outside="{{ $openModel }}=false">
        @csrf
        <div class="flex justify-between mb-5"><h2 class="text-2xl font-black">Stopp-Vorlage</h2><button type="button" @click="{{ $openModel }}=false" aria-label="Stopp-Erstellung schließen">✕</button></div>
        <div class="grid md:grid-cols-2 gap-4">
            <label class="text-sm font-bold md:col-span-2">Titel *<input required name="title" class="sck-input rounded-xl w-full mt-1"></label>
            <div class="md:col-span-2 relative" x-data="sckAddressSearch()" @click.outside="open=false">
                <label class="text-sm font-bold">Adresse oder gespeicherten Datensatz suchen<input x-model="query" @input="changed" @focus="results.length&&(open=true)" class="sck-input rounded-xl w-full mt-1" placeholder="Lokal suchen oder online nachschlagen" autocomplete="off"></label>
                <div x-show="open" x-cloak class="address-results absolute z-30 left-0 right-0 mt-1 max-h-64 overflow-y-auto rounded-xl border border-gray-700 bg-gray-900 shadow-2xl">
                    <template x-for="(item,index) in results" :key="item.source+'-'+item.id+'-'+index"><button type="button" @click="choose(item)" class="block w-full text-left px-3 py-2 hover:bg-purple-500/10"><small class="text-purple-400 font-bold" x-text="item.group"></small><span class="block text-sm" x-text="item.label"></span></button></template>
                    <p x-show="loading" class="p-3 text-sm text-gray-400">Suche…</p>
                    <button x-show="canSearchOnline && !loading && !searchedOnline" type="button" @click="searchOnline" class="w-full border-t border-gray-700 px-3 py-3 text-left text-sm font-bold text-cyan-400 hover:bg-cyan-500/10"><i class="fa-solid fa-globe mr-2"></i>Online nach „<span x-text="query"></span>“ suchen</button>
                    <p x-show="searchedOnline && !results.some(item => item.source === 'tomtom')" class="p-3 text-sm text-gray-400">Online keine weiteren Treffer gefunden.</p>
                    <p x-show="!loading&&!results.length&&!canSearchOnline" class="p-3 text-sm text-gray-400">Keine Treffer gefunden.</p>
                </div>
            </div>
            <label class="text-sm font-bold">Kunde<select name="customer_id" @change="preloadCustomerAddress" class="sck-input rounded-xl w-full mt-1"><option value="">Ohne Kunde</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select></label>
            <label class="text-sm font-bold">Priorität<select name="priority" class="sck-input rounded-xl w-full mt-1">@for($i=1;$i<=5;$i++)<option value="{{ $i }}" @selected($i===3)>{{ $i }}</option>@endfor</select></label>
            <label class="text-sm font-bold">Straße<input name="street" class="sck-input rounded-xl w-full mt-1"></label><label class="text-sm font-bold">Hausnummer<input name="house_number" class="sck-input rounded-xl w-full mt-1"></label>
            <label class="text-sm font-bold">PLZ<input name="postal_code" class="sck-input rounded-xl w-full mt-1"></label><label class="text-sm font-bold">Ort<input name="city" class="sck-input rounded-xl w-full mt-1"></label>
            <input type="hidden" name="country_code" value="DE"><p class="md:col-span-2 rounded-xl border border-cyan-500/30 bg-cyan-500/5 px-3 py-2 text-sm text-cyan-200"><i class="fa-solid fa-location-crosshairs mr-2"></i>Koordinaten werden beim Speichern automatisch über TomTom ermittelt.</p>
            <label class="text-sm font-bold">Dauer (Min.)<input required type="number" min="0" name="service_minutes" value="30" class="sck-input rounded-xl w-full mt-1"></label><label class="text-sm font-bold">Wiederholung<input name="recurrence" placeholder="z. B. monatlich" class="sck-input rounded-xl w-full mt-1"></label>
            <label class="text-sm font-bold">Frühestens<input type="time" name="window_start" class="sck-input rounded-xl w-full mt-1"></label><label class="text-sm font-bold">Spätestens<input type="time" name="window_end" class="sck-input rounded-xl w-full mt-1"></label>
            <label class="text-sm font-bold">Kontakt<input name="contact_name" class="sck-input rounded-xl w-full mt-1"></label><label class="text-sm font-bold">Kontakt-Telefon<input name="contact_phone" class="sck-input rounded-xl w-full mt-1"></label>
            <label class="text-sm font-bold md:col-span-2">Vorgeschlagene Artikel<select name="item_ids[]" multiple class="sck-input rounded-xl w-full mt-1 min-h-32">@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->bezeichnung }} ({{ $item->neue_artikelnummer }})</option>@endforeach</select></label>
            <label class="text-sm font-bold">Zugang<textarea name="access_notes" class="sck-input rounded-xl w-full mt-1"></textarea></label><label class="text-sm font-bold">Parken<textarea name="parking_notes" class="sck-input rounded-xl w-full mt-1"></textarea></label>
            <label class="text-sm font-bold md:col-span-2">Notizen<textarea name="notes" rows="3" class="sck-input rounded-xl w-full mt-1"></textarea></label><label class="flex gap-2 items-center"><input type="checkbox" name="active" value="1" checked> Aktiv</label>
        </div>
        <button class="mt-5 w-full bg-purple-600 text-white py-3 rounded-xl font-black">Vorlage speichern</button>
    </form>
</div>
