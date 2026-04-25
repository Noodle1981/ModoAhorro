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
    /** @var ClimateService */
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
        // 2. Factor de Uso Real
        $equipmentType = $usage->equipment->type;
        $realUsageFactor = $equipmentType->load_factor ?? 1.0;

        // ⚡ AJUSTE POR ETIQUETA DE EFICIENCIA (A, B, C...)
        $labelCoefficient = 1.0;
        if ($usage->equipment->energy_label) {
            $coeff = \App\Models\EnergyLabelCoefficient::where('category_id', $equipmentType->category_id)
                ->where('label', $usage->equipment->energy_label)
                ->first();
            if ($coeff) {
                $labelCoefficient = $coeff->coefficient;
            }
        }
        
        $powerKw = ($usage->equipment->nominal_power_w ?? $equipmentType->default_power_watts ?? 0) / 1000;
        $powerKw *= $labelCoefficient; // Escalar potencia por eficiencia

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

            // 🌡️ AJUSTE POR INEFICIENCIA DE DISEÑO (Aire Portátil, etc)
            if ($equipmentType->thermal_efficiency_penalty > 0) {
                $consumption *= (1 + ($equipmentType->thermal_efficiency_penalty / 100));
            }

            // 🧛 CÁLCULO DE CONSUMO VAMPIRO (STANDBY)
            if ($usage->equipment->is_standby) {
                $standbyHoursPerDay = max(0, 24 - $hoursPerDay);
                $standbyPowerKw = ($usage->equipment->type->default_standby_power_w ?? 0) / 1000;
                $standbyConsumption = $standbyPowerKw * $standbyHoursPerDay * $daysInPeriod;
                $consumption += $standbyConsumption;
            }
            
            return round($consumption, 4);
        }
        
        // 5. Cálculo Proporcional a Personas (Modelo Determinista)
        if ($equipmentType->usage_unit === 'people_proportional') {
            $peopleCount = $invoice->contract->entity->people_count ?? 1;
            $daysInPeriod = $usage->use_days_in_period;
            
            if (empty($daysInPeriod)) {
                $totalDays = Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date));
                $daysInPeriod = max(1, $totalDays);
            }
            
            // Fórmula: Coeficiente Social * Personas * Días
            $consumption = $equipmentType->social_coefficient * $peopleCount * $daysInPeriod;
            
            return round($consumption, 4);
        }

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
        // ⚡ AJUSTE POR ETIQUETA DE EFICIENCIA (A, B, C...)
        $labelCoefficient = 1.0;
        if ($usage->equipment->energy_label) {
            $coeff = \App\Models\EnergyLabelCoefficient::where('category_id', $usage->equipment->type->category_id)
                ->where('label', $usage->equipment->energy_label)
                ->first();
            if ($coeff) {
                $labelCoefficient = $coeff->coefficient;
            }
        }

        $powerKw = ($usage->equipment->nominal_power_watts ?? $usage->equipment->type->default_power_watts ?? 0) / 1000;
        $powerKw *= $labelCoefficient; // Escalar potencia teórica por etiqueta
        
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
        return $this->calibrateUnifiedPeriod(collect([$invoice]));
    }

    /**
     * Calibra un grupo de facturas (Unificación) de forma integral.
     */
    public function calibrateUnifiedPeriod($invoices): array
    {
        if ($invoices->isEmpty()) {
            throw new \Exception("No hay facturas para calibrar.");
        }

        return DB::transaction(function () use ($invoices) {
            $representativeInvoice = $invoices->sortBy('installment_number')->first();
            $entity = $representativeInvoice->contract->entity;

            // 1. Calcular totales del periodo unificado
            $totalBilledKwh = $invoices->sum('total_energy_consumed_kwh');
            $startDate = $invoices->min('start_date');
            $endDate = $invoices->max('end_date');

            // 2. Cargar datos climáticos para el rango completo
            $climateLoad = $this->climateService->loadDataForDateRange($entity, $startDate, $endDate);
            $isFallback = $climateLoad['is_fallback'] ?? false;

            // 3. Obtener consumos de equipos (Usamos los de la factura representativa como base del inventario)
            // Si el usuario cargó inventarios diferentes en cada cuota (raro), tomamos los de la representativa.
            $usages = $representativeInvoice->equipmentUsages()->with(['equipment.category', 'equipment.type'])->get();
            
            $simulatedEquipments = $usages->map(function($usage) use ($representativeInvoice, $startDate, $endDate) {
                // Creamos un clon temporal de la factura para ajustar el cálculo de días de este uso
                $tempInvoice = clone $representativeInvoice;
                $tempInvoice->start_date = $startDate;
                $tempInvoice->end_date = $endDate;

                $eq = $usage->equipment;
                $eq->avg_daily_use_hours = $usage->avg_daily_use_hours; 
                $eq->_usage_id = $usage->id;
                $eq->_theo_kwh = $this->calculateEquipmentConsumption($usage, $tempInvoice);
                return $eq;
            });

            // 4. Ejecutar el Motor de Energía con los totales unificados
            $this->energyEngine->setFallbackMode($isFallback);
            
            // Creamos un objeto proxy para el motor
            $proxyInvoice = clone $representativeInvoice;
            $proxyInvoice->total_energy_consumed_kwh = $totalBilledKwh;
            $proxyInvoice->start_date = $startDate;
            $proxyInvoice->end_date = $endDate;

            $engineResult = $this->energyEngine->processInvoice($proxyInvoice, $simulatedEquipments);
            
            // 5. Persistir resultados en los EquipmentUsage de la factura representativa
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

            // 6. Marcar TODAS las facturas del grupo como calibradas
            $recommendedTotalKwh = $engineResult['recommended_total_kwh'] ?? 0;
            
            foreach ($invoices as $inv) {
                $inv->update([
                    'recommended_kwh' => $recommendedTotalKwh, // El recomendado es el del periodo total
                    'calibrated_at'   => now()
                ]);
            }

            return [
                'usages' => $usages,
                'summary' => [
                    'tank_0' => $engineResult['tank_0_certainty'] ?? 0,
                    'tank_1' => $engineResult['tank_1_base'] ?? 0,
                    'tank_2' => $engineResult['tank_2_climate'] ?? 0,
                    'tank_3' => $engineResult['tank_3_elasticity'] ?? 0,
                    'theoretical_total' => $engineResult['theoretical_total'] ?? 0,
                    'calibrated_total' => $engineResult['calibrated_total'] ?? 0,
                    'unassigned' => $engineResult['unassigned_remainder'] ?? 0,
                    'logs' => $engineResult['logs'] ?? []
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
