@extends('sck.layouts.sck')

@section('content')
<div class="space-y-8">
    <!-- Header area -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h2 class="text-3xl font-black tracking-tight">SCK Anwendungs-Dashboard</h2>
            <p class="text-gray-400 mt-1">Wähle eine der untenstehenden Applikationen des Service Center Klein aus.</p>
        </div>
        
        <!-- Default app configuration form -->
        <div class="glass-panel p-4 rounded-xl border border-gray-800 max-w-sm w-full">
            <form action="{{ route('sck.set-default-app') }}" method="POST" class="space-y-3">
                @csrf
                <div class="flex flex-col space-y-1">
                    <label for="default_app" class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex items-center space-x-1.5 has-tooltip cursor-default">
                        <span>Standard-App beim Start:</span>
                        <i class="fa-solid fa-circle-question text-cyan-400 text-xxs"></i>
                        <div class="tooltip-item tooltip-left">Bestimmt, welche Applikation direkt geladen wird, wenn du das SCK-Portal öffnest oder dich neu anmeldest.</div>
                    </label>
                    <select name="default_app" id="default_app" onchange="this.form.submit()" class="sck-input text-sm rounded-lg px-3 py-2 w-full mt-1">
                        <option value="">-- Keine (Hauptmenü anzeigen) --</option>
                        <option value="lager" {{ auth()->user()->sck_default_app === 'lager' ? 'selected' : '' }}>Lagersystem</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Grid of sub-apps -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Lagersystem Card -->
        <a href="{{ route('sck.lager.index') }}" class="glass-panel glass-panel-hover p-6 rounded-2xl border border-gray-800 flex flex-col justify-between group transition-all duration-300 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-cyan-500/10 to-transparent rounded-bl-full pointer-events-none group-hover:scale-110 transition-transform"></div>
            
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 group-hover:bg-cyan-500 group-hover:text-white transition-all duration-300">
                    <i class="fa-solid fa-boxes-stacked text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xl font-bold tracking-tight group-hover:text-cyan-400 transition-colors flex items-center space-x-2">
                        <span>Lagersystem</span>
                        <i class="fa-solid fa-arrow-right text-xs opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"></i>
                    </h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Verwalte den aktuellen Lagerbestand, generiere neue Barcodes, führe Ein- und Ausbuchungen durch und exportiere Bestandslisten.
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-800/80 mt-6 pt-4 flex items-center justify-between text-xs text-gray-500">
                <span class="flex items-center space-x-1">
                    <i class="fa-solid fa-circle text-[6px] text-emerald-500 animate-pulse"></i>
                    <span>Aktiviert</span>
                </span>
                <span>Bereit für QR-Scan</span>
            </div>
        </a>

        <!-- Placeholder for future apps -->
        <div class="glass-panel p-6 rounded-2xl border border-dashed border-gray-800 flex flex-col justify-between items-center text-center opacity-40 cursor-not-allowed select-none">
            <div class="w-12 h-12 rounded-xl bg-gray-800 flex items-center justify-center text-gray-500">
                <i class="fa-solid fa-plus text-2xl"></i>
            </div>
            <div class="space-y-1 mt-4">
                <h3 class="text-lg font-bold tracking-tight text-gray-400">Weitere Sub-Apps</h3>
                <p class="text-xs text-gray-500 px-4">
                    Das System ist modular aufgebaut. Zukünftige Applikationen können hier einfach hinzugefügt werden.
                </p>
            </div>
            <div class="mt-6 text-[10px] text-gray-600 uppercase font-semibold">
                In Planung
            </div>
        </div>
    </div>
</div>
@endsection
