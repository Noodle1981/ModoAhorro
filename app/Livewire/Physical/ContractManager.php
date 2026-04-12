<?php

namespace App\Livewire\Physical;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Contract;
use App\Models\Entity;
use App\Models\Proveedor;

#[Layout('layouts.app')]
class ContractManager extends Component
{
    // Context
    public ?int $entityId = null; // If provided, manages only for that entity
    
    // Modal State
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $contractId = null;

    // Form Fields
    public $selected_entity_id;
    public $proveedor_id;
    public $supply_number;
    public $meter_number;
    public $client_number;
    public $rate_name;
    public $is_three_phase = false;
    public $contracted_power_kw_p1;
    public $contracted_power_kw_p2;
    public $contracted_power_kw_p3;
    public $start_date;
    public $is_active = true;

    public function mount(?int $entityId = null)
    {
        $this->entityId = $entityId;
        $this->selected_entity_id = $entityId;
    }

    #[Computed]
    public function contracts()
    {
        $query = Contract::with(['entity', 'proveedor']);
        
        if ($this->entityId) {
            $query->where('entity_id', $this->entityId);
        }

        return $query->latest()->get();
    }

    #[Computed]
    public function entities()
    {
        return Entity::all();
    }

    #[Computed]
    public function proveedores()
    {
        return Proveedor::all();
    }

    public function resetForm()
    {
        $this->reset([
            'proveedor_id', 'supply_number', 'meter_number', 'client_number',
            'rate_name', 'is_three_phase', 'contracted_power_kw_p1',
            'contracted_power_kw_p2', 'contracted_power_kw_p3',
            'start_date', 'contractId', 'isEditing'
        ]);
        $this->selected_entity_id = $this->entityId;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $contract = Contract::findOrFail($id);
        
        $this->contractId = $contract->id;
        $this->isEditing = true;
        
        $this->selected_entity_id = $contract->entity_id;
        $this->proveedor_id = $contract->proveedor_id;
        $this->supply_number = $contract->supply_number;
        $this->meter_number = $contract->meter_number;
        $this->client_number = $contract->client_number;
        $this->rate_name = $contract->rate_name;
        $this->is_three_phase = (bool)$contract->is_three_phase;
        $this->contracted_power_kw_p1 = $contract->contracted_power_kw_p1;
        $this->contracted_power_kw_p2 = $contract->contracted_power_kw_p2;
        $this->contracted_power_kw_p3 = $contract->contracted_power_kw_p3;
        $this->start_date = $contract->start_date;
        $this->is_active = (bool)$contract->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'selected_entity_id' => 'required|exists:entities,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'supply_number' => 'required|string',
            'rate_name' => 'required|string',
            'contracted_power_kw_p1' => 'required|numeric|min:0',
        ]);

        $data = [
            'entity_id' => $this->selected_entity_id,
            'proveedor_id' => $this->proveedor_id,
            'supply_number' => $this->supply_number,
            'meter_number' => $this->meter_number ?? 'N/A',
            'client_number' => $this->client_number ?? 'N/A',
            'rate_name' => $this->rate_name,
            'is_three_phase' => $this->is_three_phase,
            'contracted_power_kw_p1' => $this->contracted_power_kw_p1,
            'contracted_power_kw_p2' => $this->contracted_power_kw_p2,
            'contracted_power_kw_p3' => $this->contracted_power_kw_p3,
            'start_date' => $this->start_date ?? now()->toDateString(),
            'is_active' => $this->is_active,
            'contract_number' => 'CONT-' . strtoupper(uniqid()), // Fallback
        ];

        if ($this->is_active && !$this->isEditing) {
            // Deactivate other contracts for the same entity if this one is new and active
            Contract::where('entity_id', $this->selected_entity_id)->update(['is_active' => false]);
        }

        if ($this->isEditing) {
            Contract::findOrFail($this->contractId)->update($data);
            session()->flash('success', 'Contrato actualizado.');
        } else {
            Contract::create($data);
            session()->flash('success', 'Contrato registrado.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();
        session()->flash('success', 'Contrato eliminado.');
    }

    public function toggleActive($id)
    {
        $contract = Contract::findOrFail($id);
        
        if (!$contract->is_active) {
            // Deactivate others first
            Contract::where('entity_id', $contract->entity_id)->update(['is_active' => false]);
        }
        
        $contract->update(['is_active' => !$contract->is_active]);
        session()->flash('success', 'Estado de contrato actualizado.');
    }

    public function render()
    {
        return view('livewire.physical.contract-manager');
    }
}
