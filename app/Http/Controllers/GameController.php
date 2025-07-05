<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = [
            [
                'name' => 'Spinning Crown',
                'description' => 'Spin the crown to see who pays for the next round!',
                'route' => 'tools.spinning-crown',
                'icon' => 'crown', // Placeholder for icon
            ],
            // Add other games here
        ];
        return view('games.index', compact('games'));
    }
}
