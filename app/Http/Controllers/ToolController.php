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
        return view('tools.spinning-crown', $this->getGameData());
    }

    public function playerSelection()
    {
        return view('tools.player-selection', [
            'names' => Session::get('players_list', []),
            'winnersTally' => Session::get('winners_tally', [])
        ]);
    }

    public function hiLow()
    {
        return view('tools.hi-low', $this->getGameData());
    }

    public function tickingBomb()
    {
        return view('tools.ticking-bomb', $this->getGameData());
    }

    public function russianRoulette()
    {
        return view('tools.russian-roulette', $this->getGameData());
    }

    public function snakePit()
    {
        return view('tools.snake-pit', $this->getGameData());
    }

    private function getGameData()
    {
        $players = Session::get('players_list', []);
        $lmsActive = Session::get('lms_active', false);
        $eliminated = Session::get('eliminated_players', []);
        $overallWinner = Session::get('lms_overall_winner');

        if ($lmsActive) {
            $players = array_values(array_diff($players, $eliminated));
            
            // If only one player left and they are the overall winner, reset the round for the *next* spin
            // but for the current view, we might need to know if the round just ended.
        }

        return [
            'names' => $players,
            'winnersTally' => Session::get('winners_tally', []),
            'lmsActive' => $lmsActive,
            'eliminated' => $eliminated,
            'overallWinner' => $overallWinner
        ];
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

        $winner = $request->input('winner');
        Session::put('last_winner', $winner);

        $winnersTally = Session::get('winners_tally', []);
        if (!isset($winnersTally[$winner])) {
            $winnersTally[$winner] = 0;
        }
        $winnersTally[$winner]++;
        Session::put('winners_tally', $winnersTally);

        // Handle Last Man Standing elimination
        if (Session::get('lms_active', false)) {
            $eliminated = Session::get('eliminated_players', []);
            if (!in_array($winner, $eliminated)) {
                $eliminated[] = $winner;
                Session::put('eliminated_players', $eliminated);
            }

            // Check if only one player is left
            $totalPlayers = Session::get('players_list', []);
            if (count($totalPlayers) - count($eliminated) <= 1) {
                // Determine the last man standing
                $remaining = array_diff($totalPlayers, $eliminated);
                $overallWinner = reset($remaining);
                if ($overallWinner) {
                    Session::put('lms_overall_winner', $overallWinner);
                }
                // Do not reset here, let the frontend handle the "Round Over" display
            }
        }

        return response()->json(['success' => true]);
    }

    public function toggleLms(Request $request)
    {
        $active = $request->input('active', false);
        Session::put('lms_active', $active);
        
        // Reset elimination state when toggling
        Session::forget('eliminated_players');
        Session::forget('lms_overall_winner');
        
        return response()->json(['success' => true, 'lms_active' => $active]);
    }

    public function resetLms()
    {
        Session::forget('eliminated_players');
        Session::forget('lms_overall_winner');
        return response()->json(['success' => true]);
    }
}
