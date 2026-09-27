<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\Mining\BaselineEngine;
use App\Services\Mining\DeviationOutputService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MiningDashboardController extends Controller
{
    public function __construct(
        protected BaselineEngine $baselineEngine,
        protected DeviationOutputService $deviationOutputService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Obtener pabellones y módulos mineros del usuario (o todos los mineros si es super admin)
        $query = Entity::query()
            ->whereIn('type', ['pabellon', 'oficina'])
            ->with([
                'locality.province',
                'rooms.equipment.type.category',
                'rooms.equipment.category',
                'invoices' => function ($q) {
                    $q->orderBy('end_date', 'desc');
                },
            ]);

        if ($user && ! $user->is_super_admin) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $entities = $query->get();

        // Si no tiene entidades mineras asociadas aún, buscar la demo de Veladero
        if ($entities->isEmpty()) {
            $entities = Entity::whereIn('type', ['pabellon', 'oficina'])
                ->with([
                    'locality.province',
                    'rooms.equipment.type.category',
                    'rooms.equipment.category',
                    'invoices' => function ($q) {
                        $q->orderBy('end_date', 'desc');
                    },
                ])
                ->get();
        }

        $pavilionsData = [];
        $totalLitersWasted = 0.0;
        $totalUsdWasted = 0.0;
        $totalCo2Wasted = 0.0;
        $criticalCount = 0;
        $warnCount = 0;
        $greenWaveCount = 0;

        foreach ($entities as $entity) {
            $occupancy = (int) ($entity->camp_capacity ?? $entity->people_count ?? 30);
            $baseline = $this->baselineEngine->calculateBaseline($entity, [], $occupancy, 30);

            // Obtener última lectura de tablero cargada
            $latestInvoice = $entity->invoices
                ->sortByDesc('end_date')
                ->first();

            $actualKwh = $latestInvoice
                ? (float) ($latestInvoice->total_energy_consumed_kwh ?? 0.0)
                : (float) $baseline['baseline_kwh'];

            $deviation = $this->baselineEngine->calculateDeviation((float) $baseline['baseline_kwh'], $actualKwh);

            // Contar períodos consecutivos en sobreconsumo
            $recentInvoices = $entity->invoices
                ->sortByDesc('end_date')
                ->take(3);

            $consecutiveOverPeriods = 0;
            foreach ($recentInvoices as $inv) {
                if ((float) ($inv->total_energy_consumed_kwh ?? 0) > (float) $baseline['baseline_kwh']) {
                    $consecutiveOverPeriods++;
                } else {
                    break;
                }
            }

            $outputs = $this->deviationOutputService->generate(
                $deviation,
                max(1, $consecutiveOverPeriods),
                $entity
            );

            // Acumular KPIs
            $totalLitersWasted += (float) $deviation['liters_wasted'];
            $totalUsdWasted += (float) $deviation['usd_wasted'];
            $totalCo2Wasted += (float) $deviation['co2_kg'];

            if ($deviation['verdict'] === 'CRITICAL') {
                $criticalCount++;
            } elseif ($deviation['verdict'] === 'WARN') {
                $warnCount++;
            } else {
                $greenWaveCount++;
            }

            // Datos de la fuente de suministro
            $fuenteSuministro = $latestInvoice?->source_type ?? 'Red de Campamento';

            $pavilionsData[] = [
                'id' => $entity->id,
                'name' => $entity->name,
                'type' => $entity->type,
                'shift_type' => $entity->camp_shift_type ?? '14x14',
                'occupancy' => $occupancy,
                'module_type' => $entity->module_type ?? 'modular',
                'supply_source' => $fuenteSuministro,
                'source_type' => $latestInvoice?->source_type ?? 'generator',
                'baseline' => $baseline,
                'latest_reading' => $latestInvoice ? [
                    'id' => $latestInvoice->id,
                    'invoice_number' => $latestInvoice->invoice_number,
                    'kwh' => (float) $latestInvoice->total_energy_consumed_kwh,
                    'total_amount' => (float) $latestInvoice->total_amount,
                    'demand_kw_peak' => (float) ($latestInvoice->demand_kw_peak ?? 0),
                    'generator_hours' => (float) ($latestInvoice->generator_hours ?? 0),
                    'end_date' => $latestInvoice->end_date?->format('d/m/Y'),
                ] : null,
                'deviation' => $deviation,
                'consecutive_periods' => max(1, $consecutiveOverPeriods),
                'outputs' => $outputs,
            ];
        }

        $kpis = [
            'total_pavilions' => count($pavilionsData),
            'critical_count' => $criticalCount,
            'warn_count' => $warnCount,
            'green_wave_count' => $greenWaveCount,
            'diesel_wasted_today_liters' => round($totalLitersWasted / 30, 1),
            'diesel_wasted_month_liters' => round($totalLitersWasted, 1),
            'monthly_usd_savings_potential' => round($totalUsdWasted, 2),
            'co2_kg_monthly' => round($totalCo2Wasted, 1),
            'camp_name' => 'Campamento Base Veladero - Cordillera de San Juan',
            'altitude_msnm' => 4100,
        ];

        return Inertia::render('Mining/Dashboard', [
            'kpis' => $kpis,
            'pavilions' => $pavilionsData,
        ]);
    }
}
