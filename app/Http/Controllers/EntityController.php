<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $user = auth()->user();
    // Load entities with related locality, rooms and equipment
    $entities = $user->entities()
        ->with(['locality', 'rooms.equipment'])
        ->get()
        ->map(function ($entity) {
            // Calculate installed power (sum of equipment power in all rooms)
            $installedPower = $entity->rooms
                ->flatMap(fn($room) => $room->equipment)
                ->sum('power');
            $entity->installed_power = $installedPower;
            // Power per square meter and available percentage
            $entity->power_per_m2 = $entity->square_meters ? ($installedPower / $entity->square_meters) : 0;
            $entity->available_percentage = $entity->square_meters ? max(0, 100 - $entity->power_per_m2) : 0;
            return $entity;
        });
    return view('entities.index', compact('entities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    $localities = \App\Models\Locality::with('province')->get();
    return view('entities.create', compact('localities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        $entity = \App\Models\Entity::create($request->only([
            'name',
            'address_street',
            'address_postal_code',
            'locality_id',
            'description',
            'square_meters',
            'people_count',
        ]));

        // Crear automáticamente la sala de Portátiles
        $entity->rooms()->create([
            'name' => 'Portátiles',
            'description' => 'Sala para equipos portátiles y recargables',
        ]);

        // Asociar entidad al usuario con el plan gratuito
        $user = auth()->user();
        $freePlan = \App\Models\Plan::where('name', 'Gratuito')->first();
        $user->entities()->attach($entity->id, [
            'plan_id' => $freePlan ? $freePlan->id : 1,
            'subscribed_at' => now(),
        ]);

        return redirect()->route('entities.show', $entity->id)
            ->with('success', 'Entidad hogar creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, \App\Services\Climate\ClimateDataService $climateService)
    {
        $entity = \App\Models\Entity::with('locality')->findOrFail($id);
        
        $climateProfile = null;
        if ($entity->locality) {
            $climateProfile = $climateService->getLocalityClimateProfile($entity->locality);
        }
        
        return view('entities.show', compact('entity', 'climateProfile'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $entity = \App\Models\Entity::findOrFail($id);
    $localities = \App\Models\Locality::all();
    return view('entities.edit', compact('entity', 'localities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        $entity = \App\Models\Entity::findOrFail($id);
        $entity->update($request->only([
            'name',
            'address_street',
            'address_postal_code',
            'locality_id',
            'description',
            'square_meters',
            'people_count',
        ]));

        return redirect()->route('entities.show', $entity->id)
            ->with('success', 'Entidad actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $entity = \App\Models\Entity::findOrFail($id);
        $entity->delete();
        return redirect()->route('entities.index')
            ->with('success', 'Entidad eliminada correctamente.');
    }

    /**
     * Show budget request placeholder for an entity.
     */
    public function budget(string $id, \App\Services\Climate\ClimateDataService $climateService)
    {
        $entity = \App\Models\Entity::with(['locality', 'contracts.invoices.equipmentUsages'])->findOrFail($id);
        
        $climateProfile = null;
        if ($entity->locality) {
            $climateProfile = $climateService->getLocalityClimateProfile($entity->locality);
        }
        
        // Get all invoices
        $invoices = $entity->contracts()
            ->with('invoices.equipmentUsages')
            ->get()
            ->flatMap(fn($contract) => $contract->invoices)
            ->sortByDesc('end_date');
            
        $latestInvoice = $invoices->first();
        
        // Calculate averages
        $monthlyConsumption = null;
        $averageTariff = 150; // Default fallback
        $invoiceCount = $invoices->count();
        $invoiceData = null;
        
        if ($invoiceCount > 0) {
            $totalConsumption = 0;
            $totalDays = 0;
            $totalCost = 0;
            
            foreach ($invoices as $invoice) {
                $consumption = $invoice->total_energy_consumed_kwh ?? $invoice->equipmentUsages->sum('consumption_kwh');
                $cost = $invoice->total_amount;
                
                $startDate = \Carbon\Carbon::parse($invoice->start_date);
                $endDate = \Carbon\Carbon::parse($invoice->end_date);
                $days = $startDate->diffInDays($endDate);
                
                if ($days > 0 && $consumption > 0) {
                    $totalConsumption += $consumption;
                    $totalDays += $days;
                    $totalCost += $cost;
                }
            }
            
            if ($totalDays > 0) {
                $monthlyConsumption = ($totalConsumption / $totalDays) * 30;
            }
            
            if ($totalConsumption > 0) {
                $averageTariff = $totalCost / $totalConsumption;
            }
            
            if ($latestInvoice) {
                 $latestConsumption = $latestInvoice->total_energy_consumed_kwh ?? $latestInvoice->equipmentUsages->sum('consumption_kwh');
                 $startDate = \Carbon\Carbon::parse($latestInvoice->start_date);
                 $endDate = \Carbon\Carbon::parse($latestInvoice->end_date);
                 $periodDays = $startDate->diffInDays($endDate);

                $invoiceData = [
                    'number' => $latestInvoice->invoice_number,
                    'start_date' => $latestInvoice->start_date,
                    'end_date' => $latestInvoice->end_date,
                    'total_amount' => $latestInvoice->total_amount,
                    'period_days' => $periodDays,
                    'total_consumption' => $latestConsumption,
                ];
            }
        }
        
        return view('entities.budget', compact('entity', 'monthlyConsumption', 'invoiceData', 'invoiceCount', 'averageTariff', 'invoices', 'climateProfile'));
    }
}
