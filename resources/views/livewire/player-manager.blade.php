<div>
    <div class="flex items-center">
        <input type="text" wire:model.defer="newPlayer" class="border-gray-300 rounded-md shadow-sm" placeholder="Add new player">
        <button wire:click="addPlayer" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md">Add</button>
    </div>
    <ul class="mt-4">
        @foreach($players as $index => $player)
            <li class="flex items-center justify-between py-2">
                <span>{{ $player }}</span>
                <button wire:click="removePlayer({{ $index }})" class="text-red-500">Remove</button>
            </li>
        @endforeach
    </ul>
</div>
