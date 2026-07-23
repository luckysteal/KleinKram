<!DOCTYPE html>
<html lang="de" id="sck-html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Service Center Klein (SCK)</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Compiled Assets (Strictly isolated sck bundle) -->
    @vite(['resources/css/sck.css', 'resources/js/sck.js'])
    @stack('styles')

    <!-- Apply saved theme immediately to prevent flash -->
    <script>
        (function() {
            var theme = localStorage.getItem('sck-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body class="sck-theme min-h-screen flex flex-col antialiased">

    <!-- Top Navigation Header -->
    <header class="glass-panel border-b border-gray-800 sticky top-0 z-40 px-4 py-3 sm:px-8">
        @php
            $primaryNavigation = [
                ['route' => 'sck.dashboard', 'parameters' => ['no_redirect' => 1], 'active' => 'sck.dashboard', 'label' => 'Übersicht', 'icon' => 'fa-grid-2'],
                ['route' => 'sck.lager.index', 'parameters' => [], 'active' => 'sck.lager.*', 'label' => 'Lager', 'icon' => 'fa-boxes-stacked'],
                ['route' => 'sck.kunden.index', 'parameters' => [], 'active' => 'sck.kunden.*', 'label' => 'Kunden', 'icon' => 'fa-address-book'],
                ['route' => 'sck.routen.index', 'parameters' => [], 'active' => 'sck.routen.*', 'label' => 'Routen', 'icon' => 'fa-route'],
                ['route' => 'sck.wochenplanung.index', 'parameters' => [], 'active' => 'sck.wochenplanung.*', 'label' => 'Woche', 'icon' => 'fa-calendar-week'],
                ['route' => 'sck.map.index', 'parameters' => [], 'active' => 'sck.map.*', 'label' => 'Karte', 'icon' => 'fa-map-location-dot'],
            ];
        @endphp
        <div class="w-full max-w-[1700px] mx-auto flex flex-wrap items-center gap-x-4 gap-y-3">
            <div class="flex items-center space-x-3">
                <a href="{{ route('sck.dashboard', ['no_redirect' => 1]) }}" class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-purple-600 flex items-center justify-center shadow-lg shadow-cyan-500/20 transition-transform active:scale-95">
                    <i class="fa-solid fa-screwdriver-wrench text-white text-lg"></i>
                </a>
                <div>
                    <a href="{{ route('sck.dashboard', ['no_redirect' => 1]) }}" class="text-lg font-black tracking-wider uppercase bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent hover:brightness-110 transition-all">
                        Service Center Klein
                    </a>
                    <p class="text-xs text-gray-500 font-medium">SCK Portal v1.0</p>
                </div>
            </div>

            <!-- Primary SCK navigation -->
            <nav class="order-3 flex w-full items-center gap-1 overflow-x-auto pb-0.5 lg:order-2 lg:w-auto lg:flex-1 lg:justify-center lg:overflow-visible" aria-label="SCK Hauptnavigation">
                @foreach($primaryNavigation as $item)
                    @php($isActive = request()->routeIs($item['active']))
                    <a href="{{ route($item['route'], $item['parameters']) }}"
                       @class([
                           'inline-flex shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-sm font-bold transition-all duration-200',
                           'border-cyan-500/40 bg-cyan-500/10 text-cyan-300 shadow-sm shadow-cyan-500/10' => $isActive,
                           'border-transparent text-gray-400 hover:border-gray-700 hover:bg-gray-900/60 hover:text-gray-100' => ! $isActive,
                       ])
                       @if($isActive) aria-current="page" @endif>
                        <i class="fa-solid {{ $item['icon'] }} text-xs"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <!-- Header Actions -->
            <div class="ml-auto flex items-center space-x-2 lg:order-3">
                <!-- Dark / Light Mode Toggle -->
                <button id="theme-toggle"
                        class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-cyan-400 bg-gray-900/40 hover:bg-cyan-500/10 border border-gray-800 hover:border-cyan-500/30 transition-all duration-200 has-tooltip"
                        aria-label="Theme wechseln"
                        onclick="toggleSckTheme()">
                    <!-- Moon icon (shown in dark mode) -->
                    <i id="theme-icon-dark"  class="fa-solid fa-moon text-sm"></i>
                    <!-- Sun icon (shown in light mode) -->
                    <i id="theme-icon-light" class="fa-solid fa-sun  text-sm hidden"></i>
                    <div class="tooltip-item tooltip-left">Hell-/Dunkel-Modus umschalten</div>
                </button>

                <!-- Profile menu -->
                <div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape.window="open = false">
                    <button type="button"
                            @click="open = !open"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-800 bg-gray-900/40 text-gray-400 transition-all duration-200 hover:border-cyan-500/30 hover:bg-cyan-500/10 hover:text-cyan-400 has-tooltip"
                            :aria-expanded="open.toString()"
                            aria-controls="sck-profile-menu"
                            aria-label="Profilmenü öffnen">
                        <i class="fa-solid fa-user text-sm"></i>
                        <div class="tooltip-item tooltip-left">Profilmenü</div>
                    </button>

                    <div id="sck-profile-menu"
                         x-show="open"
                         x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-gray-700 bg-gray-900 shadow-2xl">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-3 text-sm font-semibold text-gray-200 transition-colors hover:bg-cyan-500/10 hover:text-cyan-300">
                            <i class="fa-solid fa-user-gear w-4"></i>
                            <span>Profil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-800">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-3 text-left text-sm font-semibold text-gray-300 transition-colors hover:bg-red-500/10 hover:text-red-400">
                                <i class="fa-solid fa-arrow-right-from-bracket w-4"></i>
                                <span>Abmelden</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow w-full max-w-[1700px] mx-auto px-4 py-6 sm:px-8">
        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-red-500/40 bg-red-500/10 p-4 text-red-300"><strong>Bitte Eingaben prüfen:</strong><ul class="list-disc ml-5 mt-1 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 border-t border-gray-950 text-center text-xs text-gray-600 bg-gray-950/20">
        <div class="w-full max-w-[1700px] mx-auto px-4 flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
            <p>&copy; 2026 Service Center Klein. Alle Rechte vorbehalten.</p>
            <div class="flex space-x-4">
                <a href="{{ route('sck.dashboard', ['no_redirect' => 1]) }}" class="hover:text-cyan-400 transition-colors">Hauptmenü</a>
                <a href="{{ route('sck.lager.index') }}" class="hover:text-cyan-400 transition-colors">Lagersystem</a>
                <a href="{{ route('sck.kunden.index') }}" class="hover:text-cyan-400 transition-colors">Kunden</a>
                <a href="{{ route('sck.routen.index') }}" class="hover:text-cyan-400 transition-colors">Routen</a>
                <a href="{{ route('sck.wochenplanung.index') }}" class="hover:text-cyan-400 transition-colors">Woche</a>
                <a href="{{ route('sck.map.index') }}" class="hover:text-cyan-400 transition-colors">Karte</a>
            </div>
        </div>
    </footer>

    <!-- Global Flash Notification Toast (using Alpine.js) -->
    <div x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            init() {
                @if(session('success'))
                    this.trigger('{{ session('success') }}', 'success');
                @endif
                @if(session('error'))
                    this.trigger('{{ session('error') }}', 'error');
                @endif
            },
            trigger(msg, type) {
                this.message = msg;
                this.type = type;
                this.show = true;
                setTimeout(() => this.show = false, 5000);
            }
         }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-5 right-5 z-50 max-w-sm w-full bg-gray-900/95 border rounded-xl shadow-2xl p-4 flex items-start space-x-3 glass-panel"
         :class="type === 'success' ? 'border-cyan-500/40 text-cyan-200' : 'border-red-500/40 text-red-200'"
         x-cloak>
        <div class="flex-shrink-0">
            <template x-if="type === 'success'">
                <i class="fa-solid fa-circle-check text-cyan-400 text-lg"></i>
            </template>
            <template x-if="type === 'error'">
                <i class="fa-solid fa-triangle-exclamation text-red-400 text-lg"></i>
            </template>
        </div>
        <div class="flex-1 text-sm font-medium" x-text="message"></div>
        <button @click="show = false" class="flex-shrink-0 text-gray-500 hover:text-gray-300 transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <!-- Global Print Section -->
    <div id="print-section" class="hidden"></div>

    <!-- Theme Toggle Script -->
    <script>
        function syncThemeIcons(theme) {
            var iconDark  = document.getElementById('theme-icon-dark');
            var iconLight = document.getElementById('theme-icon-light');
            if (!iconDark || !iconLight) return;
            if (theme === 'light') {
                iconDark.classList.add('hidden');
                iconLight.classList.remove('hidden');
            } else {
                iconDark.classList.remove('hidden');
                iconLight.classList.add('hidden');
            }
        }

        function toggleSckTheme() {
            var html  = document.documentElement;
            var current = html.getAttribute('data-theme') || 'dark';
            var next    = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('sck-theme', next);
            syncThemeIcons(next);
            window.dispatchEvent(new CustomEvent('sck-theme-changed', { detail: { theme: next } }));
        }

        // Sync icons on DOMContentLoaded (theme is already applied inline above)
        document.addEventListener('DOMContentLoaded', function() {
            var theme = localStorage.getItem('sck-theme') || 'dark';
            syncThemeIcons(theme);
        });
    </script>

    @stack('scripts')
</body>
</html>
