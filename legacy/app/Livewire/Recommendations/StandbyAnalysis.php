<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Entity;
use App\Services\StandbyAnalysisService;

#[Layout('layouts.app')]
class StandbyAnalysis extends Component
{
    public Entity $entity;
    public $results = [];

    public function mount(Entity $entity, StandbyAnalysisService $service)
    {
        $this->entity = $entity;
        $this->refreshResults($service);
    }

    public function toggleStandby($equipmentId, StandbyAnalysisService $service)
    {
        $service->toggleEquipmentStandby($equipmentId);
        $this->refreshResults($service);
    }

    protected function refreshResults(StandbyAnalysisService $service)
    {
        $this->results = $service->calculateStandbyAnalysis($this->entity);
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        
        return view('livewire.recommendations.standby-analysis', array_merge($this->results, [
            'config' => $config
        ]));
    }
}
