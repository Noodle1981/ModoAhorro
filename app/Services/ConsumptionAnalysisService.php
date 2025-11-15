<?php

namespace App\Services;

use App\Models\EquipmentUsage;
use App\Models\Invoice;

class ConsumptionAnalysisService
{
    /**
     * Calcula el consumo total de un equipo en un periodo
     * @param EquipmentUsage $usage
     * @param Invoice $invoice
     * @return float Consumo en kWh
     */
    public function calculateEquipmentConsumption(EquipmentUsage $usage, Invoice $invoice): float
    {
        $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
        // Si la frecuencia es diaria o semanal, usar la lógica tradicional
        if (in_array($usage->usage_frequency, ['diario', 'semanal']) || empty($usage->usage_frequency)) {
            $hoursPerDay = $usage->avg_daily_use_hours ?? 0;
            $daysInPeriod = $usage->use_days_in_period ?? 0;
            return round($powerKw * $hoursPerDay * $daysInPeriod, 2);
        }
        // Si la frecuencia es quincenal, mensual o puntual, usar cantidad de usos y duración promedio
        $usageCount = $usage->usage_count ?? 0;
        $avgUseDuration = $usage->avg_use_duration ?? 0; // en horas
        return round($powerKw * $avgUseDuration * $usageCount, 2);
    }

    /**
     * Calcula el consumo total de todos los equipos de una factura
     * @param Invoice $invoice
     * @return array [equipo_id => consumo_kwh]
     */
    public function calculateInvoiceConsumption(Invoice $invoice): array
    {
        $result = [];
        foreach ($invoice->equipmentUsages()->with('equipment')->get() as $usage) {
            $result[$usage->equipment_id] = $this->calculateEquipmentConsumption($usage, $invoice);
        }
        return $result;
    }
}
