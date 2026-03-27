@extends('layouts.admin')

@section('title', 'Edit Dating Question')

@section('content')
<div class="max-w-4xl mx-auto py-10" x-data="{ 
    type: '{{ $question->type }}', 
    options: {{ json_encode($question->options) }},
    addOption() { 
        this.options.push({ emoji: '🆕', label: '', traits: { Spontaneous: 0, Homebody: 0, Adventurous: 0, Romantic: 0, Logical: 0, Organized: 0, Social: 0, Creative: 0 } });
    },
    removeOption(index) {
        if(this.options.length > 1) this.options.splice(index, 1);
    }
}">
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Edit Question</h1>
        <a href="{{ route('admin.dating-questions.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition underline font-black uppercase tracking-widest text-xs">Back to List</a>
    </div>

    <form action="{{ route('admin.dating-questions.update', $question->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="bg-white shadow-2xl rounded-3xl overflow-hidden border border-gray-100">
            <div class="p-8 space-y-6">
                <!-- Question Type -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-400 tracking-widest mb-2">Question Context</label>
                        <select name="type" x-model="type" class="w-full rounded-2xl border-gray-100 bg-gray-50 py-4 px-6 text-gray-700 font-bold focus:ring-pink-500 focus:border-pink-500 transition">
                            <option value="character">Part 1: Who are you? (Character)</option>
                            <option value="partner">Part 2: What do you want? (Partner)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-400 tracking-widest mb-2">Universe / Category</label>
                        <select name="universe" class="w-full rounded-2xl border-gray-100 bg-gray-50 py-4 px-6 text-gray-700 font-bold focus:ring-pink-500 focus:border-pink-500 transition">
                            <option value="">None (Generic)</option>
                            <option value="Phineas & Ferb" {{ $question->universe === 'Phineas & Ferb' ? 'selected' : '' }}>Phineas & Ferb</option>
                            <option value="Harry Potter" {{ $question->universe === 'Harry Potter' ? 'selected' : '' }}>Harry Potter</option>
                            <option value="High School Musical" {{ $question->universe === 'High School Musical' ? 'selected' : '' }}>High School Musical</option>
                            <option value="Lord of the Rings" {{ $question->universe === 'Lord of the Rings' ? 'selected' : '' }}>Lord of the Rings</option>
                            <option value="Bernd das Brot" {{ $question->universe === 'Bernd das Brot' ? 'selected' : '' }}>Bernd das Brot</option>
                        </select>
                    </div>
                </div>

                <!-- Question Text -->
                <div>
                  <label class="block text-xs font-black uppercase text-gray-400 tracking-widest mb-2">The Question</label>
                  <input type="text" name="text" value="{{ $question->text }}" placeholder="e.g. Harry Potter: Der sprechende Hut?" class="w-full rounded-2xl border-gray-100 bg-gray-50 py-4 px-6 text-gray-700 font-bold focus:ring-pink-500 focus:border-pink-500 transition" required>
                </div>
            </div>

            <!-- Options Management -->
            <div class="bg-gray-50/50 p-8 border-t border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <label class="block text-xs font-black uppercase text-gray-400 tracking-widest">Answer Options & Vibe Weights</label>
                    <button type="button" @click="addOption()" class="text-xs font-black text-pink-600 bg-pink-50 px-3 py-1.5 rounded-full hover:bg-pink-100 transition tracking-widest uppercase">+ Add Option</button>
                </div>

                <div class="space-y-4">
                    <template x-for="(option, index) in options" :key="index">
                        <div class="bg-white p-6 rounded-3xl border border-gray-200 shadow-sm relative group overflow-hidden">
                            <div class="grid grid-cols-12 gap-6 items-start">
                                <!-- Emoji & Label -->
                                <div class="col-span-12 md:col-span-5 flex gap-4">
                                    <div class="w-16">
                                        <input type="text" :name="`options[${index}][emoji]`" x-model="option.emoji" class="w-full text-center text-2xl rounded-xl border-gray-100 bg-gray-50 py-3 focus:ring-pink-500" required>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" :name="`options[${index}][label]`" x-model="option.label" placeholder="Option Label" class="w-full rounded-xl border-gray-100 bg-gray-50 py-3 px-4 font-bold text-sm focus:ring-pink-500" required>
                                    </div>
                                </div>

                                <!-- Traits Grid -->
                                <div class="col-span-12 md:col-span-6 grid grid-cols-4 gap-2">
                                    <template x-for="(val, trait) in option.traits" :key="trait">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] font-black uppercase text-gray-400 mb-1" x-text="trait"></span>
                                            <input type="number" :name="`options[${index}][traits][${trait}]`" x-model="option.traits[trait]" class="w-full rounded-lg border-gray-100 bg-gray-50 py-1.5 px-2 text-xs font-bold text-center focus:ring-pink-500" min="0" max="10">
                                        </div>
                                    </template>
                                </div>

                                <!-- Remove -->
                                <div class="col-span-12 md:col-span-1 flex items-center justify-end">
                                    <button type="button" @click="removeOption(index)" class="p-2 text-gray-300 hover:text-red-500 transition" title="Delete Option">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="p-8 border-t border-gray-100 flex justify-end bg-white">
                <button type="submit" class="inline-flex items-center px-8 py-4 bg-pink-600 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-pink-700 active:bg-pink-900 transition duration-150 shadow-xl shadow-pink-200 hover:scale-[1.02] transform active:scale-[0.98]">
                    Save Changes ✨
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
