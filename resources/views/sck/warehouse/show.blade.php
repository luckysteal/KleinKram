@extends('sck.layouts.sck')

@section('content')
<div class="max-w-md mx-auto space-y-6" x-data="{ openEditModal: false }">

    <!-- Header / Navigation back -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <a href="{{ route('sck.lager.index') }}" class="text-gray-400 hover:text-cyan-400 transition-colors">
                <i class="fa-solid fa-circle-chevron-left text-xl"></i>
            </a>
            <span class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Artikel-Detailansicht</span>
        </div>
        <button @click="openEditModal = true" class="text-xs bg-gray-900 hover:bg-cyan-500/10 hover:text-cyan-400 border border-gray-800 hover:border-cyan-500/30 px-3 py-1.5 rounded-lg flex items-center space-x-1.5 transition-all">
            <i class="fa-solid fa-pen"></i>
            <span>Bearbeiten</span>
        </button>
    </div>

    <!-- Product Card -->
    <div class="glass-panel rounded-2xl border border-gray-800 overflow-hidden shadow-2xl">
        <!-- Accent Header -->
        <div class="bg-gradient-to-r from-cyan-600/30 to-purple-600/30 px-6 py-5 border-b border-gray-800">
            <div class="flex items-start justify-between space-x-4">
                <div class="flex-grow">
                    <span class="text-xxs font-bold text-cyan-400 uppercase tracking-widest bg-cyan-950/80 px-2.5 py-1 rounded-full border border-cyan-800/40">
                        {{ $item->geraet }}
                    </span>
                    <h2 class="text-2xl font-black text-white mt-2">{{ $item->bezeichnung }}</h2>
                </div>
                <div class="flex flex-col items-center space-y-2 flex-shrink-0">
                    <div class="bg-gray-900 p-2 rounded-xl border border-gray-800 text-center font-mono w-24">
                        <span class="text-[9px] text-gray-500 block uppercase font-bold tracking-wider">System-Nr.</span>
                        <span class="text-cyan-400 font-black text-sm">{{ $item->neue_artikelnummer }}</span>
                    </div>
                    <!-- QR Code display -->
                    <div class="bg-white p-1 rounded-lg shadow-md inline-block">
                        <canvas id="detail-qr-canvas" data-url="{{ route('sck.lager.artikel', $item->neue_artikelnummer) }}" width="64" height="64"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Specifications list -->
        <div class="p-6 space-y-4 text-sm">
            
            <!-- Current Stock display -->
            <div class="bg-gray-950/60 p-4 rounded-xl border border-gray-850 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center space-x-1.5 has-tooltip cursor-default">
                        <span>Aktueller Lagerbestand</span>
                        <i class="fa-solid fa-circle-question text-cyan-400 text-xxs"></i>
                        <div class="tooltip-item tooltip-left">Zeigt die im Lager erfassten Einheiten. Ändert sich nach Ein- oder Ausbuchungen.</div>
                    </span>
                    <div class="text-2xl font-black font-mono mt-1" :class="{{ $item->stueckzahl }} < 5 ? 'text-amber-400' : 'text-gray-100'">
                        {{ $item->stueckzahl }} Stk.
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-cyan-500/10 flex items-center justify-center text-cyan-400">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>

            <!-- Details List -->
            <div class="space-y-3 pt-2">
                <div class="flex justify-between py-2 border-b border-gray-850/40">
                    <span class="text-gray-500 font-medium">Lieferant</span>
                    <span class="text-gray-200 font-semibold">{{ $item->lieferant }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-850/40">
                    <span class="text-gray-500 font-medium flex items-center space-x-1.5 has-tooltip cursor-default">
                        <span>Einkaufspreis (netto)</span>
                        <i class="fa-solid fa-circle-question text-cyan-400 text-xxs"></i>
                        <div class="tooltip-item">Der vereinbarte Einkaufspreis ohne Mehrwertsteuer.</div>
                    </span>
                    <span class="text-gray-200 font-mono">{{ number_format($item->ek_ohne_st, 2, ',', '.') }} €</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-850/40">
                    <span class="text-gray-500 font-medium flex items-center space-x-1.5 has-tooltip cursor-default">
                        <span>Verkaufspreis (netto)</span>
                        <i class="fa-solid fa-circle-question text-cyan-400 text-xxs"></i>
                        <div class="tooltip-item">Der kalkulierte Verkaufspreis ohne Mehrwertsteuer.</div>
                    </span>
                    <span class="text-gray-200 font-mono">{{ number_format($item->vk_ohne_st, 2, ',', '.') }} €</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-850/40">
                    <span class="text-gray-500 font-medium flex items-center space-x-1.5 has-tooltip cursor-default">
                        <span>Alte Artikelnummer</span>
                        <div class="tooltip-item">Referenznummer aus dem Alt-System (falls vorhanden).</div>
                    </span>
                    <span class="text-gray-400 font-mono">{{ $item->alte_artikelnummer ?? 'Keine' }}</span>
                </div>
                @if($item->kommentar)
                    <div class="py-2">
                        <span class="text-gray-500 font-medium block mb-1">Lager-Hinweis / Kommentar:</span>
                        <p class="text-gray-400 bg-gray-900/60 p-3 rounded-lg border border-gray-850 text-xs leading-relaxed">
                            {{ $item->kommentar }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Quick Adjustments Form -->
            <div class="border-t border-gray-800 pt-6 mt-4 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center space-x-1.5 has-tooltip cursor-default">
                    <span>Schnellbuchung (Mobil optimiert)</span>
                    <i class="fa-solid fa-circle-question text-cyan-400 text-xxs"></i>
                    <div class="tooltip-item tooltip-left">Passe den Lagerbestand schnell an. Trage die Anzahl ein und wähle die entsprechende Aktion.</div>
                </h4>
                
                <form action="{{ route('sck.lager.update-stock') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    
                    <div class="flex flex-col space-y-1.5">
                        <label for="quantity" class="text-xs text-gray-500">Menge / Anzahl</label>
                        <div class="flex items-center space-x-3">
                            <input type="number" name="quantity" id="quantity" value="1" min="1" class="sck-input text-lg rounded-xl px-4 py-2 w-full font-bold font-mono text-center">
                        </div>
                    </div>

                    <!-- Quick buttons -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="submit" name="action" value="remove" class="bg-red-950/40 hover:bg-red-600 text-red-400 hover:text-white border border-red-500/20 py-3.5 px-4 rounded-xl font-black text-sm flex items-center justify-center space-x-2 transition-all duration-200 active:scale-95">
                            <i class="fa-solid fa-minus"></i>
                            <span>Entnehmen</span>
                        </button>
                        <button type="submit" name="action" value="add" class="bg-emerald-950/40 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-500/20 py-3.5 px-4 rounded-xl font-black text-sm flex items-center justify-center space-x-2 transition-all duration-200 active:scale-95">
                            <i class="fa-solid fa-plus"></i>
                            <span>Auffüllen</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('detail-qr-canvas');
            if (canvas) {
                new QRious({
                    element: canvas,
                    value: canvas.dataset.url,
                    size: 64,
                    background: '#ffffff',
                    foreground: '#000000',
                    level: 'H'
                });
            }
        });
    </script>
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
                <input type="hidden" name="id" value="{{ $item->id }}">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="edit_bezeichnung" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bezeichnung *</label>
                        <input type="text" name="bezeichnung" id="edit_bezeichnung" value="{{ $item->bezeichnung }}" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_geraet" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gerätetyp / Kategorie *</label>
                        <input type="text" name="geraet" id="edit_geraet" value="{{ $item->geraet }}" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_lieferant" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lieferant *</label>
                        <input type="text" name="lieferant" id="edit_lieferant" value="{{ $item->lieferant }}" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_ek_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">EK Netto (in €) *</label>
                        <input type="number" name="ek_ohne_st" id="edit_ek_ohne_st" step="0.01" min="0" value="{{ $item->ek_ohne_st }}" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_vk_ohne_st" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">VK Netto (in €) *</label>
                        <input type="number" name="vk_ohne_st" id="edit_vk_ohne_st" step="0.01" min="0" value="{{ $item->vk_ohne_st }}" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_alte_artikelnummer" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Alte Artikelnummer</label>
                        <input type="text" name="alte_artikelnummer" id="edit_alte_artikelnummer" value="{{ $item->alte_artikelnummer }}" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1">
                        <label for="edit_stueckzahl" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Bestand *</label>
                        <input type="number" name="stueckzahl" id="edit_stueckzahl" value="{{ $item->stueckzahl }}" min="0" required class="sck-input text-sm rounded-lg px-3 py-2 mt-1">
                    </div>

                    <div class="flex flex-col space-y-1 sm:col-span-2">
                        <label for="edit_kommentar" class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kommentar</label>
                        <textarea name="kommentar" id="edit_kommentar" rows="3" class="sck-input text-sm rounded-lg px-3 py-2 mt-1">{{ $item->kommentar }}</textarea>
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
</div>
@endsection
