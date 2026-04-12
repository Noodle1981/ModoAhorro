<?php

namespace App\Livewire\Physical;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Contract;
use Illuminate\Support\Facades\Validator;

#[Layout('layouts.app')]
class InvoiceManager extends Component
{
    public Entity $entity;
    
    // Modal State
    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $invoiceId = null;

    // Form Fields
    public $invoice_number;
    public $invoice_date;
    public $start_date;
    public $end_date;
    public $total_energy_consumed_kwh;
    public $total_amount;
    
    // Advanced Fields (Hidden by default)
    public bool $showAdvanced = false;
    public $cost_for_energy;
    public $cost_for_power;
    public $taxes;
    public $other_charges;

    public function mount(Entity $entity)
    {
        $this->entity = $entity;
    }

    #[Computed]
    public function contract()
    {
        return $this->entity->contracts()->where('is_active', true)->first();
    }

    #[Computed]
    public function invoices()
    {
        if (!$this->contract) return collect();
        
        return $this->contract->invoices()
            ->with('usageAdjustment')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function resetForm()
    {
        $this->reset([
            'invoice_number', 'invoice_date', 'start_date', 'end_date', 
            'total_energy_consumed_kwh', 'total_amount', 'invoiceId',
            'isEditing', 'showAdvanced', 'cost_for_energy', 'cost_for_power',
            'taxes', 'other_charges'
        ]);
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
        $invoice = Invoice::findOrFail($id);
        
        $this->invoiceId = $invoice->id;
        $this->isEditing = true;
        
        $this->invoice_number = $invoice->invoice_number;
        $this->invoice_date = $invoice->invoice_date;
        $this->start_date = $invoice->start_date;
        $this->end_date = $invoice->end_date;
        $this->total_energy_consumed_kwh = $invoice->total_energy_consumed_kwh;
        $this->total_amount = $invoice->total_amount;
        
        $this->cost_for_energy = $invoice->cost_for_energy;
        $this->cost_for_power = $invoice->cost_for_power;
        $this->taxes = $invoice->taxes;
        $this->other_charges = $invoice->other_charges;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'invoice_number' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_energy_consumed_kwh' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if (!$this->contract) {
            session()->flash('error', 'No hay un contrato activo para guardar la factura.');
            return;
        }

        $data = [
            'contract_id' => $this->contract->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date ?? $this->start_date,
            'issue_date' => $this->invoice_date ?? $this->start_date,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_energy_consumed_kwh' => $this->total_energy_consumed_kwh,
            'total_amount' => round($this->total_amount),
            'cost_for_energy' => round($this->cost_for_energy ?? 0),
            'cost_for_power' => round($this->cost_for_power ?? 0),
            'taxes' => round($this->taxes ?? 0),
            'other_charges' => round($this->other_charges ?? 0),
        ];

        if ($this->isEditing) {
            Invoice::findOrFail($this->invoiceId)->update($data);
            session()->flash('success', 'Factura actualizada correctamente.');
        } else {
            Invoice::create($data);
            session()->flash('success', 'Factura cargada correctamente.');
        }

        // Trigger entity recalculations if needed
        // $this->entity->updateThermalLabel();

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        session()->flash('success', 'Factura eliminada.');
    }

    public function toggleAdvanced()
    {
        $this->showAdvanced = !$this->showAdvanced;
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.physical.invoice-manager', [
            'config' => $config
        ]);
    }
}
