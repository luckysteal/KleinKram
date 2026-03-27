@extends('layouts.admin')

@section('title', 'View Result: ' . $result->name)

@section('content')
    <div class="mb-8">
        <a href="{{ route('admin.dating-match-results.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-900 transition flex items-center gap-1 group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Stats Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 h-full">
                <div class="bg-gradient-to-r from-pink-500 to-purple-600 p-8 text-white h-[200px] flex flex-col justify-end relative">
                    <div class="absolute top-4 right-4 bg-white/20 px-3 py-1 rounded-full text-xs font-bold backdrop-blur-sm">
                        {{ $result->franchise }}
                    </div>
                    <h2 class="text-4xl font-black">{{ $result->name }}</h2>
                    <p class="text-white/80 font-medium">{{ $result->gender }} • Seeking {{ $result->seeking }}</p>
                </div>
                <div class="p-8 space-y-8">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-400 block mb-1">Character Match</span>
                        <h3 class="text-3xl font-black text-gray-900">{{ $result->mapped_character }}</h3>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-400 block mb-3">Core Traits (Scoring)</span>
                        <div class="space-y-3">
                            @foreach ($result->traits as $trait => $score)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-600">{{ $trait }}</span>
                                    <div class="w-32 h-2 bg-gray-100 rounded-full overflow-hidden relative">
                                        <div class="bg-pink-500 h-full transition-all duration-500" style="width: {{ ($score / 21) * 100 }}%"></div>
                                    </div>
                                    <span class="text-xs font-black text-gray-900">{{ $score }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100 flex gap-3">
                        <a href="{{ route('admin.dating-match-results.edit', $result->id) }}" class="flex-1 bg-gray-100 text-gray-800 font-bold py-3 rounded-xl text-center hover:bg-gray-200 transition">Edit Basic Info</a>
                        <a href="{{ route('fragebogen.show', $result->id) }}" target="_blank" class="bg-indigo-600 text-white font-bold p-3 rounded-xl hover:shadow-lg transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details & Transcript Column -->
        <div class="lg:col-span-2 space-y-8">
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Detailed Answer Log (Transcript)
            </h3>
            
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                <div class="divide-y divide-gray-50 bg-white">
                    @foreach ($result->full_results as $index => $item)
                        <div class="p-6 hover:bg-gray-50/50 transition">
                            <div class="flex items-start gap-4">
                                <span class="bg-gray-100 text-gray-400 font-black text-[10px] w-6 h-6 rounded-lg flex items-center justify-center shrink-0">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-gray-900 mb-1 leading-tight">{{ $item['question'] }}</h4>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black text-indigo-600 px-2 py-0.5 rounded-md bg-indigo-50">Answer: {{ $item['answer'] }}</span>
                                        <div class="flex gap-1">
                                            @foreach ($item['traits'] as $trait => $val)
                                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">{{ $trait }}+{{ $val }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="shrink-0 text-[10px] font-bold py-1 px-2 rounded-lg {{ $item['type'] === 'character' ? 'bg-pink-100 text-pink-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ ucfirst($item['type']) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-6 bg-gray-50 rounded-2xl border border-gray-200">
                <h4 class="text-xs font-bold uppercase text-gray-400 mb-4 tracking-widest">Raw Data (Debugging)</h4>
                <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto text-[10px] leading-relaxed text-blue-300 font-mono shadow-inner max-h-[200px]">
                    <pre>{{ json_encode($result, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>
@endsection
