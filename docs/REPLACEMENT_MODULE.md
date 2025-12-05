# REPLACEMENT_MODULE.md
# Especificación Técnica: Motor de ROI y Reemplazos (Largo Plazo)

## 1. Objetivo
Analizar los equipos de alto consumo ("Ballenas") del usuario, compararlos con estándares de eficiencia modernos y calcular el Retorno de Inversión (ROI).
**Nota:** Se aceptan inversiones con retorno de hasta **10 años (120 meses)**, alineándose con la vida útil promedio de los grandes electrodomésticos.

---

## 2. Base de Datos: Benchmarks de Mercado

**Tabla:** `efficiency_benchmarks`
* `equipment_type_id`: FK.
* `target_name`: String (Ej: "Aire Inverter A++").
* `efficiency_gain_factor`: Float (Ej: 0.40 -> Ahorra un 40%).
* `average_market_price`: Decimal.
* `min_kwh_trigger`: Float.
* `max_payback_months`: Int (**Default: 120**).

---

## 3. Datos Semilla (Seeder Ajustado a 10 Años)

| Equipo Usuario | Reemplazo Sugerido | Ahorro (%) | Costo Est. | Gatillo (kWh) | Payback Máx |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Aire Acondicionado** | Tecnología Inverter A++ | **35%** | $850,000 | 100 kWh | **120 meses** |
| **Heladera** | Heladera Inverter No-Frost | **45%** | $1,200,000 | 60 kWh | **120 meses** |
| **Lavarropas** | Lavarropas Inverter | **30%** | $700,000 | 25 kWh | **120 meses** |
| **Iluminación** | Pack LED Alta Eficiencia | **80%** | $25,000 | 10 kWh | **24 meses** |
| **Termotanque Elec.** | Termotanque Solar | **75%** | $900,000 | 150 kWh | **120 meses** |
| **Calefón a Gas** | Termotanque Solar | **60%** | $900,000 | N/A | **120 meses** |

*Nota: Para Iluminación mantenemos un payback corto porque los focos duran menos.*

---

## 4. Lógica del Motor de ROI (`ReplacementService`)

```php
public function generateOpportunities($reconciledUsages, $kwhPrice)
{
    $opportunities = [];

    foreach ($reconciledUsages as $usage) {
        $benchmark = EfficiencyBenchmark::where('equipment_type_id', $usage->equipment->type_id)->first();
        
        if (!$benchmark || $usage->kwh_reconciled < $benchmark->min_kwh_trigger) continue;

        // Cálculo Financiero
        $currentMonthlyCost = $usage->kwh_reconciled * $kwhPrice;
        $monthlySavings = $currentMonthlyCost * $benchmark->efficiency_gain_factor;
        
        // Evitar división por cero
        if ($monthlySavings <= 0) continue;

        $paybackMonths = $benchmark->average_market_price / $monthlySavings;

        // FILTRO: Aceptamos hasta el límite configurado (120 meses / 10 años)
        if ($paybackMonths <= $benchmark->max_payback_months) {
            
            $opportunities[] = [
                'device_name' => $usage->equipment->name,
                'current_tech' => $usage->equipment->type->name,
                'target_tech' => $benchmark->target_name,
                'investment_cost' => $benchmark->average_market_price,
                'monthly_savings' => round($monthlySavings, 2),
                'payback_months' => round($paybackMonths, 1),
                'roi_verdict' => $this->getVerdictLabel($paybackMonths), // Etiqueta inteligente
                'roi_color' => $this->getVerdictColor($paybackMonths),
                'years_to_profit' => round($paybackMonths / 12, 1)
            ];
        }
    }

    return collect($opportunities)->sortBy('payback_months');
}

/**
 * Define la atractividad de la inversión según el tiempo.
 */
private function getVerdictLabel($months) {
    if ($months < 12) return '💎 Retorno Inmediato (< 1 año)';
    if ($months < 36) return '🔥 Gran Oportunidad (2-3 años)';
    if ($months < 60) return '✅ Buena Inversión (4-5 años)';
    if ($months <= 120) return '📈 Ahorro a Largo Plazo (Vida Útil)';
    return '⚠️ Retorno Lento';
}

private function getVerdictColor($months) {
    if ($months < 36) return 'success'; // Verde
    if ($months < 60) return 'info';    // Azul
    return 'warning';                   // Amarillo/Naranja
}




Visualización en Dashboard (UI)
Tarjeta: Aire Acondicionado (Ejemplo Largo Plazo)

🔄 Renovación Estratégica Aire Grande (Cocina) es antiguo e ineficiente.

Tu Gasto Actual: $25,000 / mes

Con Inverter A++: Ahorras $8,750 / mes

Costo Equipo: $850,000

📈 Veredicto: Ahorro a Largo Plazo Recuperas tu dinero en 8 años. ¿Por qué conviene? Un aire de buena calidad dura 12-15 años. Una vez pagado, tendrás 7 años de ganancia neta ($735,000 acumulados).