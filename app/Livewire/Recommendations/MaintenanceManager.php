<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Entity;
use App\Models\Equipment;
use App\Models\MaintenanceLog;
use App\Services\MaintenanceService;
use Carbon\Carbon;

#[Layout('layouts.app')]
class MaintenanceManager extends Component
{
    public Entity $entity;
    public $notes = [];
    public $selectedTask = [];

    public function mount(Entity $entity)
    {
        $this->entity = $entity;
    }

    #[Computed]
    public function maintenanceData()
    {
        $equipments = Equipment::whereHas('room', function($q) {
            $q->where('entity_id', $this->entity->id);
        })->with(['type.maintenanceTasks', 'room'])->get();

        $service = app(MaintenanceService::class);
        $data = [];
        
        foreach ($equipments as $equipment) {
            if ($equipment->type && $equipment->type->maintenanceTasks->isNotEmpty()) {
                $status = $service->checkStatus($equipment);
                $data[] = [
                    'equipment' => $equipment,
                    'status' => $status
                ];
            }
        }

        return $data;
    }

    public function registerMaintenance($equipmentId)
    {
        $taskId = $this->selectedTask[$equipmentId] ?? null;
        $note = $this->notes[$equipmentId] ?? null;

        if (!$taskId) {
            $this->addError("task.$equipmentId", 'Selecciona una tarea.');
            return;
        }

        MaintenanceLog::create([
            'equipment_id' => $equipmentId,
            'maintenance_task_id' => $taskId,
            'completed_at' => Carbon::now(),
            'notes' => $note
        ]);

        // Reset inputs for this equipment
        unset($this->selectedTask[$equipmentId]);
        unset($this->notes[$equipmentId]);

        session()->flash('success', 'Mantenimiento registrado correctamente.');
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.maintenance-manager', [
            'config' => $config
        ]);
    }
}
