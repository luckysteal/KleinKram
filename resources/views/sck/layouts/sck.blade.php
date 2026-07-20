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
        <div class="w-full max-w-[1700px] mx-auto flex items-center justify-between">
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

            <!-- Header Actions -->
            <div class="flex items-center space-x-3">
                <!-- User name with tooltip -->
                <div class="hidden sm:flex items-center space-x-2 bg-gray-900/60 px-3 py-1.5 rounded-full border border-gray-800 has-tooltip cursor-default">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 animate-pulse"></span>
                    <span class="text-sm font-semibold text-gray-300">{{ auth()->user()->name }}</span>
                    <div class="tooltip-item tooltip-left">Angemeldet als {{ auth()->user()->name }}. Typ: {{ auth()->user()->is_admin ? 'Admin' : (auth()->user()->role ?? 'Benutzer') }}</div>
                </div>

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

                <!-- Back to Main App Button -->
                <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center space-x-1.5 text-xs text-gray-400 hover:text-cyan-400 bg-gray-900/40 hover:bg-cyan-500/10 px-3 py-2 rounded-lg border border-gray-800 hover:border-cyan-500/30 transition-all duration-200 has-tooltip">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Haupt-Dashboard</span>
                    <div class="tooltip-item">Verlässt das SCK-Portal und öffnet das reguläre Benutzer-Dashboard.</div>
                </a>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-red-400 bg-gray-900/40 hover:bg-red-500/10 border border-gray-800 hover:border-red-500/30 transition-all duration-200 has-tooltip">
                        <i class="fa-solid fa-power-off"></i>
                        <div class="tooltip-item tooltip-left">Meldet dich sicher von der gesamten Anwendung ab.</div>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow w-full max-w-[1700px] mx-auto px-4 py-6 sm:px-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 border-t border-gray-950 text-center text-xs text-gray-600 bg-gray-950/20">
        <div class="w-full max-w-[1700px] mx-auto px-4 flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
            <p>&copy; 2026 Service Center Klein. Alle Rechte vorbehalten.</p>
            <div class="flex space-x-4">
                <a href="{{ route('sck.dashboard', ['no_redirect' => 1]) }}" class="hover:text-cyan-400 transition-colors">Hauptmenü</a>
                <a href="{{ route('sck.lager.index') }}" class="hover:text-cyan-400 transition-colors">Lagersystem</a>
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
