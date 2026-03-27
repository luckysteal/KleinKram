<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatingQuestion;
use Illuminate\Http\Request;

class DatingQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = DatingQuestion::latest()->paginate(20);
        return view('admin.dating-questions.index', compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.dating-questions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255',
            'type' => 'required|in:character,partner',
            'universe' => 'nullable|string|max:255',
            'options' => 'required|array|min:1',
            'options.*.emoji' => 'required|string',
            'options.*.label' => 'required|string',
            'options.*.traits' => 'required|array',
        ]);

        DatingQuestion::create($validated);

        return redirect()->route('admin.dating-questions.index')
            ->with('success', 'Question created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DatingQuestion $datingQuestion)
    {
        return view('admin.dating-questions.edit', ['question' => $datingQuestion]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DatingQuestion $datingQuestion)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255',
            'type' => 'required|in:character,partner',
            'universe' => 'nullable|string|max:255',
            'options' => 'required|array|min:1',
            'options.*.emoji' => 'required|string',
            'options.*.label' => 'required|string',
            'options.*.traits' => 'required|array',
        ]);

        $datingQuestion->update($validated);

        return redirect()->route('admin.dating-questions.index')
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DatingQuestion $datingQuestion)
    {
        $datingQuestion->delete();

        return redirect()->route('admin.dating-questions.index')
            ->with('success', 'Question deleted successfully.');
    }
}
