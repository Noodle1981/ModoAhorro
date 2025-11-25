<?php

namespace App\Services\Climate;

use App\Models\Invoice;
use App\Models\Equipment;
use Carbon\Carbon;

class UsageSuggestionService
{
    private ClimateDataService $climateService;
    
    public function __construct(ClimateDataService $climateService)
    {
        $this->climateService = $climateService;
    }
    
    /**
     * Sugiere horas de uso para equipos de climatización basándose en datos climáticos
     * 
     * @param Equipment $equipment
     * @param Invoice $invoice
     * @param float $latitude
     * @param float $longitude
     * @return array ['suggested_hours_per_day', 'effective_days', 'confidence', 'explanation']
     */
    public function suggestClimateUsage(Equipment $equipment, Invoice $invoice, float $latitude, float $longitude): array
    {
        $category = $equipment->category->name ?? '';
        $result = [
            'equipment_id' => $equipment->id,
            'usage_adjustment_percent' => 100,
            'adjusted_consumption' => null,
            'suggested_hours_per_day' => 0,
            'effective_days' => 0,
            'confidence' => 'low',
            'explanation' => 'No es equipo de climatización o no se pudo calcular ajuste.'
        ];

        if ($category !== 'Climatización') {
            return $result;
        }

        $stats = $this->climateService->getClimateStats(
            $latitude,
            $longitude,
            Carbon::parse($invoice->start_date),
            Carbon::parse($invoice->end_date)
        );

        // Determinar si es aire acondicionado o calefacción
        $isAirConditioning = $this->isAirConditioningEquipment($equipment);
        $isHeating = $this->isHeatingEquipment($equipment);

        $suggestion = null;
        if ($isAirConditioning) {
            $suggestion = $this->suggestAirConditioningHours($stats);
        } elseif ($isHeating) {
            $suggestion = $this->suggestHeatingHours($stats);
        }

        if ($suggestion) {
            $totalDays = $stats['total_days'] ?? 0;
            $effectiveDays = $suggestion['effective_days'] ?? 0;
            $usageAdjustmentPercent = $totalDays > 0 ? round(100 * $effectiveDays / $totalDays, 2) : 100;

            // El cálculo debe usar la potencia nominal en watts y convertir a kW
            $powerKw = ($equipment->nominal_power_w ?? 0) / 1000;
            $loadFactor = $equipment->factor_carga ?? 1;
            $efficiency = $equipment->eficiencia ?? 1;
            $originalConsumption = $powerKw * ($suggestion['suggested_hours_per_day'] ?? 0) * $effectiveDays * $loadFactor / ($efficiency > 0 ? $efficiency : 1);
            $adjustedConsumption = $originalConsumption * ($usageAdjustmentPercent / 100);

            $result = array_merge([
                'equipment_id' => $equipment->id,
                'usage_adjustment_percent' => $usageAdjustmentPercent,
                'adjusted_consumption' => $adjustedConsumption
            ], $suggestion);
        }

        return $result;
    }
    
    /**
     * Sugiere horas de uso para aire acondicionado
     */
    private function suggestAirConditioningHours(array $stats): array
    {
        $hotDays = $stats['hot_days_count'];
        $totalDays = $stats['total_days'];
        $avgTempAvg = $stats['avg_temp_avg'] ?? null;

        if ($hotDays == 0) {
            return [
                'suggested_hours_per_day' => 0,
                'effective_days' => 0,
                'confidence' => 'high',
                'explanation' => 'No hubo días con temperatura promedio superior a 28°C en este período.'
            ];
        }

        // Calcular horas basadas en temperatura promedio
        // Temp > 32°C → 8h/día
        // Temp 28-32°C → 4-6h/día
        // Temp < 28°C → 0h/día
        $hoursPerDay = 0;

        if ($avgTempAvg !== null) {
            if ($avgTempAvg >= 32) {
                $hoursPerDay = 8;
            } elseif ($avgTempAvg >= 30) {
                $hoursPerDay = 6;
            } elseif ($avgTempAvg >= 28) {
                $hoursPerDay = 4;
            }
        }

        $explanation = sprintf(
            'Se detectaron %d días de calor (temp. promedio ≥28°C) de %d días totales. Temperatura promedio: %.1f°C',
            $hotDays,
            $totalDays,
            $avgTempAvg
        );

        return [
            'suggested_hours_per_day' => $hoursPerDay,
            'effective_days' => $hotDays,
            'confidence' => $hotDays >= 10 ? 'high' : 'medium',
            'explanation' => $explanation
        ];
    }
    
    /**
     * Sugiere horas de uso para calefacción
     */
    private function suggestHeatingHours(array $stats): array
    {
        $coldDays = $stats['cold_days_count'];
        $totalDays = $stats['total_days'];
        $avgTempMin = $stats['avg_temp_min'];
        
        if ($coldDays == 0) {
            return [
                'suggested_hours_per_day' => 0,
                'effective_days' => 0,
                'confidence' => 'high',
                'explanation' => 'No hubo días con temperatura inferior a 15°C en este período.'
            ];
        }
        
        // Calcular horas basadas en temperatura promedio mínima
        // Temp < 10°C → 8h/día
        // Temp 10-15°C → 4-6h/día
        // Temp > 15°C → 0h/día
        $hoursPerDay = 0;
        
        if ($avgTempMin <= 10) {
            $hoursPerDay = 8;
        } elseif ($avgTempMin <= 12) {
            $hoursPerDay = 6;
        } elseif ($avgTempMin <= 15) {
            $hoursPerDay = 4;
        }
        
        $explanation = sprintf(
            'Se detectaron %d días de frío (temp. min ≤15°C) de %d días totales. Temperatura mínima promedio: %.1f°C',
            $coldDays,
            $totalDays,
            $avgTempMin
        );
        
        return [
            'suggested_hours_per_day' => $hoursPerDay,
            'effective_days' => $coldDays,
            'confidence' => $coldDays >= 10 ? 'high' : 'medium',
            'explanation' => $explanation
        ];
    }
    
    /**
     * Determina si el equipo es aire acondicionado
     */
    private function isAirConditioningEquipment(Equipment $equipment): bool
    {
        $name = strtolower($equipment->name);
        $typeName = strtolower($equipment->type->name ?? '');
        // Considerar aire acondicionado y ventiladores como equipos de climatización
        return str_contains($name, 'aire')
            || str_contains($typeName, 'aire acondicionado')
            || str_contains($name, 'ventilador')
            || str_contains($typeName, 'ventilador');
    }
    
    /**
     * Determina si el equipo es calefacción
     */
    private function isHeatingEquipment(Equipment $equipment): bool
    {
        $name = strtolower($equipment->name);
        $typeName = strtolower($equipment->type->name ?? '');
        
        $heatingKeywords = ['caloventor', 'estufa', 'radiador', 'panel calefactor', 'calefactor'];
        
        foreach ($heatingKeywords as $keyword) {
            if (str_contains($name, $keyword) || str_contains($typeName, $keyword)) {
                return true;
            }
        }
        
        return false;
    }
}
