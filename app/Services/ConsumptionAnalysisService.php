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
        // Potencia nominal en kW
        $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
        // Horas de uso por día
        $hoursPerDay = $usage->avg_daily_use_hours ?? 0;
        // Días de uso en el periodo
        $daysInPeriod = $usage->use_days_in_period ?? 0;
        // Consumo total
        return round($powerKw * $hoursPerDay * $daysInPeriod, 2);
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
