<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PlayerManager extends Component
{
    public $players = [];
    public $newPlayer = '';

    public function mount($initialPlayers)
    {
        $this->players = json_decode($initialPlayers, true) ?? [];
    }

    public function addPlayer()
    {
        if ($this->newPlayer) {
            $this->players[] = $this->newPlayer;
            $this->newPlayer = '';
            $this->dispatchBrowserEvent('players-updated', ['players' => $this->players]);
        }
    }

    public function removePlayer($index)
    {
        array_splice($this->players, $index, 1);
        $this->dispatchBrowserEvent('players-updated', ['players' => $this->players]);
    }

    public function render()
    {
        return view('livewire.player-manager');
    }
}
