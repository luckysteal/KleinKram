<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = [
            [
                'name' => __('Spinning Crown'),
                'description' => __('Spin the crown to see who pays for the next round!'),
                'route' => 'tools.spinning-crown',
                'icon' => 'fas fa-crown',
                'color' => 'amber'
            ],
            [
                'name' => __('Hi-Low'),
                'description' => __('Guess if the next is higher/lower. Random (0-27).'),
                'route' => 'tools.hi-low',
                'icon' => 'fas fa-chart-line',
                'color' => 'indigo'
            ],
            [
                'name' => __('Ticking Bomb'),
                'description' => __('Quick! Pass the bomb before it explodes (10-30s)!'),
                'route' => 'tools.ticking-bomb',
                'icon' => 'fas fa-bomb',
                'color' => 'rose'
            ],
            [
                'name' => __('Russian Roulette'),
                'description' => __('Pull the trigger. Who gets the single chamber?'),
                'route' => 'tools.russian-roulette',
                'icon' => 'fas fa-crosshairs',
                'color' => 'gray'
            ],
            [
                'name' => __('Snake Pit'),
                'description' => __('Reveal tiles but avoid the hidden snakes!'),
                'route' => 'tools.snake-pit',
                'icon' => 'fas fa-biohazard',
                'color' => 'emerald'
            ],
            [
                'name' => __('Player Management'),
                'description' => __('Manage the list of players for games.'),
                'route' => 'tools.player-selection',
                'icon' => 'fas fa-users-cog',
                'color' => 'blue'
            ],
            // Add other games here
        ];
        return view('games.index', compact('games'));
    }
}
