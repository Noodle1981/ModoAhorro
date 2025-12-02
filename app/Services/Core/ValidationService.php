<?php

namespace App\Services\Core;

use App\Models\Invoice;

class ValidationService
{
    /**
     * Calcula la desviación entre consumo calculado y facturado
     */
    public function calculateDeviation(Invoice $invoice, float $calculatedConsumption): array
    {
        $billed = $invoice->total_energy_consumed_kwh ?? 0;
        $deviation = abs($calculatedConsumption - $billed);
        $deviationPercent = $billed > 0 ? ($deviation / $billed) * 100 : 0;
        
        return [
            'billed' => $billed,
            'calculated' => $calculatedConsumption,
            'deviation' => $deviation,
            'deviation_percent' => round($deviationPercent, 2),
            'alert_level' => $this->getAlertLevel($deviationPercent),
        ];
    }
    
    /**
     * Determina el nivel de alerta según la desviación
     */
    private function getAlertLevel(float $deviationPercent): string
    {
        return match(true) {
            $deviationPercent < 10 => 'success',  // ✅ Excelente
            $deviationPercent < 30 => 'warning',  // ⚠️ Revisar
            default => 'danger'                    // ❌ Crítico
        };
    }
    
    /**
     * Genera sugerencias de ajuste
     */
    public function getSuggestions(Invoice $invoice, float $calculatedConsumption): array
    {
        $suggestions = [];
        $diff = ($invoice->total_energy_consumed_kwh ?? 0) - $calculatedConsumption;
        
        // Si el calculado es mucho MENOR que el facturado (falta consumo)
        if ($diff > 50) {
            $suggestions[] = 'Revisa equipos de climatización (mayor impacto)';
            $suggestions[] = 'Verifica equipos de alto consumo (>1000W)';
            $suggestions[] = '¿Olvidaste ajustar algún equipo?';
        }
        // Si el calculado es mucho MAYOR que el facturado (sobra consumo)
        elseif ($diff < -50) {
             $suggestions[] = 'Revisa si cargaste horas de más en algún equipo';
             $suggestions[] = 'Verifica la potencia de los equipos cargados';
        }
        
        return $suggestions;
    }
}
