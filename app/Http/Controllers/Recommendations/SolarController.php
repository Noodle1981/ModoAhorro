<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\SolarWaterHeaterService;
use App\Services\Solar\SolarPowerService;
use App\Services\Climate\ClimateDataService;
use Illuminate\Http\Request;

class SolarController extends Controller
{
    public function panels(Request $request, string $id, SolarPowerService $service, ClimateDataService $climateService)
    {
        $entity = Entity::findOrFail($id);
        $config = config("entity_types.{$entity->type}", []);
        
        $result = null;
        $climateProfile = null;

        if ($entity->locality) {
            $climateProfile = $climateService->getLocalityClimateProfile($entity->locality);
        }

        // Get invoices for consumption data
        $invoices = \App\Models\Invoice::whereHas('contract', function($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })->where('end_date', '>=', now()->subYear())->get();

        $avgConsumption = $invoices->avg('total_energy_consumed_kwh') ?? 0;
        $maxConsumption = $invoices->max('total_energy_consumed_kwh') ?? 0;

        if ($request->isMethod('post')) {
            $request->validate([
                'available_area' => 'required|numeric|min:1',
            ]);

            $availableArea = $request->input('available_area');
            $result = $service->calculateSolarCoverage($availableArea, $maxConsumption, $avgConsumption);
            
            // Add financial calculation
            $avgTariff = 150; // TODO: Get from invoices
            $result['annual_savings'] = $result['monthly_generation_kwh'] * 12 * $avgTariff;
            
            // Estimation: 1000 USD/kWp installed -> ~1,000,000 ARS
            $pricePerKwp = 1200000; 
            $investment = $result['system_size_kwp'] * $pricePerKwp;
            $result['investment'] = $investment;
            $result['roi_years'] = $result['annual_savings'] > 0 ? $investment / $result['annual_savings'] : 0;
        }

        return view('entities.solar_panels', compact('entity', 'result', 'config', 'climateProfile', 'avgConsumption'));
    }

    public function waterHeater(Request $request, string $id, SolarWaterHeaterService $service)
    {
        $entity = Entity::with(['rooms.equipment.type'])->findOrFail($id);
        $config = config("entity_types.{$entity->type}", []);
        
        // Detect Electric Water Heater
        $hasElectricWaterHeater = $entity->rooms->flatMap->equipment->contains(function ($eq) {
            return \Illuminate\Support\Str::contains(strtolower($eq->type->name ?? ''), ['termotanque', 'calefon']) 
                && \Illuminate\Support\Str::contains(strtolower($eq->type->category ?? ''), 'electr');
        });

        $result = null;

        if ($hasElectricWaterHeater || $request->isMethod('post')) {
            // Calculate using service
            // For manual gas input, we'll need to pass that to the service or handle it here
            // The existing service calculates based on people count, but returns savings for all types
            // We just need to display the relevant one or the hypothetical one
            
            $peopleCount = $request->input('people_count');
            $result = $service->calculateWaterHeaterData($entity, $peopleCount);
        }

        return view('entities.solar_water_heater', compact('entity', 'result', 'config', 'hasElectricWaterHeater'));
    }
}
