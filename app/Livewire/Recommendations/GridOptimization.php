<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Entity;
use App\Services\GridOptimizerService;

#[Layout('layouts.app')]
class GridOptimization extends Component
{
    public Entity $entity;

    public function mount(Entity $entity)
    {
        $this->entity = $entity;
    }

    #[Computed]
    public function results()
    {
        $optimizer = app(GridOptimizerService::class);
        
        // Find tariff scheme from contract if possible, or use a default one
        $tariffScheme = $this->entity->contracts()->first()?->tariffScheme 
                        ?? \App\Models\TariffScheme::first();

        // Fetch usages from all invoices of the entity
         $usages = \App\Models\EquipmentUsage::whereHas('invoice', function ($q) {
            $q->whereIn('contract_id', $this->entity->contracts()->pluck('id'));
        })->with('equipment.type')->get();

        $schedule = $tariffScheme 
            ? $optimizer->calculateShiftSavings($usages, $tariffScheme) 
            : [];
        
        $totalSavings = collect($schedule)->sum('potential_savings');
        
        return [
            'schedule' => $schedule,
            'totalSavings' => $totalSavings,
            'tariffScheme' => $tariffScheme
        ];
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.grid-optimization', [
            'config' => $config
        ]);
    }
}
