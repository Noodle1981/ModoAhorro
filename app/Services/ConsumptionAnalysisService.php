<?php

namespace App\Services;

use App\Models\EquipmentUsage;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ConsumptionAnalysisService
{
    protected $usageSuggestionService;
    protected $energyEngine;
    protected $climateService;
    protected $maintenanceService;

    public function __construct(
        \App\Services\Climate\UsageSuggestionService $usageSuggestionService,
        \App\Services\EnergyEngineService $energyEngine,
        \App\Services\ClimateService $climateService,
        \App\Services\MaintenanceService $maintenanceService
    ) {
        $this->usageSuggestionService = $usageSuggestionService;
        $this->energyEngine = $energyEngine;
        $this->climateService = $climateService;
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Calcula el consumo total de un equipo en un periodo
     * 
     * Formula: Energía (kWh) = P × h × d × FC
     * 
     * @param EquipmentUsage $usage
     * @param Invoice $invoice
     * @return float Consumo en kWh
     */
    public function calculateEquipmentConsumption(EquipmentUsage $usage, Invoice $invoice): float
    {
        // 1. Potencia Nominal (Convertida a kW)
        $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
        
        // 2. Factor de Uso Real
        $equipmentType = $usage->equipment->type;
        $realUsageFactor = $equipmentType->load_factor ?? 1.0;

        // ❄️ CÁLCULO ESPECÍFICO PARA HELADERAS (Modelo Avanzado)
        if ($this->isFridge($usage)) {
            return $this->calculateFridgeConsumption($usage, $invoice);
        }
        
        // 3. Cálculo para Frecuencia Diaria/Semanal
        if ($usage->avg_daily_use_hours > 0 || in_array($usage->usage_frequency, ['diario', 'diariamente', 'semanal']) || empty($usage->usage_frequency)) {
            $hoursPerDay = $usage->avg_daily_use_hours ?? 0;
            $daysInPeriod = $usage->use_days_in_period;
            
            // Fallback: Si no hay días guardados (null/0), calcular según función centralizada
            if (empty($daysInPeriod)) {
                $totalDays = Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date));
                $totalDays = max(1, $totalDays);
                $daysInPeriod = $this->getDaysByFrequency($usage->usage_frequency, $totalDays);
            }
            
            // 🌡️ AJUSTE CLIMÁTICO: Para equipos de climatización, ajustar días según clima
            $effectiveDays = $this->getEffectiveDaysWithClimate($usage, $invoice, $daysInPeriod);
            
            // Fórmula: Potencia (kW) * Horas * Días Efectivos * Factor de Uso Real
            $consumption = $powerKw * $hoursPerDay * $effectiveDays * $realUsageFactor;

            // 🌡️ AJUSTE CLIMÁTICO ESPECÍFICO: Termotanques
            if ($this->isWaterHeater($usage)) {
                $factor = $this->getWaterHeaterClimateFactor($usage, $invoice);
                $consumption *= $factor;
            }

            // 🛠️ AJUSTE POR MANTENIMIENTO
            $maintenancePenalty = $this->maintenanceService->getPenaltyFactor($usage->equipment);
            $consumption *= $maintenancePenalty;

            // 🧛 CÁLCULO DE CONSUMO VAMPIRO (STANDBY)
            if ($usage->equipment->is_standby) {
                $standbyHoursPerDay = max(0, 24 - $hoursPerDay);
                $standbyPowerKw = ($usage->equipment->type->default_standby_power_w ?? 0) / 1000;
                $standbyConsumption = $standbyPowerKw * $standbyHoursPerDay * $daysInPeriod;
                $consumption += $standbyConsumption;
            }
            
            return round($consumption, 4);
        }
        
        // 4. Cálculo para uso Puntual
        $usageCount = $usage->usage_count ?? 0;
        $avgUseDuration = $usage->avg_use_duration ?? 0;
        $consumption = $powerKw * $avgUseDuration * $usageCount * $realUsageFactor;
        
        $maintenancePenalty = $this->maintenanceService->getPenaltyFactor($usage->equipment);
        $consumption *= $maintenancePenalty;

        return round($consumption, 4);
    }
    
    /**
     * Obtiene los días efectivos de uso considerando datos climáticos
     */
    private function getEffectiveDaysWithClimate(EquipmentUsage $usage, Invoice $invoice, int $totalDays): int
    {
        $category = $usage->equipment->category->name ?? '';
        
        if ($category !== 'Climatización') {
            return $totalDays;
        }

        $climateDays = $this->energyEngine->getClimateDays(); 
        $hasClimateData = isset($climateDays['cooling_days']) || isset($climateDays['heating_days']);
        
        if (!$hasClimateData) {
             return $totalDays; 
        }

        $isCooling = ($category === 'Climatización');
        $detectedDays = $isCooling ? ($climateDays['cooling_days'] ?? 0) : ($climateDays['heating_days'] ?? 0);

        return min($detectedDays, $totalDays);
    }

    private function isWaterHeater(EquipmentUsage $usage): bool
    {
        $name = strtolower($usage->equipment->name);
        $type = strtolower($usage->equipment->type->name ?? '');
        $keywords = ['termotanque', 'calefón', 'calefon', 'bomba de agua'];
        
        foreach ($keywords as $keyword) {
            if (str_contains($name, $keyword) || str_contains($type, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function getWaterHeaterClimateFactor(EquipmentUsage $usage, Invoice $invoice): float
    {
        try {
            $locality = $invoice->contract->entity->locality;
            if (!$locality || !$locality->latitude || !$locality->longitude) {
                return 1.0;
            }

            $this->climateService->loadDataForInvoice($invoice);
            
            $stats = $this->climateService->getClimateStats(
                $locality->latitude,
                $locality->longitude,
                Carbon::parse($invoice->start_date),
                Carbon::parse($invoice->end_date)
            );

            $avgTemp = $stats['avg_temp_avg'] ?? 20;

            if ($avgTemp < 15) {
                Log::info("🌡️ TERMOTANQUE (Invierno): {$usage->equipment->name} - Factor x1.25 (Temp: {$avgTemp}°C)");
                return 1.25;
            }

            if ($avgTemp > 25) {
                Log::info("🌡️ TERMOTANQUE (Verano): {$usage->equipment->name} - Factor x0.85 (Temp: {$avgTemp}°C)");
                return 0.85;
            }

        } catch (\Exception $e) {
            Log::warning('Error calculando factor termotanque: ' . $e->getMessage());
        }

        return 1.0;
    }

    private function isFridge(EquipmentUsage $usage): bool
    {
        $name = strtolower($usage->equipment->name);
        $type = strtolower($usage->equipment->type->name ?? '');
        
        if (str_contains($name, 'heladera') || str_contains($name, 'freezer') || 
            str_contains($type, 'heladera') || str_contains($type, 'refrigerador')) {
            return true;
        }
        return false;
    }

    private function calculateFridgeConsumption(EquipmentUsage $usage, Invoice $invoice): float
    {
        $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
        
        $daysInPeriod = $usage->use_days_in_period;
        if (empty($daysInPeriod)) {
            $totalDays = Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date));
            $daysInPeriod = max(1, $totalDays);
        }

        $baseLoadFactor = 0.25; 
        $peopleCount = $invoice->contract->entity->people_count ?? 1;
        $peopleCount = max(1, min($peopleCount, 15));
        
        $activityFactorPerPerson = 0.015;
        $totalLoadFactor = $baseLoadFactor + ($peopleCount * $activityFactorPerPerson);
        $consumption = $powerKw * 24 * $daysInPeriod * $totalLoadFactor;

        try {
            $locality = $invoice->contract->entity->locality;
            if ($locality && $locality->latitude) {
                $this->climateService->loadDataForInvoice($invoice);
                
                $stats = $this->climateService->getClimateStats(
                    $locality->latitude,
                    $locality->longitude,
                    Carbon::parse($invoice->start_date),
                    Carbon::parse($invoice->end_date)
                );
                
                $avgTemp = $stats['avg_temp_avg'] ?? 20;
                $climateCorrection = 1.0;
                
                if ($avgTemp > 20) {
                    $climateCorrection += (($avgTemp - 20) * 0.02);
                } elseif ($avgTemp < 20) {
                    $climateCorrection -= ((20 - $avgTemp) * 0.01);
                }
                
                $climateCorrection = max(0.8, min($climateCorrection, 1.5));
                $consumption *= $climateCorrection;
                
                Log::info("❄️ HELADERA: {$usage->equipment->name} (Temp: $avgTemp °C) -> Factor: $totalLoadFactor, Clima: $climateCorrection");
            }
        } catch (\Exception $e) {
            Log::warning("Error ajuste climático heladera: " . $e->getMessage());
        }

        return round($consumption, 4);
    }

    public function calculateInvoiceConsumption(Invoice $invoice): array
    {
        $result = [];
        foreach ($invoice->equipmentUsages()->with('equipment')->get() as $usage) {
            $result[$usage->equipment_id] = $this->calculateEquipmentConsumption($usage, $invoice);
        }
        return $result;
    }

    public function calibrateInvoiceConsumption(Invoice $invoice): array
    {
        return DB::transaction(function () use ($invoice) {
            $climateLoad = $this->climateService->loadDataForInvoice($invoice);
            $isFallback = $climateLoad['is_fallback'] ?? false;

            $usages = $invoice->equipmentUsages()->with(['equipment.category', 'equipment.type'])->get();
            
            $simulatedEquipments = $usages->map(function($usage) use ($invoice) {
                $eq = $usage->equipment;
                $eq->avg_daily_use_hours = $usage->avg_daily_use_hours; 
                $eq->_usage_id = $usage->id;
                $eq->_theo_kwh = $this->calculateEquipmentConsumption($usage, $invoice);
                return $eq;
            });

            $this->energyEngine->setFallbackMode($isFallback);
            $engineResult = $this->energyEngine->processInvoice($invoice, $simulatedEquipments);
            
            $logs = $engineResult['logs'] ?? [];
            
            EquipmentUsage::withoutEvents(function() use ($simulatedEquipments, $usages) {
                foreach ($simulatedEquipments as $processedEq) {
                    $usage = $usages->firstWhere('id', $processedEq->_usage_id);
                    if ($usage) {
                        $usage->kwh_reconciled = $processedEq->calculated_consumption_kwh;
                        $usage->tank_assignment = $processedEq->tank_assignment;
                        $usage->audit_logs = $processedEq->audit_logs ?? [];
                        $usage->save();
                    }
                }
            });

            $invoice->update([
                'recommended_kwh' => $engineResult['recommended_total_kwh'] ?? 0,
                'calibrated_at'   => now()
            ]);

            return [
                'usages' => $usages,
                'summary' => [
                    'tank_1' => $engineResult['tank_1_base'] ?? 0,
                    'tank_2' => $engineResult['tank_2_climate'] ?? 0,
                    'tank_3' => $engineResult['tank_3_elasticity'] ?? 0,
                    'theoretical_total' => $engineResult['theoretical_total'] ?? 0,
                    'calibrated_total' => $engineResult['calibrated_total'] ?? 0,
                    'unassigned' => $engineResult['unassigned_remainder'] ?? 0,
                    'logs' => $logs
                ],
                'climate_data' => $engineResult['climate_data'] ?? []
            ];
        });
    }

    public function analyzeConsumptionWithClimate(Invoice $invoice): array
    {
        $this->climateService->loadDataForInvoice($invoice);
        
        $locality = $invoice->contract->entity->locality;
        if (!$locality || !$locality->latitude || !$locality->longitude) {
            return [
                'success' => false,
                'message' => 'Localidad no configurada o sin coordenadas',
                'details' => []
            ];
        }

        $analysis = [];
        $usages = $invoice->equipmentUsages()->with(['equipment.category', 'equipment.type'])->get();

        foreach ($usages as $usage) {
            $declaredKwh = $this->calculateEquipmentConsumption($usage, $invoice);
            
            $suggestion = $this->usageSuggestionService->suggestClimateUsage(
                $usage->equipment,
                $invoice,
                $locality->latitude,
                $locality->longitude
            );

            $item = [
                'equipment_name' => $usage->equipment->name,
                'category' => ($usage->equipment->category && $usage->equipment->category->name) ? $usage->equipment->category->name : 'Otro',
                'declared_kwh' => $declaredKwh,
                'declared_hours' => $usage->avg_daily_use_hours,
                'suggestion' => null,
                'discrepancy_kwh' => 0,
                'is_efficient' => true
            ];

            if ($suggestion) {
                $suggestedUsage = $usage->replicate();
                $suggestedUsage->avg_daily_use_hours = $suggestion['suggested_hours_per_day'];
                $suggestedKwh = $this->calculateEquipmentConsumption($suggestedUsage, $invoice);

                $item['suggestion'] = [
                    'hours' => $suggestion['suggested_hours_per_day'],
                    'kwh' => $suggestedKwh,
                    'reason' => $suggestion['explanation'],
                    'confidence' => $suggestion['confidence']
                ];

                $item['discrepancy_kwh'] = round($declaredKwh - $suggestedKwh, 2);
                
                if ($declaredKwh > ($suggestedKwh * 1.1)) {
                    $item['is_efficient'] = false;
                }
            }

            $analysis[] = $item;
        }

        return [
            'success' => true,
            'locality' => $locality->name,
            'details' => $analysis
        ];
    }

    private function getDaysByFrequency($frequency, $totalDays)
    {
        $factor = match($frequency) {
            'casi_frecuentemente' => 0.85,
            'frecuentemente' => 0.60,
            'ocasionalmente' => 0.30,
            'raramente' => 0.10,
            'nunca' => 0.0,
            default => 0.60
        };
        
        return floor($totalDays * $factor);
    }
}
