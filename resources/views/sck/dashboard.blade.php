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
                        <option value="kunden" {{ auth()->user()->sck_default_app === 'kunden' ? 'selected' : '' }}>Kundendatenbank</option>
                        <option value="routen" {{ auth()->user()->sck_default_app === 'routen' ? 'selected' : '' }}>Routenplanung</option>
                        <option value="wochenplanung" {{ auth()->user()->sck_default_app === 'wochenplanung' ? 'selected' : '' }}>Wochenplanung</option>
                        <option value="adressverwaltung" {{ auth()->user()->sck_default_app === 'adressverwaltung' ? 'selected' : '' }}>Adressverwaltung</option>
                        <option value="karte" {{ auth()->user()->sck_default_app === 'karte' ? 'selected' : '' }}>Karte</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Grid of sub-apps -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach([
            ['route'=>'sck.lager.index','icon'=>'fa-boxes-stacked','accent'=>'cyan','title'=>'Lagersystem','text'=>'Verwalte Lagerbestand, Barcodes, Ein- und Ausbuchungen sowie Bestandslisten.','status'=>'Bereit für QR-Scan'],
            ['route'=>'sck.kunden.index','icon'=>'fa-address-book','accent'=>'purple','title'=>'Kundendatenbank','text'=>'Kontakte, Einsatzhistorie, Reputation, Fotos, Kommentare und Margen.','status'=>'Kunden im Blick'],
            ['route'=>'sck.routen.index','icon'=>'fa-route','accent'=>'emerald','title'=>'Routenplanung','text'=>'Einzelne Touren planen, dokumentieren, abrechnen und exportieren.','status'=>'Touren planen'],
            ['route'=>'sck.wochenplanung.index','icon'=>'fa-calendar-week','accent'=>'orange','title'=>'Wochenplanung','text'=>'Stopps auf mehrere Touren verteilen und drei optimierte Varianten vergleichen.','status'=>'Wochen optimieren'],
            ['route'=>'sck.administration.addresses.index','icon'=>'fa-location-crosshairs','accent'=>'cyan','title'=>'Adressverwaltung','text'=>'Fehlende Breiten- und Längengrade für Kunden und Stopps gezielt berechnen.','status'=>'Koordinaten prüfen'],
            ['route'=>'sck.map.index','icon'=>'fa-map-location-dot','accent'=>'purple','title'=>'Karte','text'=>'Home, Kunden, eigene Punkte und RouteXL-Touren gemeinsam auf der TomTom-Karte anzeigen.','status'=>'Touren visualisieren'],
        ] as $app)
        <a href="{{ route($app['route']) }}" class="glass-panel glass-panel-hover p-6 rounded-2xl border border-gray-800 flex min-h-[260px] flex-col justify-between group transition-all duration-300 relative overflow-hidden">
            <div @class([
                'absolute top-0 right-0 w-24 h-24 bg-gradient-to-br to-transparent rounded-bl-full pointer-events-none group-hover:scale-110 transition-transform duration-300',
                'from-cyan-500/10' => $app['accent'] === 'cyan',
                'from-purple-500/10' => $app['accent'] === 'purple',
                'from-emerald-500/10' => $app['accent'] === 'emerald',
                'from-orange-500/10' => $app['accent'] === 'orange',
            ])></div>

            <div class="space-y-4">
                <div class="sck-dashboard-app-icon w-12 h-12 rounded-xl border flex items-center justify-center transition-all duration-300" data-accent="{{ $app['accent'] }}">
                    <i class="fa-solid {{ $app['icon'] }} text-2xl"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xl font-bold tracking-tight group-hover:text-cyan-400 transition-colors flex items-center space-x-2">
                        <span>{{ $app['title'] }}</span>
                        <i class="fa-solid fa-arrow-right text-xs opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300"></i>
                    </h3>
                    <p class="text-sm text-gray-400 leading-relaxed">{{ $app['text'] }}</p>
                </div>
            </div>

            <div class="border-t border-gray-800/80 mt-6 pt-4 flex items-center justify-between text-xs text-gray-500">
                <span class="flex items-center space-x-1">
                    <i class="fa-solid fa-circle text-[6px] text-emerald-500 animate-pulse"></i>
                    <span>Aktiviert</span>
                </span>
                <span>{{ $app['status'] }}</span>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
