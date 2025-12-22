<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Entity;

class EntityTypeCard extends Component
{
    public string $type;
    public array $config;
    public bool $allowed;
    
    public function mount(string $type, array $config, bool $allowed = true)
    {
        $this->type = $type;
        $this->config = $config;
        $this->allowed = $allowed;
    }

    public function getEntitiesProperty()
    {
        return Entity::where('type', $this->type)
            ->whereHas('users', fn($q) => $q->where('user_id', auth()->id()))
            ->with('locality')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.entity-type-card', [
            'entities' => $this->entities,
        ]);
    }
}
