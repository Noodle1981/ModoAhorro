<?php

namespace App\Traits;

use App\Models\Invoice;
use App\Models\Entity;
use Carbon\Carbon;

trait GroupsInvoices
{
    /**
     * Groups invoices into unified periods for an entity.
     * 
     * @param Entity $entity
     * @return \Illuminate\Support\Collection
     */
    protected function getUnifiedPeriods(Entity $entity)
    {
        return Invoice::whereHas('contract', function ($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })
            ->with(['contract.proveedor', 'equipmentUsages:id,invoice_id,tank_assignment,consumption_kwh,kwh_reconciled,equipment_id'])
            ->get()
            ->groupBy(function ($item) {
                return $item->contract_id . '_' . $item->start_date . '_' . $item->end_date;
            })
            ->map(function ($group) {
                $first = $group->first();
                $total_kwh = $group->sum('total_energy_consumed_kwh');
                $total_amount = $group->sum('total_amount');
                $installments_count = $group->count();
                $total_expected_installments = $first->total_installments ?? 2; // Default to 2 for residential

                // Real bimonthly goal (take the highest value found in the group)
                $real_bimonthly_kwh = $group->max('bimonthly_consumption_kwh');

                // Check if all invoices in the group are calibrated
                $all_calibrated = $group->every(fn($inv) => !is_null($inv->calibrated_at));
                $any_calibrated = $group->some(fn($inv) => !is_null($inv->calibrated_at));

                // Compute tanks and check if usages are saved
                $tanks = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                $has_usages = false;

                foreach ($group as $inv) {
                    if ($inv->equipmentUsages->isNotEmpty()) {
                        $has_usages = true;
                        foreach ($inv->equipmentUsages as $usage) {
                            $kwh = $usage->kwh_reconciled ?? $usage->consumption_kwh ?? 0;
                            $tank = (int) $usage->tank_assignment;
                            if (isset($tanks[$tank])) {
                                $tanks[$tank] += $kwh;
                            }
                        }
                    }
                }

                return [
                    'id' => $first->contract_id . '_' . Carbon::parse($first->start_date)->format('Ymd') . '_' . Carbon::parse($first->end_date)->format('Ymd'),
                    'contract_id' => $first->contract_id,
                    'contract_name' => ($first->contract->proveedor->name ?? 'S/P') . ' (#' . $first->contract->supply_number . ')',
                    'start_date' => Carbon::parse($first->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($first->end_date)->format('Y-m-d'),
                    'total_kwh' => $total_kwh,
                    'real_bimonthly_kwh' => $real_bimonthly_kwh ?: $total_kwh,
                    'total_amount' => $total_amount,
                    'installments_count' => $installments_count,
                    'total_expected_installments' => $total_expected_installments,
                    'is_complete' => ($installments_count >= $total_expected_installments) || ($installments_count == 1 && empty($first->installment_number)),
                    'is_calibrated' => $all_calibrated,
                    'is_partially_calibrated' => $any_calibrated && !$all_calibrated,
                    'has_usages_saved' => $has_usages,
                    'tanks' => $tanks,
                    'theoretical_kwh' => array_sum($tanks),
                    'calibrated_at' => $group->max('calibrated_at'),
                    'recommended_kwh' => $group->max('recommended_kwh'),
                    'invoices' => $group->map(function ($inv) {
                        return [
                            'id' => $inv->id,
                            'number' => $inv->invoice_number,
                            'amount' => $inv->total_amount,
                            'kwh' => $inv->total_energy_consumed_kwh,
                            'installment' => $inv->installment_number,
                            'total_installments' => $inv->total_installments,
                            'calibrated_at' => $inv->calibrated_at,
                        ];
                    })->values()
                ];
            })
            ->values()
            ->sortBy('end_date')
            ->values();
    }
}
