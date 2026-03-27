@extends('layouts.admin')

@section('title', 'Edit Result: ' . $result->name)

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.dating-match-results.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-900 transition flex items-center gap-1 group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to List
        </a>
    </div>

    <div class="max-w-2xl bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 mx-auto">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-8 text-white h-[120px] flex flex-col justify-center">
            <h2 class="text-3xl font-black">Edit Result</h2>
            <p class="text-white/80 font-medium">User: {{ $result->name }}</p>
        </div>
        <form action="{{ route('admin.dating-match-results.update', $result->id) }}" method="POST" class="p-8 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $result->name) }}" required
                        class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold shadow-inner">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Identify as</label>
                    <select name="gender" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold shadow-inner">
                        @foreach(['Male', 'Female', 'Other'] as $gender)
                            <option value="{{ $gender }}" {{ old('gender', $result->gender) === $gender ? 'selected' : '' }}>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Looking for</label>
                    <select name="seeking" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold shadow-inner">
                        @foreach(['Men', 'Women', 'Everyone'] as $seeking)
                            <option value="{{ $seeking }}" {{ old('seeking', $result->seeking) === $seeking ? 'selected' : '' }}>{{ $seeking }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Universe</label>
                    <select name="franchise" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold shadow-inner">
                        @foreach(['High School Musical', 'Harry Potter', 'Phineas & Ferb', 'Lord of the Rings', 'Bernd das Brot', 'Wildcard'] as $f)
                            <option value="{{ $f }}" {{ old('franchise', $result->franchise) === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-4">
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2 px-1">Character Match (Manual Correction)</label>
                <input type="text" name="mapped_character" value="{{ old('mapped_character', $result->mapped_character) }}" required
                    class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-black text-indigo-700 shadow-inner">
            </div>

            <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100 mt-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[10px] leading-tight text-amber-900 font-medium italic">Trait-Scores and full results (transcript) are read-only to preserve the scientific integrity of the result as it was lived by the user. Editing these would require directly modifying the JSON in the database.</p>
                </div>
            </div>

            <div class="pt-8 flex gap-4">
                <button type="submit" class="flex-1 bg-gray-900 text-white font-bold py-4 rounded-xl hover:shadow-2xl hover:bg-black transition-all transform hover:-translate-y-1">Save Changes</button>
                <a href="{{ route('admin.dating-match-results.index') }}" class="bg-gray-100 text-gray-400 font-bold px-6 py-4 rounded-xl hover:bg-gray-200 transition">Discard</a>
            </div>
        </form>
    </div>
@endsection
