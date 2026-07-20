@props([
    'icon',
    'tooltip',
    'action' => null,
    'activeColor' => 'cyan',
    'dropdown' => false
])

<div class="relative group" @if($dropdown) x-data="{ open: false }" @endif>
    <button type="button" @click="{{ $dropdown ? 'open = !open' : $action }}" 
            @if($dropdown) @click.outside="open = false" @endif
            {{ $attributes->merge(['class' => "flex h-9 w-9 items-center justify-center rounded-xl transition-all hover:scale-105 active:scale-95"]) }}
            :class="{
                'text-cyan-400 hover:bg-cyan-500/10 hover:text-cyan-300': '{{ $activeColor }}' === 'cyan' || '{{ $activeColor }}' === 'blue',
                'text-emerald-400 hover:bg-emerald-500/10 hover:text-emerald-300': '{{ $activeColor }}' === 'emerald' || '{{ $activeColor }}' === 'green',
                'text-rose-400 hover:bg-rose-500/10 hover:text-rose-300': '{{ $activeColor }}' === 'rose' || '{{ $activeColor }}' === 'red',
                'text-amber-400 hover:bg-amber-500/10 hover:text-amber-300': '{{ $activeColor }}' === 'amber' || '{{ $activeColor }}' === 'yellow',
                'text-purple-400 hover:bg-purple-500/10 hover:text-purple-300': '{{ $activeColor }}' === 'purple'
            }">
        <i class="{{ $icon }} text-base"></i>
    </button>
    
    @if($dropdown)
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-2"
             class="absolute left-14 top-0 w-48 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl z-50 py-2 overflow-hidden backdrop-blur-xl"
             style="display: none;">
            {{ $slot }}
        </div>
    @endif

    <div @if($dropdown) x-show="!open" @endif 
         class="absolute left-14 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-gray-950 text-gray-200 text-[10px] font-bold uppercase tracking-widest rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50 shadow-xl border border-gray-700">
        {{ $tooltip }}
    </div>
</div>
