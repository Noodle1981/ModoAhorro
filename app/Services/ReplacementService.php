<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Invoice;
use App\Models\EfficiencyBenchmark;
use App\Models\EquipmentUsage;

class ReplacementService
{
    /**
     * Genera oportunidades de reemplazo para una entidad.
     */
    public function generateOpportunities(Entity $entity, ?Invoice $invoice = null): array
    {
        $opportunities = [];

        // Calcular tarifa promedio del kWh
        $avgKwhPrice = $this->getAvgTariff($invoice);

        // Obtener todos los equipos activos de la entidad
        $equipments = $entity->rooms()
            ->with(['equipment.type'])
            ->get()
            ->flatMap(fn($room) => $room->equipment)
            ->filter(fn($eq) => $eq->is_active !== false);

        foreach ($equipments as $equipment) {
            $type = $equipment->type;
            if (!$type) continue;

            // Buscar benchmark para este tipo de equipo
            $benchmark = EfficiencyBenchmark::where('equipment_type_id', $type->id)->first();
            if (!$benchmark) continue;

            // Calcular consumo mensual estimado
            $consumptionKwh = $this->estimateMonthlyConsumption($equipment, $invoice);
            if ($consumptionKwh <= 0) continue;

            // 1. Filtrar si ya es eficiente (Inverter reciente)
            if ($equipment->is_inverter
                && str_contains(strtolower($benchmark->meli_search_term ?? ''), 'inverter')
                && $equipment->acquisition_year
                && $equipment->acquisition_year > (date('Y') - 10)
            ) {
                continue;
            }

            // 2. Ajustar factor de ahorro según indicadores del equipo
            $efficiencyGain = $benchmark->efficiency_gain_factor;

            // Equipo viejo (>10 años) → +15% de ahorro potencial
            if ($equipment->acquisition_year && $equipment->acquisition_year < (date('Y') - 10)) {
                $efficiencyGain = min(0.95, $efficiencyGain + 0.15);
            }

            // Mala etiqueta energética → +10%
            if ($equipment->energy_label && in_array($equipment->energy_label, ['C', 'D', 'E'])) {
                $efficiencyGain = min(0.95, $efficiencyGain + 0.10);
            }

            // 3. Calcular ahorro
            $potentialSavingsKwh = $consumptionKwh * $efficiencyGain;

            // 4. Construir término de búsqueda
            $searchTerm = $benchmark->meli_search_term;
            if ($equipment->capacity && $equipment->capacity_unit) {
                $searchTerm .= ' ' . (int)$equipment->capacity . ' ' . $equipment->capacity_unit;
            }

            // 5. Calcular ahorro monetario y ROI
            $monthlySavings = $potentialSavingsKwh * $avgKwhPrice;
            $investment = $benchmark->average_market_price;
            $paybackMonths = $monthlySavings > 0 ? $investment / $monthlySavings : 999;

            $opportunities[] = [
                'id'                      => $equipment->id,
                'name'                    => $equipment->name,
                'current_consumption_kwh' => round($consumptionKwh, 1),
                'potential_savings_kwh'   => round($potentialSavingsKwh, 1),
                'monthly_savings_amount'  => round($monthlySavings, 0),
                'investment_cost'         => $investment,
                'payback_months'          => round($paybackMonths, 1),
                'verdict'                 => $this->getVerdict($paybackMonths),
                'suggestion'              => $searchTerm,
                'affiliate_link'          => $benchmark->affiliate_link,
                'is_estimated'            => !$this->hasRealUsage($equipment, $invoice),
            ];
        }

        // Ordenar por mayor ahorro mensual
        usort($opportunities, fn($a, $b) => $b['monthly_savings_amount'] <=> $a['monthly_savings_amount']);

        return $opportunities;
    }

    private function estimateMonthlyConsumption($equipment, ?Invoice $invoice): float
    {
        if ($invoice) {
            $usage = EquipmentUsage::where('invoice_id', $invoice->id)
                ->where('equipment_id', $equipment->id)
                ->first();
            if ($usage && ($usage->kwh_reconciled > 0 || $usage->total_energy_consumed_kwh > 0)) {
                return $usage->kwh_reconciled ?: $usage->total_energy_consumed_kwh;
            }
        }

        $powerW = $equipment->nominal_power_w ?? $equipment->type->default_power_watts ?? 0;
        $hoursPerDay = $equipment->avg_daily_use_hours ?? $equipment->type->default_avg_daily_use_hours ?? 0;

        if ($powerW <= 0 || $hoursPerDay <= 0) return 0;
        return ($powerW / 1000) * $hoursPerDay * 30;
    }

    private function hasRealUsage($equipment, ?Invoice $invoice): bool
    {
        if (!$invoice) return false;
        return EquipmentUsage::where('invoice_id', $invoice->id)
            ->where('equipment_id', $equipment->id)
            ->where(function($q) {
                $q->where('kwh_reconciled', '>', 0)->orWhere('total_energy_consumed_kwh', '>', 0);
            })
            ->exists();
    }

    private function getAvgTariff(?Invoice $invoice): float
    {
        if ($invoice && $invoice->total_energy_consumed_kwh > 0) {
            return $invoice->total_amount / $invoice->total_energy_consumed_kwh;
        }
        return 150; // ARS/kWh por defecto
    }

    private function getVerdict(float $months): array
    {
        if ($months <= 12) {
            return ['label' => '💎 Retorno Inmediato', 'color' => 'success', 'bg' => 'bg-emerald-100/50', 'text' => 'text-emerald-700'];
        } elseif ($months <= 36) {
            return ['label' => '🔥 Gran Oportunidad', 'color' => 'warning', 'bg' => 'bg-amber-100/50', 'text' => 'text-amber-700'];
        } else {
            return ['label' => '📈 Ahorro a Largo Plazo', 'color' => 'info', 'bg' => 'bg-sky-100/50', 'text' => 'text-sky-700'];
        }
    }
}
