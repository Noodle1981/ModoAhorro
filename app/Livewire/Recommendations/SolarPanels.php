<?php

namespace App\Livewire\Recommendations;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Entity;
use App\Models\Invoice;
use App\Services\Solar\SolarPowerService;
use App\Services\ClimateService;

#[Layout('layouts.app')]
class SolarPanels extends Component
{
    public Entity $entity;
    public $availableArea;
    public $result = null;
    public $avgConsumption = 0;
    public $maxConsumption = 0;
    public $climateProfile = null;

    public function mount(Entity $entity, ClimateService $climateService)
    {
        $this->entity = $entity;
        
        // Cargar superficie disponible por defecto (40%)
        $this->availableArea = round($this->entity->square_meters * 0.4);

        if ($this->entity->locality) {
            $this->climateProfile = $climateService->getLocalityClimateProfile($this->entity->locality);
        }

        // Obtener consumos históricos
        $invoices = Invoice::whereHas('contract', function($q) {
            $q->where('entity_id', $this->entity->id);
        })->where('end_date', '>=', now()->subYear())->get();

        $this->avgConsumption = $invoices->avg('total_energy_consumed_kwh') ?? 0;
        $this->maxConsumption = $invoices->max('total_energy_consumed_kwh') ?? 0;

        // Cálculo inicial
        $this->calculate(new SolarPowerService());
    }

    public function updatedAvailableArea()
    {
        $this->calculate(new SolarPowerService());
    }

    public function calculate(SolarPowerService $service)
    {
        if ($this->availableArea < 1) return;

        $this->result = $service->calculateSolarCoverage(
            $this->availableArea, 
            $this->maxConsumption, 
            $this->avgConsumption
        );
        
        // Agregar cálculos económicos (mismo que en controller)
        $avgTariff = 150; 
        $this->result['annual_savings'] = $this->result['monthly_generation_kwh'] * 12 * $avgTariff;
        
        $pricePerKwp = 1200000; 
        $investment = $this->result['system_size_kwp'] * $pricePerKwp;
        $this->result['investment'] = $investment;
        $this->result['roi_years'] = $this->result['annual_savings'] > 0 ? $investment / $this->result['annual_savings'] : 0;
    }

    public function render()
    {
        $config = config("entity_types.{$this->entity->type}", []);
        return view('livewire.recommendations.solar-panels', [
            'config' => $config
        ]);
    }
}
