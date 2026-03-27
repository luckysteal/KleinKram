@extends('layouts.admin')

@section('title', 'Fragebogen Results')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Fragebogen Results</h1>
        <p class="text-sm text-gray-500">Total Entries: {{ $results->total() }}</p>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 shadow-sm rounded-r-lg" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm leading-5 font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Universe</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Match</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($results as $item)
                    <tr class="hover:bg-gray-50/80 transition duration-150">
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $item->name }}</div>
                            <div class="text-xs text-gray-500">{{ $item->gender }} • Seeking {{ $item->seeking }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $item->franchise }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 font-medium">
                            {{ $item->mapped_character }}
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400">
                            {{ $item->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                            <a href="{{ route('fragebogen.show', $item->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 transition font-bold">Public Link</a>
                            <a href="{{ route('admin.dating-match-results.show', $item->id) }}" class="text-gray-600 hover:text-gray-900 transition">View</a>
                            <a href="{{ route('admin.dating-match-results.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900 transition">Edit</a>
                            <form action="{{ route('admin.dating-match-results.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Really delete?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">No results found yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4 bg-gray-50/30 border-t border-gray-100">
            {{ $results->links() }}
        </div>
    </div>
@endsection
