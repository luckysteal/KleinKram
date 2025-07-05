<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ToolController extends Controller
{
    public function wordCount(Request $request)
    {
        $wordCount = null;
        if ($request->isMethod('post')) {
            $text = $request->input('text');
            $wordCount = str_word_count($text);
        }

        return view('tools.word-count', ['wordCount' => $wordCount]);
    }

    public function spinningCrown()
    {
        return view('tools.spinning-crown', ['names' => ['Christian', 'Elisa']]);
    }

    public function spinningCrownTest()
    {
        return view('tools.spinning-crown-test', ['names' => []]);
    }

    public function updatePlayers(Request $request)
    {
        $request->validate([
            'names' => 'required|array',
            'names.*' => 'nullable|string|max:255',
        ]);

        $names = array_values(array_filter($request->input('names')));
        Session::put('players_list', $names);

        return response()->json(['success' => true, 'names' => $names]);
    }

    public function saveWinner(Request $request)
    {
        $request->validate([
            'winner' => 'required|string|max:255',
        ]);

        Session::put('last_winner', $request->input('winner'));

        return response()->json(['success' => true]);
    }
}
