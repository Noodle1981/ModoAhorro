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
    public function budget(string $id, \App\Services\Climate\ClimateDataService $climateService, \App\Services\Solar\SolarPowerService $solarService)
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
        $maxMonthlyConsumption = 0; // New variable for solar calculation
        $averageTariff = 150; // Default fallback
        $invoiceCount = $invoices->count();
        $invoiceData = null;
        
        if ($invoiceCount > 0) {
            $totalConsumption = 0;
            $totalDays = 0;
            $totalCost = 0;
            
            // Variables for representative average (Solar/Projections)
            $totalRepConsumption = 0;
            $totalRepDays = 0;
            
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
                    
                    // Only include in representative average if flagged true (default)
                    if ($invoice->is_representative) {
                        $totalRepConsumption += $consumption;
                        $totalRepDays += $days;
                        
                        // Normalize to 30 days for max comparison (only representative ones?)
                        // User didn't specify, but usually max consumption for sizing should probably exclude anomalies if they are low.
                        // If anomaly is high usage, maybe keep it? But usually vacation is low usage.
                        // Let's stick to representative for the "Average" which is the key metric for solar savings.
                        // For Max, we might still want the absolute max to ensure system covers peaks, 
                        // unless the peak was an anomaly (e.g. broken meter).
                        // Let's use representative for Max too to be safe/consistent.
                        $monthlyNormalized = ($consumption / $days) * 30;
                        if ($monthlyNormalized > $maxMonthlyConsumption) {
                            $maxMonthlyConsumption = $monthlyNormalized;
                        }
                    }
                }
            }
            
            // Calculate average based on representative data
            if ($totalRepDays > 0) {
                $monthlyConsumption = ($totalRepConsumption / $totalRepDays) * 30;
            } elseif ($totalDays > 0) {
                 // Fallback if all are anomalous (unlikely but possible), use total
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
        
        // Solar Coverage Calculation
        $solarData = null;
        $estimatedMonthlySavings = 0;
        $estimatedAnnualSavings = 0;
        
        if ($monthlyConsumption) {
            // Use entity square meters as available area (assuming roof is a percentage or user will adjust later)
            // For now, let's assume 50% of square meters is available roof space if not specified
            $availableArea = $entity->square_meters * 0.5; 
            
            $solarData = $solarService->calculateSolarCoverage(
                $availableArea, 
                $maxMonthlyConsumption, 
                $monthlyConsumption
            );

            // Robust Savings Calculation (Simulation)
            // We simulate the solar generation against each historical invoice to get a realistic savings estimate
            // considering the "Net Billing" limit (cannot save more than consumption in a period unless sold back, 
            // but usually sold at lower rate. Here we assume simple savings: min(Generation, Consumption))
            
            $totalSimulatedSavings = 0;
            $totalDaysAnalyzed = 0;
            $dailyGeneration = $solarData['monthly_generation_kwh'] / 30; // Average daily generation
            
            foreach ($invoices as $invoice) {
                $consumption = $invoice->total_energy_consumed_kwh ?? $invoice->equipmentUsages->sum('consumption_kwh');
                $startDate = \Carbon\Carbon::parse($invoice->start_date);
                $endDate = \Carbon\Carbon::parse($invoice->end_date);
                $days = $startDate->diffInDays($endDate);
                
                if ($days > 0) {
                    $dailyConsumption = $consumption / $days;
                    
                    // Daily saving is limited by daily consumption (assuming no net metering profit for excess)
                    // This fixes the "Winter" error where savings > bill
                    $dailySavingKwh = min($dailyGeneration, $dailyConsumption);
                    
                    $periodSavingKwh = $dailySavingKwh * $days;
                    $periodSavingMoney = $periodSavingKwh * $averageTariff;
                    
                    $totalSimulatedSavings += $periodSavingMoney;
                    $totalDaysAnalyzed += $days;
                }
            }
            
            if ($totalDaysAnalyzed > 0) {
                $annualizedSavings = ($totalSimulatedSavings / $totalDaysAnalyzed) * 365;
                $estimatedAnnualSavings = $annualizedSavings;
                $estimatedMonthlySavings = $annualizedSavings / 12;
            } else {
                // Fallback if no days analyzed (shouldn't happen if monthlyConsumption exists)
                $estimatedMonthlySavings = $solarData['monthly_generation_kwh'] * $averageTariff;
                $estimatedAnnualSavings = $estimatedMonthlySavings * 12;
            }
        }
        
        return view('entities.budget', compact('entity', 'monthlyConsumption', 'invoiceData', 'invoiceCount', 'averageTariff', 'invoices', 'climateProfile', 'solarData', 'estimatedMonthlySavings', 'estimatedAnnualSavings'));
    }

    public function solarWaterHeater(string $id, \App\Services\Climate\ClimateDataService $climateService, \App\Services\Solar\SolarWaterService $waterService)
    {
        $entity = \App\Models\Entity::with(['locality', 'contracts.invoices'])->findOrFail($id);
        
        $climateProfile = null;
        if ($entity->locality) {
            $climateProfile = $climateService->getLocalityClimateProfile($entity->locality);
        }
        
        // Calculate Average Tariff (copied from budget method logic)
        // We need this to estimate electric savings
        $invoices = $entity->contracts()
            ->with('invoices')
            ->get()
            ->flatMap(fn($contract) => $contract->invoices);
            
        $averageTariff = 150; // Default fallback
        
        if ($invoices->count() > 0) {
            $totalConsumption = 0;
            $totalCost = 0;
            
            foreach ($invoices as $invoice) {
                $consumption = $invoice->total_energy_consumed_kwh ?? 0;
                $cost = $invoice->total_amount;
                
                if ($consumption > 0) {
                    $totalConsumption += $consumption;
                    $totalCost += $cost;
                }
            }
            
            if ($totalConsumption > 0) {
                $averageTariff = $totalCost / $totalConsumption;
            }
        }
        
        $waterHeaterData = $waterService->calculateWaterHeater($entity->people_count, $climateProfile, $averageTariff);
        
        return view('entities.solar_water_heater', compact('entity', 'climateProfile', 'waterHeaterData', 'averageTariff'));
    }

    /**
     * Show Standby Analysis for an entity.
     */
    public function standbyAnalysis(string $id)
    {
        $entity = \App\Models\Entity::with(['rooms.equipment.type'])->findOrFail($id);
        // Get all equipment that has standby power capability
        // EXCLUDING Infrastructure (Modems, Routers) as per user feedback
        $equipmentList = $entity->rooms
            ->flatMap(fn($room) => $room->equipment)
            ->filter(fn($eq) => ($eq->type->default_standby_power_w ?? 0) > 0)
            ->filter(fn($eq) => !str_contains(strtolower($eq->name), 'modem') && !str_contains(strtolower($eq->name), 'router') && !str_contains(strtolower($eq->type->name ?? ''), 'modem') && !str_contains(strtolower($eq->type->name ?? ''), 'router'));
            
        // Calculate totals
        $totalStandbyKwh = 0;
        $totalPotentialSavingsKwh = 0;
        $totalRealizedSavingsKwh = 0;
        
        foreach ($equipmentList as $eq) {
            $standbyPowerKw = ($eq->type->default_standby_power_w ?? 0) / 1000;
            $standbyHours = max(0, 24 - ($eq->avg_daily_use_hours ?? 0));
            $monthlyKwh = $standbyPowerKw * $standbyHours * 30;
            
            if ($eq->is_standby) {
                $totalStandbyKwh += $monthlyKwh;
                $totalPotentialSavingsKwh += $monthlyKwh;
            } else {
                $totalRealizedSavingsKwh += $monthlyKwh;
            }
        }
        
        // Estimate cost (using a default or calculated tariff if available, here using 150 as fallback/standard)
        $averageTariff = 150; 
        $totalStandbyCost = $totalStandbyKwh * $averageTariff;
        $totalPotentialSavings = $totalPotentialSavingsKwh * $averageTariff;
        $totalRealizedSavings = $totalRealizedSavingsKwh * $averageTariff;
        
        return view('entities.standby_analysis', compact('entity', 'equipmentList', 'totalStandbyKwh', 'totalStandbyCost', 'totalPotentialSavings', 'totalRealizedSavings'));
    }

    /**
     * Toggle standby status for an equipment.
     */
    public function toggleStandby(Request $request, string $entityId, string $equipmentId)
    {
        $equipment = \App\Models\Equipment::findOrFail($equipmentId);
        
        // Verify equipment belongs to entity (security check)
        // This is a bit loose, ideally check via room->entity_id
        if ($equipment->room->entity_id != $entityId) {
            abort(403, 'Unauthorized action.');
        }
        
        $equipment->is_standby = !$equipment->is_standby;
        $equipment->save();
        
        return redirect()->route('entities.standby_analysis', $entityId)
            ->with('success', 'Estado de Stand By actualizado.');
    }

    /**
     * Show Grid Optimization Analysis.
     */
    public function gridOptimization(string $id, \App\Services\GridOptimizerService $optimizer)
    {
        $entity = \App\Models\Entity::with(['rooms.equipment.type'])->findOrFail($id);
        
        $tariffScheme = \App\Models\TariffScheme::with('bands')->first(); // Default to the first one (seeded)
        
        if (!$tariffScheme) {
            // Fallback or error
            // For now, let's just return empty or redirect
             return redirect()->back()->with('error', 'No hay esquemas tarifarios disponibles. Ejecute el seeder.');
        }

        // Get active equipment usages simulation
        $equipments = $entity->rooms->flatMap(fn($r) => $r->equipment);
        
        $usages = $equipments->map(function($eq) {
            return (object) [
                'equipment' => $eq,
                'kwh_reconciled' => ($eq->type->default_power_watts ?? 0) * ($eq->type->default_avg_daily_use_hours ?? 0) * 30 / 1000,
                'daily_kwh' => ($eq->type->default_power_watts ?? 0) * ($eq->type->default_avg_daily_use_hours ?? 0) / 1000
            ];
        });

        $opportunities = $optimizer->calculateShiftSavings($usages, $tariffScheme);

        return view('grid.optimization', compact('entity', 'opportunities', 'tariffScheme'));
    }
}
