@extends('layouts.admin')

@section('title', 'Edit Bar')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Bar</h1>

    <form action="{{ route('admin.bars.update', $bar->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="name" class="block font-medium text-sm text-gray-700">Name</label>
            <input type="text" name="name" id="name" value="{{ $bar->name }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        </div>

        <div class="mb-4">
            <label for="address" class="block font-medium text-sm text-gray-700">Address</label>
            <input type="text" name="address" id="address" value="{{ $bar->address }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Update
            </button>
        </div>
    </form>
@endsection