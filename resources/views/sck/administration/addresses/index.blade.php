@extends('sck.layouts.sck')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs uppercase tracking-[.25em] text-cyan-400 font-bold">Administration</p>
        <h1 class="text-3xl font-black">Adressverwaltung</h1>
        <p class="text-gray-400 mt-1">Berechne Breiten- und Längengrade für Adressen, bei denen mindestens ein Wert fehlt.</p>
    </div>

    <div class="glass-panel border border-gray-800 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-gray-800 flex items-center justify-between gap-3">
            <h2 class="font-black">Adressen ohne vollständige Koordinaten</h2>
            <span class="rounded-full bg-cyan-500/10 border border-cyan-500/30 px-3 py-1 text-sm font-bold text-cyan-300">{{ $missingCoordinates->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-400"><th class="p-4">Typ</th><th class="p-4">Name</th><th class="p-4">Adresse</th><th class="p-4 text-right">Aktion</th></tr></thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($missingCoordinates as $entry)
                        <tr>
                            <td class="p-4"><span class="rounded-full bg-gray-800 px-2.5 py-1 text-xs font-bold">{{ $entry['type_label'] }}</span></td>
                            <td class="p-4 font-bold">{{ $entry['title'] }}</td>
                            <td class="p-4 text-gray-400">{{ $entry['address'] ?: 'Keine Adresse hinterlegt' }}</td>
                            <td class="p-4 text-right">
                                <form method="POST" action="{{ route('sck.administration.addresses.calculate-coordinates', [$entry['type'], $entry['id']]) }}">
                                    @csrf
                                    <button class="rounded-xl bg-cyan-600 px-3 py-2 font-bold text-white hover:bg-cyan-500 transition-colors"><i class="fa-solid fa-location-crosshairs mr-1"></i>Koordinaten berechnen</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-10 text-center text-gray-400"><i class="fa-solid fa-circle-check text-emerald-400 mr-2"></i>Alle Kunden- und Stoppadressen haben vollständige Koordinaten.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
