<div x-data="playerManager()" x-init="init()" class="flex flex-col h-full">
    <!-- Header with quick stats -->
    <div class="flex flex-row items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-black text-gray-900 dark:text-white">{{ __('Players') }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium"><span x-text="players.length" class="text-indigo-500 italic"></span> {{ __('total registered') }}</p>
        </div>
        <button type="button" @click="addPlayer()" class="inline-flex items-center px-4 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-sm font-bold rounded-2xl transition shadow-md shadow-indigo-500/20">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            {{ __('Add') }}
        </button>
    </div>

    <!-- Active Player List - Scrollable on small screens if long -->
    <div class="space-y-3 mb-8">
        <template x-for="(player, index) in players" :key="index">
            <div class="relative bg-gray-50/80 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-600/50 rounded-2xl overflow-hidden transition-all hover:border-indigo-400/50">
                <div class="flex items-center p-1 px-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-black text-xs" x-text="index + 1"></div>
                    <input type="text" 
                        x-model="players[index]" 
                        class="flex-1 bg-transparent border-none focus:ring-0 text-base font-bold text-gray-900 dark:text-gray-100 placeholder-gray-400 py-4"
                        placeholder="{{ __('Player Name...') }}"
                        @blur="saveToSessionStorage()">
                    <button type="button" @click="removePlayer(index)" class="p-3 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
        
        <div x-show="players.length === 0" class="py-12 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-[2rem] flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 p-6 text-center">
            <div class="mb-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <p class="text-sm font-medium italic">{{ __('The roster is empty.') }}<br>{{ __('Start by adding some players!') }}</p>
        </div>
    </div>

    <!-- Footer Actions - Sticky-friendly height -->
    <div class="mt-auto pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-gray-100 dark:border-gray-700">
        <div class="flex items-center">
            <span x-show="isSaving" x-transition class="text-xs font-bold text-emerald-500 flex items-center bg-emerald-500/10 px-3 py-1.5 rounded-full">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-ping mr-2"></span>
                {{ __('SYNCED TO SERVER') }}
            </span>
        </div>
        <button type="button" @click="saveToServer" 
            class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl shadow-xl shadow-indigo-500/30 transition transform active:scale-95 disabled:opacity-50 flex items-center justify-center uppercase tracking-widest text-xs" 
            :disabled="isSaving">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            {{ __('Save Player List') }}
        </button>
    </div>

    <script>
        window.playerManager = function() {
            return {
                players: [],
                lastWinner: null,
                isSaving: false,

                init() {
                    const stored = sessionStorage.getItem('players_draft');
                    if (stored) {
                        this.players = JSON.parse(stored);
                    } else {
                        const rootEl = this.$el.closest('[data-initial-players]');
                        this.players = JSON.parse(rootEl?.dataset.initialPlayers || '[]');
                    }
                    this.$watch('players', () => {
                        this.saveToSessionStorage();
                    });
                },

                addPlayer() {
                    this.players.push('');
                    this.$nextTick(() => {
                        const inputs = this.$el.querySelectorAll('input');
                        inputs[inputs.length - 1]?.focus();
                    });
                },

                removePlayer(index) {
                    this.players.splice(index, 1);
                },

                saveToSessionStorage() {
                    sessionStorage.setItem('players_draft', JSON.stringify(this.players));
                },

                async saveToServer() {
                    if (this.isSaving) return;
                    this.isSaving = true;
                    try {
                        const response = await fetch('{{ route('tools.players.update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ names: this.players })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.players = data.names;
                            this.saveToSessionStorage();
                            setTimeout(() => this.isSaving = false, 2500);
                        }
                    } catch (e) {
                        console.error('Save failed', e);
                        this.isSaving = false;
                    }
                }
            }
        }
    </script>
</div>
