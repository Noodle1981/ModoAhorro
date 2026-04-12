<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Entity;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Services\Recommendations\ReplacementService;

#[Layout('layouts.app')]
class ReplacementManager extends Component
{
    public Entity $entity;
    public $showRefineModal = false;
    
    // Form fields for refinement
    public ?Equipment $editingEquipment = null;
    public $acquisition_year;
    public $energy_label;
    public $capacity;
    public $capacity_unit;
    public $is_inverter;

    public function mount(Entity $entity)
    {
        $this->entity = $entity;
    }

    #[Computed]
    public function latestInvoice()
    {
        return Invoice::whereHas('contract', function ($query) {
            $query->where('entity_id', $this->entity->id);
        })->latest('end_date')->first();
    }

    #[Computed]
    public function opportunities()
    {
        $service = app(ReplacementService::class);
        return $service->generateOpportunities($this->entity, $this->latestInvoice);
    }

    #[Computed]
    public function analyzableEquipments()
    {
        return $this->entity->rooms()
            ->with(['equipment.type', 'equipment.category'])
            ->get()
            ->flatMap->equipment
            ->filter(fn($eq) => $eq->type !== null)
            ->unique('id')
            ->values();
    }

    public function openRefineModal($equipmentId)
    {
        $this->editingEquipment = Equipment::findOrFail($equipmentId);
        $this->acquisition_year = $this->editingEquipment->acquisition_year;
        $this->energy_label = $this->editingEquipment->energy_label;
        $this->capacity = $this->editingEquipment->capacity;
        $this->capacity_unit = $this->editingEquipment->capacity_unit;
        $this->is_inverter = (bool)$this->editingEquipment->is_inverter;
        
        $this->showRefineModal = true;
    }

    public function saveRefinement()
    {
        $this->validate([
            'acquisition_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'energy_label' => 'nullable|string|in:A+++,A++,A+,A,B,C,D,E',
            'capacity' => 'nullable|numeric|min:0',
            'capacity_unit' => 'nullable|string',
            'is_inverter' => 'boolean',
        ]);

        $this->editingEquipment->update([
            'acquisition_year' => $this->acquisition_year,
            'energy_label' => $this->energy_label,
            'capacity' => $this->capacity,
            'capacity_unit' => $this->capacity_unit,
            'is_inverter' => $this->is_inverter,
        ]);

        $this->showRefineModal = false;
        $this->editingEquipment = null;
        
        session()->flash('success', 'Datos actualizados. La recomendación ha sido recalculada.');
    }

    public function closeModal()
    {
        $this->showRefineModal = false;
        $this->editingEquipment = null;
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.replacement-manager', [
            'config' => $config
        ]);
    }
}
