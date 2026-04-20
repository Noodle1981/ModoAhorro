<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Entity;
use App\Services\VacationService;

#[Layout('layouts.app')]
class VacationPlan extends Component
{
    public Entity $entity;
    public $days = 15; // Valor por defecto
    public $step = 1; // 1: Input, 2: Result/Checklist
    public $result = null;

    public function mount(Entity $entity)
    {
        $this->entity = $entity->load(['rooms.equipment.type']);
    }

    public function calculate(VacationService $service)
    {
        $this->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $this->result = $service->generateChecklist($this->entity, $this->days);
        $this->step = 2;
    }

    public function confirm(VacationService $service)
    {
        $markedCount = $service->markAnomalousInvoices($this->entity, $this->days);

        $config = config("entity_types.{$this->entity->type}", []);
        $message = '¡Buen viaje! Tu plan de ahorro ha sido registrado.';
        if ($markedCount > 0) {
            $message .= " Se han marcado $markedCount facturas como 'Modo Vacaciones'.";
        }

        session()->flash('success', $message);
        return redirect()->route($config['route_prefix'] . '.show', $this->entity->id);
    }

    public function resetPlan()
    {
        $this->step = 1;
        $this->result = null;
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.vacation-plan', [
            'config' => $config
        ]);
    }
}
