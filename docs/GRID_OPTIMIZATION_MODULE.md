# GRID_OPTIMIZATION_MODULE.md
# Especificación: Optimización por Bandas Horarias (Time of Use - Grid)

## 1. Objetivo
Calcular el ahorro monetario potencial al desplazar el uso de electrodomésticos flexibles ("Shiftable Loads") desde horarios de tarifa alta (Pico) hacia horarios de tarifa reducida (Valle).

---

## 2. Base de Datos: Esquemas Tarifarios

Necesitamos modelar la estructura de precios de la compañía eléctrica.

**Tabla:** `tariff_schemes` (Planes disponibles)
* `id`: PK
* `name`: String (Ej: "Residencial T1-TR - Doble Horario").
* `provider`: String.

**Tabla:** `tariff_bands` (Las franjas horarias)
* `tariff_scheme_id`: FK.
* `name`: String (Ej: "Pico", "Valle", "Resto").
* `start_time`: Time (Ej: 18:00:00).
* `end_time`: Time (Ej: 23:00:00).
* `price_per_kwh`: Decimal (El costo en esa hora).
* `is_weekend_applicable`: Boolean (A veces los fines de semana son todo Valle).

---

## 3. Clasificación de Equipos (Shiftable)

Usar la bandera `is_shiftable` en `equipment_types` (definida en el Master Plan).

**Lista Blanca de Equipos Desplazables:**
1.  **Lavarropas** (El clásico).
2.  **Secarropas**.
3.  **Lavavajillas**.
4.  **Termotanque Eléctrico** (Se puede calentar de madrugada y usar en el día si tiene buen aislamiento).
5.  **Bombas de Agua / Piscina**.
6.  **Cargador de Vehículo Eléctrico** (Futuro).

*Nota: Aires Acondicionados y TV NO son desplazables (el usuario no va a mirar tele a las 3 AM para ahorrar).*

---

## 4. Lógica de Cálculo: `GridOptimizerService`

El servicio debe asumir el "Peor Escenario Actual" vs "Mejor Escenario Futuro".

**Suposición Heurística:**
Asumimos que actualmente el usuario usa estos equipos en horario **Pico** (o al volver del trabajo, 19:00 - 21:00), que suele ser lo más caro.

### Algoritmo de Ahorro

```php
public function calculateShiftSavings($usages, $tariffScheme)
{
    // 1. Obtener Precios
    $peakBand = $tariffScheme->bands->sortByDesc('price_per_kwh')->first();
    $offPeakBand = $tariffScheme->bands->sortBy('price_per_kwh')->first();

    $pricePeak = $peakBand->price_per_kwh;
    $priceOffPeak = $offPeakBand->price_per_kwh;
    $priceDiff = $pricePeak - $priceOffPeak;

    $opportunities = [];

    // 2. Analizar Equipos Desplazables
    foreach ($usages as $usage) {
        if (!$usage->equipment->type->is_shiftable) continue;

        // Consumo Mensual (Ya reconciliado/ajustado por Ballenas)
        $kwhMonth = $usage->kwh_reconciled;

        // Ahorro Potencial = Consumo * (PrecioCaro - PrecioBarato)
        $saving = $kwhMonth * $priceDiff;

        if ($saving > 100) { // Filtrar ahorros insignificantes
            $opportunities[] = [
                'equipment' => $usage->equipment->name,
                'current_cost' => $kwhMonth * $pricePeak,
                'optimized_cost' => $kwhMonth * $priceOffPeak,
                'potential_savings' => $saving,
                'suggestion' => "Úsalo entre las {$offPeakBand->start_time} y {$offPeakBand->end_time}"
            ];
        }
    }

    return $opportunities;
}



parte dos


# GRID_OPTIMIZATION_MODULE.md
# Especificación: Optimización Horaria (Grid Time-of-Use)

## 1. Objetivo
Detectar equipos cuyo uso es flexible ("Shiftable") y calcular el ahorro de moverlos de una Zona Pico a una Zona Valle o Resto, basándose en el esquema tarifario del proveedor.

---

## 2. Base de Datos: Tarifas

**Tabla:** `tariff_schemes`
* `provider`: 'Edenor', 'Edesur', etc.
* `name`: 'Residencial Doble Horario'.

**Tabla:** `tariff_bands`
* `scheme_id`: FK.
* `label`: 'Pico', 'Valle', 'Resto'.
* `start_time`: Time.
* `end_time`: Time.
* `price`: Float.
* `color_code`: Hex ('#FF0000' Pico, '#00FF00' Valle, '#FFFF00' Resto).

---

## 3. Lógica de Cálculo (`GridOptimizerService`)

**Entrada:** `reconciled_usages` (Solo equipos `is_shiftable` como Lavarropas/Bombas).

**Algoritmo:**

```php
public function optimizeLoadShifting($usages, $tariffScheme)
{
    // 1. Identificar Bandas
    $peakBand = $tariffScheme->bands->sortByDesc('price')->first(); // La más cara ($180)
    $valleyBand = $tariffScheme->bands->sortBy('price')->first();   // La más barata ($90)
    
    // Buscar una banda intermedia ("Resto") que sea razonable
    // Definición: No es la más barata, pero es < 20% más cara que la Valle
    $shoulderBand = $tariffScheme->bands->filter(function($b) use ($valleyBand) {
        return $b->id !== $valleyBand->id && $b->price < ($valleyBand->price * 1.20);
    })->first();

    $opportunities = [];

    foreach ($usages as $usage) {
        // Asumimos "Peor Caso": El usuario lo usa en PICO actualmente
        $currentCost = $usage->kwh_reconciled * $peakBand->price;
        
        // Calculamos "Mejor Caso": Moverlo a VALLE
        $optimizedCost = $usage->kwh_reconciled * $valleyBand->price;
        $savings = $currentCost - $optimizedCost;

        // Si el ahorro es significativo (ej > $500)
        if ($savings > 500) {
            $opp = [
                'equipment_name' => $usage->equipment->name,
                'current_cost' => $currentCost,
                'optimized_cost' => $optimizedCost,
                'monthly_savings' => $savings,
                'suggestion_primary' => "Úsalo entre {$valleyBand->start_time} y {$valleyBand->end_time}",
            ];

            // Si existe una banda intermedia cómoda (Plan B)
            if ($shoulderBand) {
                $costShoulder = $usage->kwh_reconciled * $shoulderBand->price;
                $opp['suggestion_secondary'] = "O entre {$shoulderBand->start_time} y {$shoulderBand->end_time} (Ahorras $" . ($currentCost - $costShoulder) . ")";
            }

            $opportunities[] = $opp;
        }
    }

    return $opportunities;
}