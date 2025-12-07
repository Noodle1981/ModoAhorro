<?php

namespace App\Services\Recommendations;

use App\Models\Invoice;
use App\Models\EfficiencyBenchmark;
use App\Models\EquipmentUsage;

class ReplacementService
{
    /**
     * Genera oportunidades de reemplazo para una factura dada.
     *
     * @param Invoice $invoice
     * @return array
     */
    public function generateOpportunities(Invoice $invoice): array
    {
        $opportunities = [];
        
        // Obtener usos de equipos de la factura
        $usages = $invoice->equipmentUsages()->with(['equipment.type', 'equipment.category'])->get();

        foreach ($usages as $usage) {
            $equipment = $usage->equipment;
            $type = $equipment->type;

            if (!$type) continue;

            // Buscar benchmark para este tipo de equipo
            $benchmark = EfficiencyBenchmark::where('equipment_type_id', $type->id)->first();

            if (!$benchmark) continue;

            // Calcular consumo actual estimado (mensualizado)
            // El uso ya tiene el consumo calculado para el período de la factura
            $consumptionKwh = $usage->consumption_kwh; 
            
            // Si el consumo es muy bajo, ignorar (ELIMINADO A PEDIDO DEL USUARIO)
            // if ($consumptionKwh < 10) continue;

            // 1. Filtrar si ya es eficiente
            if ($equipment->is_inverter && $benchmark->meli_search_term && str_contains($benchmark->meli_search_term, 'Inverter')) {
                 // Si ya es Inverter, solo recomendar si es muy viejo (>10 años)
                 if (!$equipment->acquisition_year || ($equipment->acquisition_year > date('Y') - 10)) {
                     continue;
                 }
            }

            // 2. Ajustar Factor de Ahorro según indicadores
            $efficiencyGain = $benchmark->efficiency_gain_factor;
            
            // Si es viejo (>10 años), aumenta el ahorro potencial
            if ($equipment->acquisition_year && $equipment->acquisition_year < (date('Y') - 10)) {
                $efficiencyGain += 0.15; // +15% de ahorro extra por vejez
            }

            // Si tiene mala etiqueta, aumenta el ahorro potencial
            if ($equipment->energy_label && in_array($equipment->energy_label, ['C', 'D', 'E'])) {
                $efficiencyGain += 0.10; // +10% de ahorro extra por mala etiqueta
            }

            // 3. Calcular Ahorro
            $potentialSavingsKwh = $consumptionKwh * $efficiencyGain;
            
            // 4. Construir término de búsqueda preciso
            $searchTerm = $benchmark->meli_search_term;
            if ($equipment->capacity && $equipment->capacity_unit) {
                // Ej: "Aire Inverter" -> "Aire Inverter 3500 frigorias"
                $searchTerm .= ' ' . (int)$equipment->capacity . ' ' . $equipment->capacity_unit;
            }

            // Calcular ahorro monetario
            // Precio promedio del kWh en esta factura
            $avgKwhPrice = $invoice->total_amount / max(1, $invoice->total_energy_consumed_kwh);
            $monthlySavings = $potentialSavingsKwh * $avgKwhPrice;

            // Calcular ROI (meses para recuperar inversión)
            $investment = $benchmark->average_market_price;
            $paybackMonths = $monthlySavings > 0 ? $investment / $monthlySavings : 999;

            // Clasificar oportunidad
            $verdict = $this->getVerdict($paybackMonths);

            $opportunities[] = [
                'equipment_id' => $equipment->id,
                'equipment_name' => $equipment->name,
                'current_consumption_kwh' => round($consumptionKwh, 2),
                'potential_savings_kwh' => round($potentialSavingsKwh, 2),
                'monthly_savings_amount' => round($monthlySavings, 2),
                'investment_cost' => $investment,
                'payback_months' => round($paybackMonths, 1),
                'verdict' => $verdict,
                'replacement_suggestion' => $searchTerm,
                'affiliate_link' => $benchmark->affiliate_link,
            ];
        }

        // Ordenar por mayor ahorro mensual
        usort($opportunities, fn($a, $b) => $b['monthly_savings_amount'] <=> $a['monthly_savings_amount']);

        return $opportunities;
    }

    private function getVerdict(float $months): array
    {
        if ($months <= 12) {
            return ['label' => '💎 Retorno Inmediato', 'color' => 'success'];
        } elseif ($months <= 36) {
            return ['label' => '🔥 Gran Oportunidad', 'color' => 'warning'];
        } else {
            return ['label' => '📈 Ahorro a Largo Plazo', 'color' => 'info'];
        }
    }
}
