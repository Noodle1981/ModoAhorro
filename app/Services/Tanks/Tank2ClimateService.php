<?php

namespace App\Services\Tanks;

use App\Models\Equipment;
use App\Services\ClimateService;
use App\Services\ThermalProfileService;
use Illuminate\Support\Collection;

class Tank2ClimateService
{
    protected $climateService;
    protected $thermalService;

    public function __construct(ClimateService $climateService, ThermalProfileService $thermalService)
    {
        $this->climateService = $climateService;
        $this->thermalService = $thermalService;
    }

    /**
     * Procesa los equipos sensibles al clima.
     */
    public function process(Collection $equipments, float &$remainingKwh, array $opContext, $invoice, bool $isFallbackMode): array
    {
        $tankConsumption = 0;
        $logs = [];
        $entity = $invoice->contract->entity;

        $targetEquipments = $equipments->filter(function ($eq) {
            // Tank Climático: Entra cualquier equipo de categoría Climatización.
            // El usuario NO necesita marcar Patrón Fijo: la categoría es la llave.
            // Incluye ventiladores (SEASONAL_HABIT): el motor climático los limita
            // a los días con condición estacional activa.
            return $eq->tank_assignment === null
                && $eq->type?->is_thermal_sensitive === true;
        });

        if ($targetEquipments->isEmpty()) {
            return ['consumption' => 0, 'logs' => [], 'climate_data' => []];
        }

        $climateStats = $this->climateService->getDegreeDaysForLocality(
            $entity->locality, 
            $invoice->start_date, 
            $invoice->end_date
        );
        
        $thermalMultiplier = $this->thermalService->calculateMultiplier($entity);

        foreach ($targetEquipments as $eq) {
            $equipmentType = $eq->type;
            $room = $eq->room;
            
            if (isset($eq->_theo_kwh)) {
                $periodKwh = $eq->_theo_kwh;
                $eq->audit_logs = [number_format($periodKwh, 1) . " kWh (Ajuste Térmico Manual)"];
            } else {
                $name = strtolower($equipmentType->name);
                $isCooling = str_contains($name, 'aire') || str_contains($name, 'ventilador') || str_contains($name, 'split');
                $degreeDays = $isCooling ? ($climateStats['cooling_days'] ?? 0) : ($climateStats['heating_days'] ?? 0);
                
                if ($degreeDays <= 0) {
                     $periodKwh = 0;
                     $eq->audit_logs = ["0 kWh (Sin Grados-Día activos)"];
                } else {
                    // --- FÍSICA DE DESPERDICIO ESTRUCTURAL (NotebookLM) ---
                    $avgDegreeDays = $degreeDays / $opContext['total_days'];
                    
                    // 1. Factor de Carga por Clima
                    $climateMainFactor = min(1.0, ($avgDegreeDays / 5.0)); 
                    
                    // --- JERARQUÍA DE LA VERDAD (NotebookLM) ---
                    $finalSquareMeters = 12.0; // Default fallback
                    if ($room && $room->square_meters > 0) {
                        $finalSquareMeters = $room->square_meters;
                    } elseif ($entity->square_meters > 0) {
                        // Reparto Proporcional: Total casa / Habitaciones con Clima
                        $climateRoomCount = $equipments->filter(fn($e) => in_array($e->type->consumption_logic, ['CLIMATE_DEPENDENT', 'CLIMATE_INEFFICIENT']))->pluck('room_id')->unique()->count();
                        $finalSquareMeters = $entity->square_meters / max(1, $climateRoomCount);
                    }

                    // --- DETECCIÓN DE INSUFICIENCIA TÉRMICA ---
                    // Un equipo estándar necesita ~100W/m2.
                    $requiredPower = $finalSquareMeters * 100;
                    $installedPower = $equipmentType->default_power_watts;
                    $sufficiencyRatio = $installedPower / max(1, $requiredPower);
                    
                    $insufficiencyPenalty = 1.0;
                    if ($sufficiencyRatio < 0.8) {
                        // Equipo chico -> No corta nunca -> Sube carga
                        $insufficiencyPenalty = 1.25;
                    }

                    // 2. Factor de Desperdicio (Penalty + Aislación + Insuficiencia)
                    $penaltyFactor = 1 + ($equipmentType->thermal_efficiency_penalty / 100);
                    $finalLoadFactor = min(1.0, $equipmentType->load_factor * $climateMainFactor * $thermalMultiplier * $penaltyFactor * $insufficiencyPenalty);
                    
                    // 3. Ajuste por tamaño de habitación
                    $roomSizeFactor = max(0.8, min(2.0, $finalSquareMeters / 12));

                    $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $opContext['daily_hours'];
                    $activeDays = ($eq->use_time_hours == 24) ? $opContext['total_days'] : $opContext['work_days'];

                    // ⚡ AJUSTE POR ETIQUETA Y TECNOLOGÍA
                    $labelCoeff = 1.0;
                    if ($eq->energy_label) {
                        $labelCoeff = \App\Models\EnergyLabelCoefficient::where('label', $eq->energy_label)
                            ->where(function($q) use ($equipmentType) {
                                $q->where('equipment_type_id', $equipmentType->id)
                                  ->orWhere(function($sq) use ($equipmentType) {
                                      $sq->whereNull('equipment_type_id')
                                         ->where('category_id', $equipmentType->category_id);
                                  });
                            })
                            ->orderByRaw('equipment_type_id IS NULL ASC')
                            ->first()?->coefficient ?? 1.0;
                    }
                    $inverterCoeff = $eq->is_inverter ? 0.70 : 1.0;

                    $effectivePower = ($eq->nominal_power_w ?? $equipmentType->default_power_watts) * $labelCoeff * $inverterCoeff;

                    $dailyKwh = ($effectivePower * $hours * $finalLoadFactor * $roomSizeFactor) / 1000;
                    $periodKwh = $dailyKwh * $activeDays;

                    // --- SPLIT 70/30 para BASE_THERMAL_LOSS (Termotanques) ---
                    if ($equipmentType->consumption_logic === 'BASE_THERMAL_LOSS') {
                        $periodKwh = $periodKwh * 0.30;
                        $currentLogs = $eq->audit_logs ?? [];
                        $currentLogs[] = "Asignado 30% como Sensibilidad Climática (" . number_format($periodKwh, 1) . " kWh)";
                        $eq->audit_logs = $currentLogs;
                    } else {
                        $wasteKwh = $periodKwh * ($equipmentType->thermal_efficiency_penalty / 100);
                        $eq->audit_logs = [
                            number_format($periodKwh, 1) . " kWh (Load: " . number_format($finalLoadFactor, 2) . ")",
                            "Desperdicio estructural: " . number_format($wasteKwh, 1) . " kWh"
                        ];
                    }
                }
            }

            $eq->calculated_consumption_kwh = ($eq->calculated_consumption_kwh ?? 0) + $periodKwh;
            $eq->tank_assignment = 3;
            $tankConsumption += $periodKwh;
            
            if ($isFallbackMode) {
                $currentLogs = $eq->audit_logs ?? [];
                $currentLogs[] = "⚠️ Datos de proximidad (API Offline)";
                $eq->audit_logs = $currentLogs;
            }
            
            $logs[] = "[Tanque 3] {$eq->name}: " . number_format($periodKwh, 1) . " kWh";
        }

        // NO restamos del remanente aquí de la misma forma que T0/T1 si queremos que T3 absorba el error,
        // pero la "Cascada" dice que T2 usa el remanente.
        // En realidad, T2 calcula su consumo teórico basado en clima, y el remanente final va a T3.
        $remainingKwh -= $tankConsumption;

        return [
            'consumption' => $tankConsumption,
            'logs' => $logs,
            'climate_data' => $climateStats,
            'processed_count' => $targetEquipments->count()
        ];
    }
}
