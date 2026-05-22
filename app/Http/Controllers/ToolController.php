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
            'winnersTally' => Session::get('winners_tally', []),
            'lastGameRoute' => Session::get('last_game_route'),
            'shuffleActive' => Session::get('shuffle_active', false)
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

    public function schlossgrabenJump()
    {
        return view('tools.schlossgraben-jump', $this->getGameData());
    }

    private function getGameData()
    {
        $players = Session::get('players_list', []);
        $lmsActive = Session::get('lms_active', false);
        $eliminated = Session::get('eliminated_players', []);
        $overallWinner = Session::get('lms_overall_winner');

        // Record last played game
        $currentRoute = request()->route()->getName();
        if (in_array($currentRoute, [
            'tools.spinning-crown',
            'tools.hi-low',
            'tools.ticking-bomb',
            'tools.russian-roulette',
            'tools.snake-pit',
            'tools.schlossgraben-jump'
        ])) {
            Session::put('last_game_route', $currentRoute);
        }

        if ($lmsActive) {
            $players = array_values(array_diff($players, $eliminated));
            
            // If only one player left and they are the overall winner, reset the round for the *next* spin
            // but for the current view, we might need to know if the round just ended.
        }

        return [
            'names' => $players,
            'winnersTally' => Session::get('winners_tally', []),
            'lmsActive' => $lmsActive,
            'shuffleActive' => Session::get('shuffle_active', false),
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

        // Handle shuffle after every round
        if (Session::get('shuffle_active', false)) {
            $players = Session::get('players_list', []);
            if (count($players) > 1) {
                $maxAttempts = 50;
                $shuffled = false;
                
                for ($i = 0; $i < $maxAttempts; $i++) {
                    shuffle($players);
                    
                    // 1. Ensure the person who just act (winner/loser) is not at index 0 
                    //    (assuming new rounds/turns often start or reset towards index 0)
                    if ($players[0] === $winner) continue;
                    
                    // 2. Ensure no two identical names are adjacent (in case of duplicate entries)
                    $hasAdjacentDuplicates = false;
                    for ($j = 0; $j < count($players) - 1; $j++) {
                        if ($players[$j] === $players[$j+1]) {
                            $hasAdjacentDuplicates = true;
                            break;
                        }
                    }
                    if ($hasAdjacentDuplicates) continue;
                    
                    $shuffled = true;
                    break;
                }
                
                if ($shuffled) {
                    Session::put('players_list', $players);
                }
            }
        }

        return response()->json([
            'success' => true,
            'names' => Session::get('players_list', []),
            'shuffle_active' => Session::get('shuffle_active', false)
        ]);
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

    public function toggleShuffle(Request $request)
    {
        $active = (bool)$request->input('active', false);
        Session::put('shuffle_active', $active);
        return response()->json(['success' => true, 'shuffle_active' => $active]);
    }
}
