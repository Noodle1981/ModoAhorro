<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Equipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EnergyEngineService
{
    protected $climateService;
    protected $thermalService;
    protected $lastClimateDays = []; // Store the last calculated climate days

    public function __construct(ClimateService $climateService, ThermalProfileService $thermalService)
    {
        $this->climateService = $climateService;
        $this->thermalService = $thermalService;
    }

    /**
     * Distribuye el consumo de la factura en los 3 Tanques y ajusta el consumo unitario de los equipos.
     * Retorna un reporte detallado del proceso.
     */
    public function processInvoice(Invoice $invoice, Collection $equipments): array
    {
        $totalBillKwh = $invoice->consumption_kwh;
        $daysInPeriod = $invoice->days_in_period; 
        $logs = [];

        // Preparar equipos con metadatos v3
        // Asumiendo que vienen con 'type' cargado o lazy loading
        
        // --- TANQUE 1: BASE INMUTABLE ---
        $tank1Consumption = 0;
        $tank1Equipments = $equipments->filter(function ($eq) {
            // Lógica robusta: Si el tipo dice base, o si el uso es 24h
            return $eq->type->isBase() || ($eq->use_time_hours == 24 && !$eq->type->isClimate());
        });

        foreach ($tank1Equipments as $eq) {
            // Consumo = Potencia * 24h * Días * FactorCarga
            $dailyKwh = ($eq->type->default_power_watts * 24 * $eq->type->load_factor) / 1000;
            $periodKwh = $dailyKwh * $daysInPeriod;
            
            $eq->calculated_consumption_kwh = $periodKwh;
            $eq->tank_assignment = 1;
            $eq->audit_logs = ["Fijado en " . number_format($periodKwh, 1) . " kWh (24/7)"];
            $tank1Consumption += $periodKwh;
            $logs[] = "[Tanque 1] {$eq->name}: Fijado en " . number_format($periodKwh, 1) . " kWh (24/7)";
        }

        // Remanente post-Tanque 1
        $remainingKwh = max(0, $totalBillKwh - $tank1Consumption);

        // --- TANQUE 2: CLIMATIZACIÓN ---
        $tank2Consumption = 0;
        $tank2Equipments = $equipments->filter(function ($eq) {
            return $eq->type->isClimate();
        });

        if ($tank2Equipments->isNotEmpty()) {
            // Datos climáticos
            $locality = $invoice->contract->entity->locality;
            $climateStats = $this->climateService->getDegreeDaysForLocality(
                $locality, 
                $invoice->start_date, 
                $invoice->end_date
            );
            
            $this->lastClimateDays = $climateStats; // Store for retrieval
            
            // Factor Térmico del Hogar (A-E)
            $thermalMultiplier = $this->thermalService->calculateMultiplier($invoice->contract->entity);

            foreach ($tank2Equipments as $eq) {
                // Heurística de nombre para decidir HDD vs CDD si no está explícito
                $name = strtolower($eq->type->name);
                $isCooling = str_contains($name, 'aire') || str_contains($name, 'ventilador') || str_contains($name, 'split');
                
                $degreeDays = $isCooling ? ($climateStats['cooling_days'] ?? 0) : ($climateStats['heating_days'] ?? 0);
                
                // Si no hubo grados día (ej: estufa en verano), consumo tiende a 0 (o a un standby mínimo?)
                // Por ahora, asumimos que si no hace frío, la estufa no se prende.
                if ($degreeDays <= 0) {
                     $periodKwh = 0;
                     $logs[] = "[Tanque 2] {$eq->name}: 0 kWh (Sin Grados-Día activos)";
                     $eq->audit_logs = ["0 kWh (Sin Grados-Día activos)"];
                } else {
                    $avgDegreeDays = $degreeDays / $daysInPeriod;
                    // Factor Climático: Normalizamos con un umbral de 5°C
                    $climateMainFactor = min(1.0, ($avgDegreeDays / 5.0)); 
                    
                    // Carga Final
                    $finalLoadFactor = $eq->type->load_factor * $climateMainFactor * $thermalMultiplier;
                    $finalLoadFactor = min(1.0, $finalLoadFactor); // Cap en 100%
                    
                    // Usar horas declaradas en la instancia si existen, sino el default del tipo
                    $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $eq->type->default_avg_daily_use_hours;

                    // Cálculo
                    $dailyKwh = ($eq->type->default_power_watts * $hours * $finalLoadFactor) / 1000;
                    $periodKwh = $dailyKwh * $daysInPeriod;
                    
                    $logMsg = number_format($periodKwh, 1) . " kWh (CDD/HDD: $degreeDays, Load: " . number_format($finalLoadFactor, 2) . ")";
                    $logs[] = "[Tanque 2] {$eq->name}: " . $logMsg;
                    $eq->audit_logs = [$logMsg];
                }

                $eq->calculated_consumption_kwh = $periodKwh;
                $eq->tank_assignment = 2;
                $tank2Consumption += $periodKwh;
            }
        }

        // Remanente post-Tanque 2
        $remainingKwh = max(0, $remainingKwh - $tank2Consumption);

        // --- TANQUE 3: ELASTICIDAD (El Resto) ---
        $tank3Consumption = 0;
        $tank3Equipments = $equipments->filter(function ($eq) {
            return $eq->tank_assignment === null; // Los que no cayeron en T1 o T2
        });

        if ($tank3Equipments->isNotEmpty()) {
            // Mapa de Intensidad -> Puntos
            $intensityMap = [
                'Bajo' => 1, 
                'Medio' => 2, 
                'Alto' => 3, 
                'Excesivo' => 5, 
                'Critico' => 5 
            ];
            
            $totalPoints = 0;
            foreach ($tank3Equipments as $eq) {
                // Normalizar intensidad
                $intensityStr = ucfirst(strtolower($eq->type->intensity ?? 'Medio'));
                $points = $intensityMap[$intensityStr] ?? 2;
                
                // Ponderación por potencia: Raíz cuadrada para suavizar (que un horno no se lleve TODO el consumo solo por tener 2000W)
                $powerWeight = sqrt($eq->type->default_power_watts); 
                
                $eq->elasticity_points = $points * $powerWeight;
                $totalPoints += $eq->elasticity_points;
            }

            if ($totalPoints > 0) {
                foreach ($tank3Equipments as $eq) {
                    $share = $eq->elasticity_points / $totalPoints;
                    $periodKwh = $remainingKwh * $share;
                    
                    $eq->calculated_consumption_kwh = $periodKwh;
                    $eq->tank_assignment = 3;
                    $tank3Consumption += $periodKwh;
                    $eq->audit_logs = ["Asignado " . number_format($periodKwh, 1) . " kWh (Elasticidad: " . number_format($share * 100, 1) . "%)"];
                }
                $logs[] = "[Tanque 3] Distribuyendo " . number_format($remainingKwh, 1) . " kWh entre " . $tank3Equipments->count() . " equipos.";
            }
        }
        
        return [
            'total_bill' => $totalBillKwh,
            'tank_1_base' => $tank1Consumption,
            'tank_2_climate' => $tank2Consumption,
            'tank_3_elasticity' => $tank3Consumption,
            'unassigned_remainder' => max(0, $remainingKwh - $tank3Consumption),
            'equipments_processed' => $equipments->count(),
            'logs' => $logs,
            'climate_data' => $this->lastClimateDays // Return it in the result too
        ];
    }

    public function getClimateDays(): array
    {
        return $this->lastClimateDays;
    }
}
