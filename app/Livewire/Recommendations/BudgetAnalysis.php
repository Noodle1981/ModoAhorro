<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Entity;
use App\Services\BudgetService;

#[Layout('layouts.app')]
class BudgetAnalysis extends Component
{
    public Entity $entity;
    public $showBudgetModal = false;
    public $budgetRequested = false;

    public function mount(Entity $entity)
    {
        $this->entity = $entity;
    }

    #[Computed]
    public function data()
    {
        $service = app(BudgetService::class);
        return $service->calculateBudgetData($this->entity);
    }

    public function requestBudget()
    {
        // Here we could send an email or log the request
        // For now, we simulate the success state
        $this->budgetRequested = true;
        
        session()->flash('success', 'Solicitud registrada. Un especialista te contactará pronto.');
    }

    public function closeModal()
    {
        $this->showBudgetModal = false;
        $this->budgetRequested = false;
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.budget-analysis', [
            'config' => $config
        ]);
    }
}
