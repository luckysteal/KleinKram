<div x-data="playerManager()" x-init="init()">
    <form @submit.prevent="saveNames" class="mb-4">
        <h3 class="text-lg font-semibold mb-2">Player Management</h3>

        <div x-show="!showFullList">
            <p class="text-gray-700">
                <span x-text="players.length"></span> Players currently.
                <span x-show="lastWinner">Last Winner: <span x-text="lastWinner"></span></span>
            </p>
            <button type="button" @click="toggleFullList" class="px-4 py-2 bg-blue-500 text-white rounded-md mt-2">Edit Players</button>
        </div>

        <div x-show="showFullList">
            <template x-for="(player, index) in players" :key="index">
                <div class="flex items-center mb-2">
                    <input type="text" x-model="players[index]" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mr-2">
                    <button type="button" @click="removePlayer(index)" class="px-3 py-2 bg-red-500 text-white rounded-md">-</button>
                </div>
            </template>
            <button type="button" @click="addPlayer()" class="px-4 py-2 bg-green-500 text-white rounded-md mt-2">Add Player</button>
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md mt-2 ml-2">Save Players</button>
            <button type="button" @click="toggleFullList" class="px-4 py-2 bg-gray-500 text-white rounded-md mt-2 ml-2">Done</button>
        </div>
    </form>

    <script>
        window.playerManager = function() {
            return {
                players: [],
                lastWinner: null,
                showFullList: false,

                init() {
                    this.players = JSON.parse(this.$el.dataset.initialPlayers || '[]');
                    this.lastWinner = JSON.parse(this.$el.dataset.initialLastWinner || 'null');
                },

                addPlayer() {
                    this.players.push('');
                },

                removePlayer(index) {
                    this.players.splice(index, 1);
                },

                toggleFullList() {
                    this.showFullList = !this.showFullList;
                },

                async saveNames() {
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
                        this.toggleFullList();
                        // Optionally show a success message
                    } else {
                        // Handle error
                    }
                }
            }
        }
    </script>
</div>
