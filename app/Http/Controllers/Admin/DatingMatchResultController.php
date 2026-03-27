<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatingMatchResult;
use Illuminate\Http\Request;

class DatingMatchResultController extends Controller
{
    /**
     * Display a listing of the results.
     */
    public function index()
    {
        $results = DatingMatchResult::latest()->paginate(20);
        return view('admin.dating-match-results.index', compact('results'));
    }

    /**
     * Display the specified result.
     */
    public function show(DatingMatchResult $datingMatchResult)
    {
        return view('admin.dating-match-results.show', ['result' => $datingMatchResult]);
    }

    /**
     * Show the form for editing the specified result.
     */
    public function edit(DatingMatchResult $datingMatchResult)
    {
        return view('admin.dating-match-results.edit', ['result' => $datingMatchResult]);
    }

    /**
     * Update the specified result in storage.
     */
    public function update(Request $request, DatingMatchResult $datingMatchResult)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|string',
            'seeking' => 'nullable|string',
            'franchise' => 'nullable|string',
            'mapped_character' => 'required|string',
        ]);

        $datingMatchResult->update($validated);

        return redirect()->route('admin.dating-match-results.index')
            ->with('success', 'Result updated successfully.');
    }

    /**
     * Remove the specified result from storage.
     */
    public function destroy(DatingMatchResult $datingMatchResult)
    {
        $datingMatchResult->delete();

        return redirect()->route('admin.dating-match-results.index')
            ->with('success', 'Result deleted successfully.');
    }
}
