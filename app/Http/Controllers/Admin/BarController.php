<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bar;
use Illuminate\Http\Request;

class BarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bars = Bar::all();
        return view('admin.bars.index', compact('bars'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.bars.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        Bar::create($request->all());

        return redirect()->route('admin.bars.index')->with('success', 'Bar created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bar $bar)
    {
        return view('admin.bars.show', compact('bar'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bar $bar)
    {
        return view('admin.bars.edit', compact('bar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bar $bar)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        $bar->update($request->all());

        return redirect()->route('admin.bars.index')->with('success', 'Bar updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bar $bar)
    {
        $bar->delete();

        return redirect()->route('admin.bars.index')->with('success', 'Bar deleted successfully.');
    }
}
