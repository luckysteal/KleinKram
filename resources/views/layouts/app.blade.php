<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="min-h-screen flex flex-col sm:h-screen lg:h-screen transition-colors duration-300">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow transition-colors duration-300">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="flex-grow flex flex-col relative w-full overflow-y-auto sm:overflow-hidden lg:overflow-hidden transition-colors duration-300">
                <div class="flex-grow flex flex-col w-full h-full">
                    {{ $slot }}
                </div>
            </main>

            @php
                $layoutWinnersTally = $winnersTally ?? session('winners_tally', []);
                // For the scoreboard, we want the full list of players to show their alive/eliminated status
                $layoutFullPlayersList = session('players_list', []);
                $layoutLmsActive = $lmsActive ?? session('lms_active', false);
                $layoutEliminatedPlayers = $eliminated ?? session('eliminated_players', []);
            @endphp

            <x-game-scoreboard 
                :winners-tally="$layoutWinnersTally" 
                :players-list="$layoutFullPlayersList"
                :lms-active="$layoutLmsActive"
                :eliminated-players="$layoutEliminatedPlayers"
            />
        </div>
    </body>
</html>
