@extends('sck.layouts.sck')

@section('content')
<div class="space-y-6" x-data="{ open: {{ $errors->default->any() ? 'true' : 'false' }}, importOpen: {{ $errors->datevImport->any() ? 'true' : 'false' }} }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div><p class="text-xs uppercase tracking-[.25em] text-cyan-400 font-bold">SCK Sub-App</p><h1 class="text-3xl font-black">Kundendatenbank</h1><p class="text-gray-400">Kontakte, Arbeitshistorie, Dokumentation und Margen.</p></div>
        <div class="flex flex-wrap gap-2">
            <button @click="importOpen=true" class="px-5 py-3 rounded-xl border border-emerald-500/40 bg-emerald-500/10 text-emerald-300 font-bold hover:bg-emerald-500/20"><i class="fa-solid fa-file-import mr-2"></i>DATEV importieren</button>
            <button @click="open=true" class="btn-neon-cyan px-5 py-3 rounded-xl font-bold"><i class="fa-solid fa-user-plus mr-2"></i>Kunde anlegen</button>
        </div>
    </div>
    <form class="glass-panel border border-gray-800 rounded-2xl p-4 flex gap-3">
        <input name="search" value="{{ request('search') }}" class="sck-input rounded-xl flex-1" placeholder="Name, Ort, PLZ oder DATEV-Konto durchsuchen">
        <button class="px-5 rounded-xl bg-cyan-600 text-white font-bold">Suchen</button>
    </form>
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($customers as $customer)
        <a href="{{ route('sck.kunden.show', $customer) }}" class="glass-panel glass-panel-hover border border-gray-800 rounded-2xl p-5 group">
            <div class="flex justify-between gap-3"><div><h2 class="font-black text-lg group-hover:text-cyan-400">{{ $customer->name }}</h2><p class="text-sm text-gray-400 mt-1">{{ $customer->full_address ?: 'Keine Adresse' }}</p></div><span class="text-amber-400 whitespace-nowrap">{{ str_repeat('★', $customer->reputation_rating ?? 0) }}{{ str_repeat('☆', 5-($customer->reputation_rating ?? 0)) }}</span></div>
            <div class="mt-5 pt-4 border-t border-gray-800 flex justify-between text-xs text-gray-400"><span>{{ $customer->completed_visits_count }} Einsätze @if($customer->datev_account) · DATEV {{ $customer->datev_account }} @endif</span><span class="uppercase font-bold {{ $customer->status === 'active' ? 'text-emerald-400' : 'text-gray-500' }}">{{ $customer->status }}</span></div>
        </a>
        @empty <div class="glass-panel border border-dashed border-gray-800 rounded-2xl p-10 text-center text-gray-400 sm:col-span-2 xl:col-span-3">Noch keine Kunden vorhanden.</div> @endforelse
    </div>
    {{ $customers->links() }}

    <div x-show="importOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" @keydown.escape.window="importOpen=false">
        <form method="POST" enctype="multipart/form-data" action="{{ route('sck.kunden.import-datev') }}" class="glass-panel bg-gray-900 border border-gray-700 rounded-3xl p-6 w-full max-w-xl" @click.outside="importOpen=false">
            @csrf
            <div class="flex justify-between items-center mb-5"><div><h2 class="text-2xl font-black">Kunden aus DATEV</h2><p class="text-sm text-gray-400 mt-1">Standardexport „Debitoren/Kreditoren“ importieren</p></div><button type="button" @click="importOpen=false"><i class="fa-solid fa-xmark"></i></button></div>
            @if($errors->datevImport->any())<div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-300">{{ $errors->datevImport->first() }}</div>@endif
            <label class="block text-sm font-bold">DATEV CSV-Datei<input type="file" name="datev_file" required accept=".csv,.txt,text/csv,text/plain" class="sck-input mt-2 rounded-xl w-full file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-white"></label>
            <div class="mt-4 rounded-xl border border-gray-800 bg-black/20 p-3 text-xs text-gray-400 space-y-1"><p>Unterstützt EXTF- und DTVF-Dateien sowie UTF-8, Windows-1252 und UTF-16.</p><p>Kreditoren werden übersprungen. Bereits importierte Debitoren werden über ihr DATEV-Konto aktualisiert.</p></div>
            <button class="mt-6 w-full bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl py-3 font-black"><i class="fa-solid fa-file-import mr-2"></i>Import starten</button>
        </form>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70" @keydown.escape.window="open=false">
        <form method="POST" action="{{ route('sck.kunden.store') }}" class="glass-panel bg-gray-900 border border-gray-700 rounded-3xl p-6 w-full max-w-3xl max-h-[92vh] overflow-y-auto" @click.outside="open=false">
            @csrf
            <div class="flex justify-between items-center mb-6"><h2 class="text-2xl font-black">Neuer Kunde</h2><button type="button" @click="open=false"><i class="fa-solid fa-xmark"></i></button></div>
            @include('sck.customers.partials.form', ['customer' => null])
            <button class="mt-6 w-full bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl py-3 font-black">Kunde speichern</button>
        </form>
    </div>
</div>
@endsection
