# 3. Calibración Inteligente (Smart Weighted Calibration)

## Concepto
No todos los equipos deben sufrir el "ajuste" por igual. Si la factura es menor a lo calculado, el recorte debe aplicarse prioritariamente a los equipos de **Alto Consumo y Uso Variable** (PC, TV, Clima), protegiendo a los equipos de **Bajo Consumo** (Luces LED) y **Uso Continuo** (Heladeras).

## Clasificación de Equipos (Tiers)

El servicio `ConsumptionCalibrator` debe clasificar los consumos (`EquipmentUsage`) en 3 niveles:

1.  **TIER 1: Fixed / Base Load (Intocables)**
    * *Criterio:* Equipos `always_on` o Seguridad.
    * *Equipos:* Heladeras, Freezers, Routers, Alarmas, Cámaras.
    * *Acción:* **NO AJUSTAR.** Se restan directamente de la factura disponible.

2.  **TIER 2: Low Impact / High Reliability (Bajo Ajuste)**
    * *Criterio:* `Category == Iluminación` OR `Potencia < 60W` (Cargadores, etc).
    * *Razón:* Es improbable que un error en horas de luz afecte el total significativamente. Reducirlos genera desconfianza (un LED no gasta 0.001 kWh).
    * *Acción:* **NO AJUSTAR** (o ajustar muy poco). Se asume el input del usuario como válido.

3.  **TIER 3: Heavy Variable / The "Sponges" (Cargas Pesadas)**
    * *Criterio:* Todo lo demás. Especialmente `Category IN [Climatización, Entretenimiento, Oficina, Cocina]` con `Potencia > 100W`.
    * *Equipos:* PC Gamer, Aires, Estufas, Hornos, Lavarropas, TVs grandes.
    * *Acción:* **ABSORBEN EL ERROR.** Todo el excedente de cálculo se recorta de aquí.

## Algoritmo de Distribución

```php
public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
{
    // 1. Clasificar Usages
    $tier1 = $usages->filter(fn($u) => $this->isTier1($u)); // Heladera, Router
    $tier2 = $usages->filter(fn($u) => $this->isTier2($u) && !$this->isTier1($u)); // Luces, Cargadores
    $tier3 = $usages->reject(fn($u) => $this->isTier1($u) || $this->isTier2($u)); // PC, TV, Aires

    // 2. Calcular Consumos Teóricos (Base)
    $kwhT1 = $tier1->sum('kwh_estimated');
    $kwhT2 = $tier2->sum('kwh_estimated');
    $kwhT3 = $tier3->sum('kwh_estimated');

    // 3. Determinar Energía Disponible para Tier 3
    // Restamos a la factura lo que SÍ O SÍ se consumió (Fijos + Luces)
    $remainingForHeavyLoads = $invoiceTotalKwh - $kwhT1 - $kwhT2;

    // --- ESCENARIO DE ALERTA (Factura Imposible) ---
    if ($remainingForHeavyLoads < 0) {
        // La factura es tan baja (ej 50 kWh) que no cubre ni heladera + luces.
        // Fallback: Ajustar Tier 1 y 2 proporcionalmente para que entren.
        $factor = $invoiceTotalKwh / ($kwhT1 + $kwhT2);
        
        return $usages->map(function ($u) use ($factor) {
            $u->kwh_reconciled = $u->kwh_estimated * $factor;
            $u->adjustment_note = 'CRITICAL_LOW_BILL';
            return $u;
        });
    }

    // --- ESCENARIO NORMAL ---
    
    // A. Tier 1 y 2 quedan intactos (Confianza alta)
    $tier1->merge($tier2)->each(function ($u) {
        $u->kwh_reconciled = $u->kwh_estimated;
        $u->adjustment_note = 'HIGH_CONFIDENCE';
    });

    // B. Tier 3 absorbe la diferencia
    // Si la PC y TV sumaban 250 kWh, y solo sobran 10 kWh:
    // Factor = 10 / 250 = 0.04
    $heavyLoadFactor = ($kwhT3 > 0) ? ($remainingForHeavyLoads / $kwhT3) : 0;

    $tier3->each(function ($u) use ($heavyLoadFactor) {
        $u->kwh_reconciled = $u->kwh_estimated * $heavyLoadFactor;
        $u->adjustment_note = 'HEAVY_LOAD_ADJUSTMENT';
    });

    return $usages;
}

// Helpers de clasificación
private function isTier1($u) {
    $types = ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma'];
    return in_array($u->equipment->type->name, $types);
}

private function isTier2($u) {
    // Iluminación o baja potencia (< 100W)
    return $u->equipment->category->name === 'Iluminación' || 
           $u->equipment->category->name === 'Portátiles' ||
           ($u->equipment->nominal_power_w < 100);
}