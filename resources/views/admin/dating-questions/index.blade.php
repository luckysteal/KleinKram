@extends('layouts.admin')

@section('title', 'Manage Questions')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Questions</h1>
            <p class="text-sm text-gray-500">The pool of randomized questions for the Fragebogen.</p>
        </div>
        <a href="{{ route('admin.dating-questions.create') }}" class="inline-flex items-center px-4 py-2 bg-pink-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-pink-700 active:bg-pink-900 focus:outline-none focus:border-pink-900 focus:ring ring-pink-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-pink-200">
            Add Question
        </a>
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
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Universe</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Question Text</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Options</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($questions as $q)
                    <tr class="hover:bg-gray-50/80 transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $q->type === 'character' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-indigo-800' }}">
                                {{ ucfirst($q->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($q->universe)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-100">
                                    {{ $q->universe }}
                                </span>
                            @else
                                <span class="text-gray-300 italic text-[10px]">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $q->text }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex -space-x-1 overflow-hidden">
                                @foreach($q->options as $opt)
                                    <span class="inline-block h-6 w-6 rounded-full ring-2 ring-white bg-gray-50 text-xs flex items-center justify-center" title="{{ $opt['label'] }}">
                                        {{ $opt['emoji'] }}
                                    </span>
                                @endforeach
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1 block">{{ count($q->options) }} options</span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                            <a href="{{ route('admin.dating-questions.edit', $q->id) }}" class="text-blue-600 hover:text-blue-900 transition">Edit</a>
                            <form action="{{ route('admin.dating-questions.destroy', $q->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Really delete this question?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition font-bold">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic">No questions found in pool.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($questions->hasPages())
            <div class="px-6 py-4 bg-gray-50/30 border-t border-gray-100">
                {{ $questions->links() }}
            </div>
        @endif
    </div>
@endsection
