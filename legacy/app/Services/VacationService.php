<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Equipment;
use App\Models\Invoice;
use Carbon\Carbon;

class VacationService
{
    /**
     * Obtiene la tarifa real de la última factura de la entidad.
     * Si no hay factura, usa $150 como fallback.
     */
    private function getRealTariff(Entity $entity): float
    {
        $invoice = Invoice::whereHas('contract', function ($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })->latest('end_date')->first();

        if ($invoice
            && ($invoice->total_energy_consumed_kwh ?? 0) > 0
            && ($invoice->total_amount ?? 0) > 0
        ) {
            return round($invoice->total_amount / $invoice->total_energy_consumed_kwh, 2);
        }

        return 150.0; // fallback
    }

    /**
     * Marks invoices as anomalous if the vacation duration is significant.
     */
    public function markAnomalousInvoices(Entity $entity, int $days): int
    {
        if ($days < 7) {
            return 0;
        }

        $startDate = Carbon::now();
        $endDate   = Carbon::now()->addDays($days);

        $invoices = Invoice::whereHas('contract', function ($query) use ($entity) {
                $query->where('entity_id', $entity->id);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();

        $count = 0;
        foreach ($invoices as $invoice) {
            $invoice->update([
                'is_representative' => false,
                'anomaly_reason'    => 'VACATION_MODE'
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Generates a personalized vacation checklist.
     */
    public function generateChecklist(Entity $entity, int $days): array
    {
        $tariff   = $this->getRealTariff($entity);
        $checklist    = [];
        $totalSavings = 0;

        $connectivity = $this->checkConnectivityRule($entity, $days, $tariff);
        $checklist[]  = $connectivity;
        $totalSavings += $connectivity['savings'] ?? 0;

        $refrigeration = $this->checkRefrigerationRule($entity, $days, $tariff);
        if ($refrigeration) {
            $checklist[]  = $refrigeration;
            $totalSavings += $refrigeration['savings'] ?? 0;
        }

        $waterHeater = $this->checkWaterHeaterRule($entity, $days, $tariff);
        if ($waterHeater) {
            $checklist[]  = $waterHeater;
            $totalSavings += $waterHeater['savings'] ?? 0;
        }

        $vampires = $this->checkVampireRule($entity, $days, $tariff);
        if ($vampires) {
            $checklist[]  = $vampires;
            $totalSavings += $vampires['savings'] ?? 0;
        }

        $lighting    = $this->checkLightingRule($entity);
        $checklist[] = $lighting;

        return [
            'checklist'    => $checklist,
            'total_savings' => round($totalSavings, 0),
            'tariff_used'  => $tariff,
        ];
    }

    private function checkConnectivityRule(Entity $entity, int $days, float $tariff): array
    {
        $hasSecurity = $entity->rooms->flatMap->equipment->contains(function ($eq) {
            $name = strtolower($eq->name);
            $type = strtolower($eq->type->name ?? '');
            return str_contains($name, 'cámara') || str_contains($name, 'camara') ||
                   str_contains($name, 'alarma') || str_contains($name, 'sensor') ||
                   str_contains($type, 'cámara') || str_contains($type, 'camara') ||
                   str_contains($type, 'alarma');
        });

        if ($hasSecurity) {
            return [
                'category'    => 'security',
                'title'       => 'Router Wi-Fi',
                'action'      => 'NO TOCAR',
                'description' => 'Tus cámaras y sensores dependen del Wi-Fi. No lo desconectes.',
                'icon'        => 'bi-router-fill',
                'color'       => 'danger',
                'savings'     => 0,
            ];
        }

        $router   = $entity->rooms->flatMap->equipment->first(function ($eq) {
            $name = strtolower($eq->name);
            return str_contains($name, 'modem') || str_contains($name, 'router');
        });
        $powerW      = $router ? ($router->nominal_power_w ?? 10) : 10;
        $savingsKwh  = ($powerW * 24 * $days) / 1000;
        $savingsMoney = round($savingsKwh * $tariff, 0);

        return [
            'category'    => 'savings',
            'title'       => 'Router Wi-Fi',
            'action'      => 'DESCONECTAR',
            'description' => "Sin equipos de seguridad, apagarlo ahorra " . number_format($savingsKwh, 1) . " kWh en {$days} días.",
            'icon'        => 'bi-router',
            'color'       => 'success',
            'savings'     => $savingsMoney,
        ];
    }

    private function checkRefrigerationRule(Entity $entity, int $days, float $tariff): ?array
    {
        $fridge = $entity->rooms->flatMap->equipment->first(function ($eq) {
            $name = strtolower($eq->name);
            return str_contains($name, 'heladera') || str_contains($name, 'freezer');
        });

        if (!$fridge) return null;

        if ($days < 20) {
            // En modo eco (temperatura mínima) se ahorra ~30% del consumo normal
            $powerW       = $fridge->nominal_power_w ?? 150;
            $loadFactor   = 0.35; // compresor corre ~35% del tiempo
            $ecoSaving    = 0.30; // modo eco reduce ~30%
            $savingsKwh   = ($powerW * 24 * $loadFactor * $ecoSaving * $days) / 1000;
            $savingsMoney = round($savingsKwh * $tariff, 0);

            return [
                'category'    => 'recommendation',
                'title'       => 'Heladera / Freezer',
                'action'      => 'MODO ECO (Temperatura mínima)',
                'description' => "No la desconectes para viajes cortos. Subí la temperatura al mínimo y vaciá los perecederos. Ahorrás ~" . number_format($savingsKwh, 1) . " kWh en {$days} días.",
                'icon'        => 'bi-snow',
                'color'       => 'warning',
                'savings'     => $savingsMoney,
            ];
        }

        // Viaje largo > 20 días: conviene desconectarla
        $powerW      = $fridge->nominal_power_w ?? 150;
        $loadFactor  = 0.35; // El compresor corre ~35% del tiempo
        $savingsKwh  = ($powerW * 24 * $loadFactor * $days) / 1000;
        $savingsMoney = round($savingsKwh * $tariff, 0);

        return [
            'category'    => 'critical',
            'title'       => 'Heladera / Freezer',
            'action'      => 'DESCONECTAR Y DEJAR PUERTA ABIERTA',
            'description' => "Más de 20 días: vaciala, desconectala y dejá la puerta entreabierta. Ahorrás " . number_format($savingsKwh, 1) . " kWh.",
            'icon'        => 'bi-snow2',
            'color'       => 'danger',
            'savings'     => $savingsMoney,
        ];
    }

    private function checkWaterHeaterRule(Entity $entity, int $days, float $tariff): ?array
    {
        $heater = $entity->rooms->flatMap->equipment->first(function ($eq) {
            $name = strtolower($eq->name);
            return str_contains($name, 'termotanque') || str_contains($name, 'calefón') || str_contains($name, 'calefon');
        });

        if (!$heater) return null;

        // Un termotanque eléctrico pierde ~1 kWh/día solo manteniendo la temperatura
        $dailyLossKwh = 1.0;
        $savingsKwh   = $dailyLossKwh * $days;
        $savingsMoney = round($savingsKwh * $tariff, 0);

        return [
            'category'    => 'critical',
            'title'       => 'Termotanque Eléctrico',
            'action'      => 'DESCONECTAR',
            'description' => "Mantener agua caliente sin nadie en casa gasta ~{$dailyLossKwh} kWh/día. En {$days} días son " . number_format($savingsKwh, 1) . " kWh innecesarios.",
            'icon'        => 'bi-droplet-half',
            'color'       => 'danger',
            'savings'     => $savingsMoney,
        ];
    }

    private function checkVampireRule(Entity $entity, int $days, float $tariff): ?array
    {
        $vampires = $entity->rooms->flatMap->equipment->filter(function ($eq) {
            $standbyW = $eq->type->default_standby_power_w ?? 0;
            return $standbyW > 0 &&
                   !str_contains(strtolower($eq->name), 'modem') &&
                   !str_contains(strtolower($eq->name), 'router');
        });

        if ($vampires->isEmpty()) return null;

        $totalDailyStandbyKwh = 0;
        $equipmentNames = [];
        foreach ($vampires as $v) {
            $standbyW = $v->type->default_standby_power_w;
            // Standby ocurre las horas que el equipo NO está en uso
            $activeHours  = $v->avg_daily_use_hours ?? 2;
            $standbyHours = max(0, 24 - $activeHours);
            $totalDailyStandbyKwh += ($standbyW * $standbyHours) / 1000;
            $equipmentNames[] = $v->name;
        }

        $savingsKwh   = round($totalDailyStandbyKwh * $days, 1);
        $savingsMoney = round($savingsKwh * $tariff, 0);

        $nameList = implode(', ', array_slice($equipmentNames, 0, 3));
        if (count($equipmentNames) > 3) {
            $nameList .= ' y ' . (count($equipmentNames) - 3) . ' más';
        }

        return [
            'category'    => 'critical',
            'title'       => 'Consumo Fantasma (' . count($equipmentNames) . ' equipos)',
            'action'      => 'DESCONECTAR DE LA PARED',
            'description' => "{$nameList}. Desenchufados de la pared ahorrás {$savingsKwh} kWh y los protegés de tormentas eléctricas.",
            'icon'        => 'bi-plug-fill',
            'color'       => 'danger',
            'savings'     => $savingsMoney,
        ];
    }

    private function checkLightingRule(Entity $entity): array
    {
        return [
            'category'    => 'security',
            'title'       => 'Iluminación',
            'action'      => 'USAR TIMER O FOTOCÉLULA',
            'description' => 'No dejes luces fijas 24h — delata que no estás. Usá un timer para que se prendan de 20 a 23hs.',
            'icon'        => 'bi-lightbulb',
            'color'       => 'info',
            'savings'     => 0,
        ];
    }
}
