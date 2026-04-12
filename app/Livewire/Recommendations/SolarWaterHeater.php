<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Entity;
use App\Services\SolarWaterHeaterService;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
class SolarWaterHeater extends Component
{
    public Entity $entity;
    public $peopleCount;
    public $result = null;
    public $hasElectricWaterHeater = false;
    public $climateProfile = null;

    public function mount(Entity $entity, SolarWaterHeaterService $service)
    {
        $this->entity = $entity->load(['rooms.equipment.type', 'locality']);
        $this->peopleCount = $this->entity->people_count ?? 4;

        // Detectar si ya tiene termotanque eléctrico
        $this->hasElectricWaterHeater = $this->entity->rooms->flatMap->equipment->contains(function ($eq) {
            return Str::contains(strtolower($eq->type->name ?? ''), ['termotanque', 'calefon']) 
                && Str::contains(strtolower($eq->type->category ?? ''), 'electr');
        });

        // Si ya tiene eléctrico, calculamos de entrada para mostrar el ahorro potencial
        if ($this->hasElectricWaterHeater) {
            $this->calculate($service);
        }
    }

    public function updatedPeopleCount()
    {
        $this->calculate(app(SolarWaterHeaterService::class));
    }

    public function calculate(SolarWaterHeaterService $service)
    {
        $data = $service->calculateWaterHeaterData($this->entity, $this->peopleCount);
        $this->result = $data;
        $this->climateProfile = $data['climateProfile'];
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.solar-water-heater', [
            'config' => $config
        ]);
    }
}
