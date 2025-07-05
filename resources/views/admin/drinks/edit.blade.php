@extends('layouts.admin')

@section('title', 'Edit Drink')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Drink</h1>

    <form action="{{ route('admin.drinks.update', $drink->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="bar_id" class="block font-medium text-sm text-gray-700">Bar</label>
            <select name="bar_id" id="bar_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                @foreach($bars as $bar)
                    <option value="{{ $bar->id }}" {{ $drink->bar_id == $bar->id ? 'selected' : '' }}>{{ $bar->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
            <input type="text" name="name" id="name" value="{{ $drink->name }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
            <label for="price" class="block font-medium text-sm text-gray-700">Price</label>
            <input type="number" step="0.01" name="price" id="price" value="{{ $drink->price }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
            <label for="icon_svg" class="block font-medium text-sm text-gray-700">Icon SVG</label>
            <textarea name="icon_svg" id="icon_svg" rows="5" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $drink->icon_svg }}</textarea>
            @if($drink->icon_svg)
                <div class="mt-2">
                    <p class="text-sm text-gray-600">Current Icon Preview:</p>
                    <x-svg-icon class="h-12 w-12" :svg="$drink->icon_svg"></x-svg-icon>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Update Drink
            </button>
        </div>
    </form>
@endsection