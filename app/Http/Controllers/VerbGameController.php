<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerbGameController extends Controller
{
    public function index()
    {
        // Load player names from session (standard for this app)
        $players = session('players_list', []);
        
        // Load game state from session if exists
        $gameState = session('verb_game_state', []);

        return view('games.verb-game', [
            'players' => $players,
            'gameState' => $gameState
        ]);
    }

    public function save(Request $request)
    {
        // Save the complete state in the session
        session(['verb_game_state' => $request->all()]);

        return response()->json(['success' => true]);
    }
}
