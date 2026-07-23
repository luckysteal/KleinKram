@extends('sck.layouts.sck')
@section('content')
<div class="space-y-6" x-data="{ open:false }">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4"><div><p class="text-xs uppercase tracking-[.25em] text-purple-400 font-bold">Routenplanung</p><h1 class="text-3xl font-black">Gespeicherte Stopps</h1><p class="text-gray-400">Wiederverwendbare Einsatzorte mit Standardwerten.</p></div><button @click="open=true" class="bg-purple-600 text-white px-5 py-3 rounded-xl font-black"><i class="fa-solid fa-location-dot mr-2"></i>Stopp anlegen</button></div>
    <div class="glass-panel border border-gray-800 rounded-2xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left"><th class="p-4">Name</th><th class="p-4">Adresse</th><th class="p-4">Kunde</th><th class="p-4">Dauer</th><th class="p-4">Fenster</th><th class="p-4"></th></tr></thead><tbody class="divide-y divide-gray-800">@forelse($stops as $stop)<tr><td class="p-4 font-bold">{{ $stop->title }}</td><td class="p-4 text-gray-400">{{ $stop->full_address }}</td><td class="p-4">{{ $stop->customer?->name ?? '–' }}</td><td class="p-4">{{ $stop->service_minutes }} Min.</td><td class="p-4">{{ $stop->window_start ? substr($stop->window_start,0,5).'–'.substr($stop->window_end,0,5) : 'frei' }}</td><td class="p-4"><form method="POST" action="{{ route('sck.stopps.destroy',$stop) }}" onsubmit="return confirm('Vorlage archivieren?')">@csrf @method('DELETE')<button class="text-red-400"><i class="fa-solid fa-trash"></i></button></form></td></tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-400">Noch keine Stopp-Vorlagen.</td></tr>@endforelse</tbody></table></div></div>
    {{ $stops->links() }}
    {{-- The form is shared with the tour planner so both entry points create identical stop templates. --}}
    <x-sck.stop-creation-modal :customers="$customers" :items="$items" open-model="open" />
</div>
@endsection
