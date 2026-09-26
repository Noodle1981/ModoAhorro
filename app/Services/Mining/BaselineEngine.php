<?php

namespace App\Services\Mining;

use App\Models\Entity;
use App\Models\Equipment;

class BaselineEngine
{
    /**
     * Calcula el consumo esperado bajo buenas prácticas operativas (Línea Base Minera)
     * dada la dotación del turno y el inventario de equipamiento del pabellón.
     *
     * @param  Entity  $pabellon  Entidad modular o pabellón
     * @param  array  $shiftSchedule  Horarios de turno ['faena' => ['07:00','19:00'], 'descanso' => ['19:00','07:00']]
     * @param  int  $occupancyCount  Personas efectivas en el período
     * @param  int  $days  Duración del ciclo/período en días (ej: 14, 30)
     */
    public function calculateBaseline(
        Entity $pabellon,
        array $shiftSchedule = [],
        int $occupancyCount = 0,
        int $days = 30
    ): array {
        $pabellon->loadMissing(['rooms.equipment.type.category', 'rooms.equipment.category']);

        $occupancy = $occupancyCount > 0
            ? $occupancyCount
            : (int) ($pabellon->camp_capacity ?? $pabellon->people_count ?? 20);

        if (empty($shiftSchedule)) {
            $shiftSchedule = config('mining.default_shift_schedule', [
                'faena' => ['07:00', '19:00'],
                'descanso' => ['19:00', '07:00'],
            ]);
        }

        $totalDailyKwh = 0.0;
        $byEquipment = [];
        $byRoom = [];
        $byCategory = [];

        foreach ($pabellon->rooms as $room) {
            $roomKwh = 0.0;

            foreach ($room->equipment as $eq) {
                $powerWatts = (float) ($eq->power_watts ?? $eq->type?->default_power_watts ?? 500);
                $powerKw = $powerWatts / 1000.0;
                $quantity = max(1, (int) ($eq->quantity ?? 1));
                $categoryName = $eq->category?->name ?? $eq->type?->category?->name ?? 'General';
                $logic = $eq->type?->consumption_logic ?? 'STANDARD';

                $responsibleHours = $this->calculateResponsibleDailyHours($eq, $logic, $categoryName, $occupancy);
                $dailyEqKwh = $powerKw * $responsibleHours * $quantity;
                $periodEqKwh = $dailyEqKwh * $days;

                $totalDailyKwh += $dailyEqKwh;
                $roomKwh += $periodEqKwh;

                if (! isset($byCategory[$categoryName])) {
                    $byCategory[$categoryName] = 0.0;
                }
                $byCategory[$categoryName] += $periodEqKwh;

                $byEquipment[] = [
                    'id' => $eq->id,
                    'name' => $eq->name,
                    'category' => $categoryName,
                    'room' => $room->name,
                    'power_watts' => $powerWatts,
                    'quantity' => $quantity,
                    'responsible_hours_day' => round($responsibleHours, 1),
                    'daily_kwh' => round($dailyEqKwh, 2),
                    'period_kwh' => round($periodEqKwh, 1),
                ];
            }

            $byRoom[] = [
                'room_id' => $room->id,
                'room_name' => $room->name,
                'period_kwh' => round($roomKwh, 1),
            ];
        }

        $totalBaselineKwh = $totalDailyKwh * $days;

        // Si el pabellón no tiene equipos cargados aún, calculamos línea base teórica paramétrica por plaza
        if ($totalBaselineKwh <= 0 && $occupancy > 0) {
            // Estándar andino minero: ~12.5 kWh/día por plaza ocupada en alta montaña con calefacción eléctrica
            $totalDailyKwh = $occupancy * 12.5;
            $totalBaselineKwh = $totalDailyKwh * $days;
        }

        // Redondear desglose por categorías
        foreach ($byCategory as $cat => $val) {
            $byCategory[$cat] = round($val, 1);
        }

        return [
            'baseline_kwh' => round($totalBaselineKwh, 1),
            'daily_baseline_kwh' => round($totalDailyKwh, 1),
            'by_equipment' => $byEquipment,
            'by_room' => $byRoom,
            'by_category' => $byCategory,
            'days' => $days,
            'occupancy_count' => $occupancy,
            'shift_schedule' => $shiftSchedule,
            'meta' => [
                'standard' => 'Norma Minera Alta Montaña San Juan (IRAM / ISO 50001)',
                'shift_model' => $pabellon->camp_shift_type ?? '14x14',
            ],
        ];
    }

    /**
     * Determina las horas responsables de operación de un equipo según buenas prácticas de campamento.
     */
    protected function calculateResponsibleDailyHours(
        Equipment $equipment,
        string $logic,
        string $categoryName,
        int $occupancy
    ): float {
        // 1. Calefacción en campamento: ECO en faena (07-19hs, 25% carga) + Activo en descanso (19-07hs, 70% carga)
        if ($logic === 'CLIMATE_DEPENDENT' || str_contains($categoryName, 'Calefacción')) {
            // 12h * 0.25 (ECO) + 12h * 0.70 (Confort nocturno) = 3h + 8.4h = 11.4h equivalentes a plena carga
            return 11.4;
        }

        // 2. Agua Caliente / Bombas / Termotanques: duchas cambio de turno
        if ($logic === 'BASE_THERMAL_LOSS' || str_contains($categoryName, 'Agua')) {
            // Termotanque industrial: standby térmico 24h con aislación (factor 0.2) + picos de recarga 4h
            return 8.5;
        }

        // 3. Traceado Eléctrico: sensor anticongelamiento opera solo ante temperaturas bajo cero (~10h nocturnas)
        if (str_contains(strtolower($equipment->name), 'traceado') || str_contains(strtolower($equipment->type?->name ?? ''), 'traceado')) {
            return 10.0;
        }

        // 4. Equipos continuos o seguridad (detectores CO, routers, emergencia)
        if (str_contains($categoryName, 'Seguridad') || $equipment->type?->name === 'Detector de Gas / CO') {
            return 24.0;
        }

        // 5. Compresores y servicios industriales de faena
        if ($logic === 'CONTINUOUS_COMMERCIAL' || str_contains($categoryName, 'Compresores')) {
            return 12.0; // Solo turno activo de trabajo
        }

        // 6. Iluminación y otros
        if (str_contains($categoryName, 'Iluminación')) {
            return 6.0; // Buenas prácticas: apagado en horas solares
        }

        return 6.0;
    }

    /**
     * Calcula el desvío entre la línea base y la lectura real del tablero.
     *
     * @param  float  $baseline  Consumo esperado en kWh
     * @param  float  $actual  Consumo real medido en kWh
     */
    public function calculateDeviation(float $baseline, float $actual): array
    {
        $deltaKwh = $actual - $baseline;
        $deltaPct = $baseline > 0 ? ($deltaKwh / $baseline) * 100 : 0.0;

        $dieselLitersFactor = (float) config('mining.diesel_liters_per_kwh', 0.28);
        $co2KgFactor = (float) config('mining.co2_kg_per_kwh', 0.27);
        $dieselUsdPrice = (float) config('mining.diesel_usd_per_liter', 1.35);

        // Litros y costos de diésel desperdiciados ante sobreconsumo
        $litersWasted = max(0.0, $deltaKwh) * $dieselLitersFactor;
        $co2KgWasted = max(0.0, $deltaKwh) * $co2KgFactor;
        $usdWasted = $litersWasted * $dieselUsdPrice;

        return [
            'baseline_kwh' => round($baseline, 1),
            'actual_kwh' => round($actual, 1),
            'delta_kwh' => round($deltaKwh, 1),
            'delta_pct' => round($deltaPct, 1),
            'liters_wasted' => round($litersWasted, 1),
            'co2_kg' => round($co2KgWasted, 1),
            'usd_wasted' => round($usdWasted, 2),
            'verdict' => $this->getVerdict($deltaPct),
        ];
    }

    /**
     * Obtiene el dictamen de desvío según umbrales de campamento.
     */
    public function getVerdict(float $deltaPct): string
    {
        if ($deltaPct <= (float) config('mining.thresholds.ok_max_delta_pct', 5.0)) {
            return 'OK';
        }

        if ($deltaPct <= (float) config('mining.thresholds.warn_max_delta_pct', 20.0)) {
            return 'WARN';
        }

        return 'CRITICAL';
    }
}
