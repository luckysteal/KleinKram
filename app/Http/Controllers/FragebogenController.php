<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DatingMatchResult;
use App\Models\DatingQuestion;

class FragebogenController extends Controller
{
    /**
     * Display the dating profile generator wizard.
     */
    public function index()
    {
        $characterQuestions = DatingQuestion::where('type', 'character')->get();
        $partnerQuestions = DatingQuestion::where('type', 'partner')->get();
        $showUniverse = \App\Models\Page::first()?->show_dating_universe ?? true;
        
        // Get all unique universes from questions that have one
        $availableUniverses = DatingQuestion::whereNotNull('universe')->distinct()->pluck('universe')->toArray();

        return view('fragebogen.index', compact('characterQuestions', 'partnerQuestions', 'showUniverse', 'availableUniverses'));
    }

    /**
     * Store a new dating matcher result.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string',
            'seeking' => 'nullable|string',
            'franchise' => 'nullable|string',
            'mapped_character' => 'required|string',
            'traits' => 'required|array',
            'partner_traits' => 'required|array',
            'full_results' => 'required|array',
        ]);

        $result = DatingMatchResult::create($validated);

        return response()->json([
            'id' => $result->id,
            'share_url' => route('fragebogen.show', $result->id)
        ]);
    }

    /**
     * Display a shared dating matcher result.
     */
    public function show($id)
    {
        $result = DatingMatchResult::findOrFail($id);
        
        return view('fragebogen.index', [
            'sharedResult' => $result
        ]);
    }
}
