@extends('sck.layouts.sck')
@section('content')
<div class="space-y-6" x-data="{ rating: {{ (int) ($customer->reputation_rating ?? 0) }}, noteOpen: false }">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div><a href="{{ route('sck.kunden.index') }}" class="text-sm text-cyan-400">← Kundendatenbank</a><h1 class="text-3xl font-black mt-2">{{ $customer->name }}</h1><p class="text-gray-400">{{ $customer->full_address }}</p></div>
        <section class="glass-panel border border-gray-800 rounded-2xl p-4" aria-label="Kundenreputation">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-3">
                <div><p class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-1">Reputation</p><form x-ref="reputationForm" method="POST" action="{{ route('sck.kunden.reputation.update', $customer) }}">@csrf @method('PUT')<input type="hidden" name="reputation_rating" :value="rating"><input type="hidden" name="reputation_note" value="{{ $customer->reputation_note }}"><div class="flex gap-1" role="radiogroup" aria-label="Reputation bewerten">@for($star = 1; $star <= 5; $star++)<button type="button" @click="rating = {{ $star }}; $nextTick(() => $refs.reputationForm.submit())" class="text-2xl leading-none transition-colors" :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-gray-600 hover:text-amber-300'" :aria-checked="rating === {{ $star }}" role="radio" aria-label="{{ $star }} von 5 Sternen">★</button>@endfor</div></form></div>
                <button type="button" @click="noteOpen = true" class="rounded-xl border border-gray-700 px-3 py-2 text-sm font-bold text-gray-200 hover:border-cyan-500/60 hover:text-cyan-300"><i class="fa-solid fa-note-sticky mr-1.5"></i>{{ $customer->reputation_note ? 'Bewertungsnotiz' : 'Notiz hinzufügen' }}</button>
            </div>
            @if($customer->reputation_reviewed_at)<p class="mt-2 text-xs text-gray-500">Zuletzt bewertet am {{ $customer->reputation_reviewed_at->format('d.m.Y') }}</p>@endif
        </section>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach([['Einsätze', $stats['visits']], ['Umsatz netto', number_format($stats['sales'], 2, ',', '.').' €'], ['Ø Marge', number_format($stats['average_margin'], 2, ',', '.').' €'], ['Letzter Einsatz', $stats['last_visit'] ? \Carbon\Carbon::parse($stats['last_visit'])->format('d.m.Y') : '–'], ['Entfernung von Home', $stats['distance_from_home'] !== null ? number_format($stats['distance_from_home'], 1, ',', '.').' km' : '–']] as [$label, $value])
        <div class="glass-panel border border-gray-800 rounded-2xl p-4"><div class="text-xs text-gray-400 uppercase font-bold">{{ $label }}</div><div class="text-xl font-black mt-1">{{ $value }}</div></div>
        @endforeach
    </div>

    <div class="grid xl:grid-cols-3 gap-6">
        <form method="POST" action="{{ route('sck.kunden.update', $customer) }}" class="glass-panel border border-gray-800 rounded-2xl p-5 xl:col-span-2">@csrf @method('PUT')<h2 class="font-black text-xl mb-5">Stammdaten</h2>@include('sck.customers.partials.form', compact('customer'))<button class="mt-5 bg-cyan-600 text-white rounded-xl px-5 py-2.5 font-bold">Änderungen speichern</button></form>
        <section class="glass-panel border border-gray-800 rounded-2xl p-5"><h2 class="font-black text-lg">Notizen</h2><p class="text-sm text-gray-300 whitespace-pre-line mt-3">{{ $customer->notes ?: 'Keine Notizen.' }}</p></section>
    </div>

    <section class="glass-panel border border-gray-800 rounded-2xl p-5"><h2 class="font-black text-xl mb-4">Kundenfotos</h2><div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3">@forelse($customer->photos as $photo)<a href="{{ route('sck.media.show',$photo) }}" target="_blank" class="group"><img src="{{ route('sck.media.show',$photo) }}" class="w-full aspect-square object-cover rounded-xl border border-gray-700"><p class="text-xs mt-1 truncate">{{ $photo->caption ?: $photo->original_name }}</p></a>@empty<p class="text-sm text-gray-400 col-span-full">Fotos werden an einem Tour-Stopp aufgenommen und hier automatisch gesammelt.</p>@endforelse</div></section>

    <section class="glass-panel border border-gray-800 rounded-2xl overflow-hidden"><h2 class="font-black text-xl p-5">Arbeitshistorie</h2><div class="divide-y divide-gray-800">@forelse($customer->tourStops as $stop)<a href="{{ route('sck.routen.show',$stop->tour) }}" class="block p-4 hover:bg-cyan-500/5"><div class="flex justify-between"><strong>{{ $stop->title }}</strong><span class="text-xs text-gray-400">{{ optional($stop->tour?->tour_date)->format('d.m.Y') }}</span></div><div class="text-xs text-gray-400 mt-1">{{ $stop->items->count() }} Artikel · {{ number_format($stop->allocated_travel_fee,2,',','.') }} € Anfahrt</div></a>@empty<p class="p-5 text-gray-400">Noch keine Einsätze.</p>@endforelse</div></section>

    <details class="glass-panel border border-gray-800 rounded-2xl overflow-hidden group"><summary class="cursor-pointer list-none p-5 flex items-center justify-between font-black text-xl">Änderungshistorie <i class="fa-solid fa-chevron-down text-sm transition-transform group-open:rotate-180"></i></summary><div class="divide-y divide-gray-800 max-h-96 overflow-y-auto">@forelse($customer->changes as $change)<div class="p-4"><div class="flex justify-between text-xs text-gray-400"><span>{{ $change->user?->name ?? 'System' }} · {{ $change->event }}</span><time>{{ $change->created_at->format('d.m.Y H:i') }}</time></div><p class="text-xs mt-2 font-mono break-all">{{ json_encode($change->after, JSON_UNESCAPED_UNICODE) }}</p></div>@empty<p class="p-5 text-gray-400">Keine Änderungen vorhanden.</p>@endforelse</div></details>

    <div x-show="noteOpen" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" @keydown.escape.window="noteOpen = false">
        <form method="POST" action="{{ route('sck.kunden.reputation.update', $customer) }}" class="glass-panel border border-gray-700 rounded-2xl w-full max-w-xl p-6" @click.outside="noteOpen = false">@csrf @method('PUT')<input type="hidden" name="reputation_rating" :value="rating"><div class="flex items-center justify-between gap-4"><div><h2 class="text-xl font-black">Bewertungsnotiz</h2><p class="text-sm text-gray-400">Hinweise zur Kundenreputation dokumentieren.</p></div><button type="button" @click="noteOpen = false" class="text-gray-400 hover:text-white" aria-label="Schließen"><i class="fa-solid fa-xmark text-xl"></i></button></div><textarea name="reputation_note" rows="8" class="sck-input mt-5 rounded-xl w-full" placeholder="Notiz zur Bewertung…">{{ $customer->reputation_note }}</textarea><div class="mt-5 flex justify-end gap-3"><button type="button" @click="noteOpen = false" class="px-4 py-2.5 text-sm font-bold text-gray-300">Abbrechen</button><button class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-cyan-500">Notiz speichern</button></div></form>
    </div>
</div>
@endsection
