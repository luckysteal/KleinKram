<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bar;
use App\Models\Drink;
use Illuminate\Http\Request;

class DrinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drinks = Drink::with('bar')->get();
        return view('admin.drinks.index', compact('drinks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bars = Bar::all();
        return view('admin.drinks.create', compact('bars'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bar_id' => 'required|exists:bars,id',
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'icon_svg' => 'nullable|string',
    ]);

        Drink::create($request->all());

        return redirect()->route('admin.drinks.index')->with('success', 'Drink created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Drink $drink)
    {
        return view('admin.drinks.show', compact('drink'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Drink $drink)
    {
        $bars = Bar::all();
        return view('admin.drinks.edit', compact('drink', 'bars'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Drink $drink)
    {
        $request->validate([
            'bar_id' => 'required|exists:bars,id',
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'icon_svg' => 'nullable|string',
    ]);

        $drink->update($request->all());

        return redirect()->route('admin.drinks.index')->with('success', 'Drink updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Drink $drink)
    {
        $drink->delete();

        return redirect()->route('admin.drinks.index')->with('success', 'Drink deleted successfully.');
    }
}
