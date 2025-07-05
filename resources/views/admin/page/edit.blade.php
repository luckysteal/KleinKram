@extends('layouts.admin')

@section('title', 'Edit Page')

@section('content')
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Edit Page</h1>

    <form action="{{ route('admin.page.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="title" class="block font-medium text-sm text-gray-700">Title</label>
            <input type="text" name="title" id="title" value="{{ $page->title }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        </div>

        <div class="mb-4">
            <label for="content" class="block font-medium text-sm text-gray-700">Content</label>
            <textarea name="content" id="content" cols="30" rows="10" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ $page->content }}</textarea>
        </div>

        <div class="mb-4">
            <input type="checkbox" name="global_tax_enabled" id="global_tax_enabled" value="1" {{ $page->global_tax_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="global_tax_enabled" class="ml-2 text-sm text-gray-700">Enable Global Tax Calculation</label>
        </div>

        <div class="mb-4">
            <input type="checkbox" name="german_tax_enabled" id="german_tax_enabled" value="1" {{ $page->german_tax_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="german_tax_enabled" class="ml-2 text-sm text-gray-700">Apply German Tax Calculation</label>
        </div>

        <div class="mb-4">
            <input type="checkbox" name="church_tax_enabled" id="church_tax_enabled" value="1" {{ $page->church_tax_enabled ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <label for="church_tax_enabled" class="ml-2 text-sm text-gray-700">Apply Church Tax</label>
        </div>

        <h2 class="text-xl font-bold text-gray-800 mb-4">Manage Badges</h2>

        <div x-data="{ badges: {{ json_encode($page->badges ?? []) }} }">
            <template x-for="(badge, index) in badges" :key="index">
                <div class="mb-4 p-4 border rounded-md">
                    <label :for="`badge_name_${index}`" class="block font-medium text-sm text-gray-700">Badge Name</label>
                    <input type="text" :name="`badges[${index}][name]`" :id="`badge_name_${index}`" x-model="badge.name" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

                    <label :for="`badge_url_${index}`" class="block font-medium text-sm text-gray-700 mt-2">Badge URL</label>
                    <input type="text" :name="`badges[${index}][url]`" :id="`badge_url_${index}`" x-model="badge.url" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">

                    <button type="button" @click="badges.splice(index, 1)" class="mt-2 inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Remove
                    </button>
                </div>
            </template>

            <button type="button" @click="badges.push({ name: '', url: '' })" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Add Badge
            </button>
        </div>

        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Update
            </button>
        </div>
    </form>
@endsection