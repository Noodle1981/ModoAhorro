<?php

namespace App\Services\Mining;

use App\Models\Entity;

class DeviationOutputService
{
    /**
     * Genera las 4 salidas de valor del sistema según el desvío detectado.
     *
     * @param  array  $deviation  Resultado del BaselineEngine::calculateDeviation
     * @param  int  $consecutivePeriods  Cantidad de períodos consecutivos analizados
     * @param  Entity|null  $pabellon  Pabellón evaluado
     */
    public function generate(array $deviation, int $consecutivePeriods = 1, ?Entity $pabellon = null): array
    {
        $deltaPct = (float) ($deviation['delta_pct'] ?? 0.0);
        $verdict = $deviation['verdict'] ?? 'OK';
        $penaltyThreshold = (int) config('mining.thresholds.penalty_consecutive_periods', 3);
        $okThreshold = (float) config('mining.thresholds.ok_max_delta_pct', 5.0);
        $warnThreshold = (float) config('mining.thresholds.warn_max_delta_pct', 20.0);

        return [
            // 📚 Salida 1: Capacitación / Concientización Operativa (Siempre presente)
            'training' => $this->buildTrainingReport($deviation, $pabellon),

            // ⚠️ Salida 2: Acta de Desvío / Penalización Operativa (Si desvío reiterado >= 3 periodos)
            'penalty' => ($consecutivePeriods >= $penaltyThreshold && $deltaPct > 10.0)
                ? $this->buildPenaltyRecord($deviation, $consecutivePeriods, $pabellon)
                : null,

            // 🌿 Salida 3: Distintivo Onda Verde (Si consumo real <= línea base + 5%)
            'green_wave' => ($deltaPct <= $okThreshold)
                ? $this->buildGreenBadge($deviation, $pabellon)
                : null,

            // 🔧 Salida 4: Propuesta de Reemplazo y ROI en Diésel (Si desvío estructural > 20% o CRITICAL)
            'replacement' => ($deltaPct > $warnThreshold || $verdict === 'CRITICAL')
                ? $this->buildReplacementROI($deviation, $pabellon)
                : null,
        ];
    }

    /**
     * Genera el reporte de capacitación y pautas de conducta para el personal de turno.
     */
    protected function buildTrainingReport(array $deviation, ?Entity $pabellon): array
    {
        $deltaKwh = (float) ($deviation['delta_kwh'] ?? 0.0);
        $recoverableKwh = max(0.0, $deltaKwh * 0.40); // 40% del sobreconsumo suele ser por hábitos
        $dieselLiters = $recoverableKwh * (float) config('mining.diesel_liters_per_kwh', 0.28);

        return [
            'title' => 'Pautas de Operación Responsable para Dotación de Turno',
            'summary' => 'Recomendaciones operativas para los dormitorios y espacios de descanso del módulo.',
            'target_pabellon' => $pabellon?->name ?? 'Pabellón Minero',
            'habits_checklist' => [
                'Modo ECO en Calefacción: Setear termostato a 16°C al salir a turno de faena.',
                'Aislación y Hermeticidad: Verificar el cierre hermético de puertas esclusa y ventanas doble vidrio.',
                'Uso Racional de Duchas: Ajustar tiempos de baño en cambio de turno para evitar encendido de apoyo de emergencia.',
                'Apagado de Iluminación: Apagar luminarias en dormitorios y pasillos modulares durante la jornada diurna.',
            ],
            'recoverable_kwh' => round($recoverableKwh, 1),
            'recoverable_diesel_liters' => round($dieselLiters, 1),
            'action_type' => 'INDUCCION_Y_CHARLA_5_MINUTOS',
        ];
    }

    /**
     * Genera el acta de penalización por desvío reiterado injustificado.
     */
    protected function buildPenaltyRecord(array $deviation, int $consecutivePeriods, ?Entity $pabellon): array
    {
        $deltaKwh = (float) ($deviation['delta_kwh'] ?? 0.0);
        $litersWasted = (float) ($deviation['liters_wasted'] ?? 0.0) * $consecutivePeriods;
        $usdWasted = (float) ($deviation['usd_wasted'] ?? 0.0) * $consecutivePeriods;
        $deltaPct = (float) ($deviation['delta_pct'] ?? 0.0);

        return [
            'code' => 'ACTA-DESVIO-'.strtoupper(substr(md5(($pabellon?->id ?? 1).time()), 0, 8)),
            'pabellon_name' => $pabellon?->name ?? 'Pabellón Minero',
            'shift_code' => $pabellon?->camp_shift_type ?? '14x14',
            'consecutive_periods' => $consecutivePeriods,
            'severity' => $deltaPct > 50.0 ? 'CRÍTICA' : 'MODERADA',
            'accumulated_wasted_kwh' => round($deltaKwh * $consecutivePeriods, 1),
            'accumulated_diesel_liters' => round($litersWasted, 1),
            'accumulated_usd_impact' => round($usdWasted, 2),
            'notice_message' => "El módulo registra {$consecutivePeriods} períodos consecutivos con sobreconsumo injustificado frente a la Línea Base.",
            'required_action' => 'Inspección técnica in situ de termostatos, sellos térmicos y reporte a la gerencia de campamento.',
        ];
    }

    /**
     * Genera el distintivo de reconocimiento ambiental 'Onda Verde'.
     */
    protected function buildGreenBadge(array $deviation, ?Entity $pabellon): array
    {
        $baselineKwh = (float) ($deviation['baseline_kwh'] ?? 100.0);
        $actualKwh = (float) ($deviation['actual_kwh'] ?? 100.0);
        $kwhAvoided = max(0.0, $baselineKwh - $actualKwh);
        $co2Avoided = $kwhAvoided * (float) config('mining.co2_kg_per_kwh', 0.27);

        return [
            'badge' => 'Onda Verde Minera',
            'status' => 'CERTIFICADO_EFICIENTE',
            'recognition' => 'Pabellón operando con eficiencia y cumplimiento estricto de la Línea Base.',
            'pabellon_name' => $pabellon?->name ?? 'Pabellón Minero',
            'co2_avoided_kg' => round($co2Avoided, 1),
            'valid_for_period' => true,
        ];
    }

    /**
     * Genera la propuesta técnica de sustitución de equipamiento y cálculo de ROI en diésel.
     */
    protected function buildReplacementROI(array $deviation, ?Entity $pabellon): array
    {
        $deltaKwh = (float) ($deviation['delta_kwh'] ?? 1000.0);
        $dieselPrice = (float) config('mining.diesel_usd_per_liter', 1.35);

        // Ahorro proyectado al reemplazar convectores resistivos por paneles infrarrojos y colectores solares ACS
        $projectedMonthlySavedKwh = max(500.0, $deltaKwh * 0.70);
        $dieselMonthlyAvoidedLiters = $projectedMonthlySavedKwh * (float) config('mining.diesel_liters_per_kwh', 0.28);
        $monthlySavingsUsd = $dieselMonthlyAvoidedLiters * $dieselPrice;
        $estimatedCapexUsd = 4500.0; // Inversión estimada típica para módulo de 30-40 plazas
        $paybackMonths = $monthlySavingsUsd > 0 ? round($estimatedCapexUsd / $monthlySavingsUsd, 1) : 12.0;

        return [
            'title' => 'Propuesta de Sustitución Tecnológica por Desvío Estructural',
            'pabellon_name' => $pabellon?->name ?? 'Pabellón Minero',
            'primary_solution' => 'Sustitución de convectores resistivos por paneles radiantes infrarrojos (900W) + Colector solar de tubos de vacío para ACS.',
            'estimated_savings_pct' => 40.0,
            'projected_monthly_saved_kwh' => round($projectedMonthlySavedKwh, 1),
            'diesel_monthly_saved_liters' => round($dieselMonthlyAvoidedLiters, 1),
            'diesel_monthly_saved_usd' => round($monthlySavingsUsd, 2),
            'estimated_capex_usd' => $estimatedCapexUsd,
            'payback_months' => $paybackMonths,
        ];
    }
}
