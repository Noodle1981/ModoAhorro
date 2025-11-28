# ENERGY_CALC_MASTER.md
# Especificación Técnica: Motor de Cálculo y Calibración Energética (MVP)

## 1. Contexto y Objetivo
El sistema actual sobreestima el consumo energético (Calculado: ~700 kWh vs Factura: 123 kWh).
El objetivo es implementar un **Pipeline de Cálculo** que:
1.  Corrija los factores técnicos de los equipos (Base física).
2.  Calibre el resultado final para que coincida matemáticamente con la factura real ("Ajuste Virtual"), distribuyendo la diferencia de forma lógica: respetando consumos fijos (heladeras/luces) y ajustando las cargas variables pesadas (PC/Clima).

---

## FASE 1: Corrección de Datos (Database)

**Acción:** Crear y ejecutar un Seeder (`FixLoadFactorsSeeder`) para actualizar la tabla `equipment_types`.
**Lógica:** El `load_factor` debe reflejar el "Ciclo de Trabajo" (Duty Cycle) y la potencia real vs nominal.

### Tabla de Valores a Actualizar
| Equipo / Categoría | Nuevo Load Factor | Justificación Técnica |
| :--- | :--- | :--- |
| **Heladeras / Freezers** | **0.35** | El motor corta el 65% del tiempo. |
| **Aires Acondicionados** | **0.50** | Compresor cíclico (Inverter o Termostato). |
| **Lavarropas** | **0.30** | El motor consume poco; la resistencia es ocasional. |
| **Computadoras (PC Gamer)** | **0.60** | Fuente de poder sobredimensionada vs uso real. |
| **Notebooks** | **0.40** | Cargador no entrega potencia máxima constante. |
| **Iluminación (LED)** | **1.00** | Consumo constante. |
| **Resistencias (Estufas)** | **1.00** | Consumo constante (si no tiene termostato). |
| **Microondas** | **1.00** | Magnetrón funciona a potencia nominal. |

---

## FASE 2: Servicio de Cálculo Base (Physics Engine)

**Archivo:** `App\Services\ConsumptionCalculator.php`
**Método:** `calculateEquipmentConsumption`

**Cambio Crítico:**
Eliminar la división por eficiencia (`/ $efficiency`). El medidor cobra la potencia de entrada (Input Power). La ineficiencia ya es consumo cobrable.

**Nueva Fórmula:**
$$Consumo_{kWh} = (Potencia_{Watts} / 1000) \times HorasDia \times DiasPeriodo \times LoadFactor$$

```php
public function calculateEquipmentConsumption(EquipmentUsage $usage): float
{
    $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
    
    // Usamos el factor corregido de la Fase 1
    $loadFactor = $usage->equipment->type->load_factor ?? 1.0; 
    
    // Cálculo para frecuencia diaria
    if ($usage->usage_frequency === 'diario') {
        $hours = $usage->avg_daily_use_hours ?? 0;
        $days = $usage->use_days_in_period ?? 0;
        
        // Fórmula limpia
        return round($powerKw * $hours * $days * $loadFactor, 2);
    }
    
    // Lógica para frecuencia puntual...
    return 0.0; 
}

FASE 3: Motor de Calibración Inteligente (Smart Calibration)Archivo: App\Services\ConsumptionCalibrator.phpObjetivo:Tomar los consumos teóricos de la FASE 2 y ajustarlos para que la suma total sea igual a Invoice->consumption_kwh (ej. 123 kWh).Estrategia: No recortar todo por igual. Proteger equipos fijos y de bajo consumo; recortar equipos pesados variables.Algoritmo de 3 Niveles (Tiers)TIER 1 (Intocables): Equipos 24h / Seguridad (Heladera, Router, Alarmas).TIER 2 (Baja Potencia): Iluminación y Cargadores. (Su impacto es bajo, recortarlos genera desconfianza).TIER 3 (Variables Pesados): PC Gamer, Aires, TV, Estufas. (Aquí reside el error de estimación humano).Código del ServicioPHPnamespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class ConsumptionCalibrator
{
    public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
    {
        // 1. Clasificación en Tiers
        $tier1 = $usages->filter(fn($u) => $this->isTier1($u));
        $tier2 = $usages->filter(fn($u) => $this->isTier2($u) && !$this->isTier1($u));
        $tier3 = $usages->reject(fn($u) => $this->isTier1($u) || $this->isTier2($u));

        // 2. Calcular Consumos Teóricos (Base Física)
        // Se asume que $u->kwh_estimated viene de la Fase 2
        $kwhT1 = $tier1->sum('kwh_estimated');
        $kwhT2 = $tier2->sum('kwh_estimated');
        $kwhT3 = $tier3->sum('kwh_estimated');

        // 3. Determinar Energía Disponible para Tier 3
        // Fórmula: Factura - (Heladeras + Luces)
        $remainingForHeavyLoads = $invoiceTotalKwh - $kwhT1 - $kwhT2;

        // --- ESCENARIO A: ALERTA CRÍTICA (Factura Irrealmente Baja) ---
        // Si la factura es menor que la suma de Heladera + Luces
        if ($remainingForHeavyLoads < 0) {
            $totalTheoretical = $kwhT1 + $kwhT2 + $kwhT3;
            $globalFactor = ($totalTheoretical > 0) ? ($invoiceTotalKwh / $totalTheoretical) : 0;
            
            return $usages->map(function ($u) use ($globalFactor) {
                $u->kwh_reconciled = $u->kwh_estimated * $globalFactor; // Ajuste parejo a todo
                $u->calibration_status = 'GLOBAL_FALLBACK';
                return $u;
            });
        }

        // --- ESCENARIO B: NORMAL (Ajuste Ponderado) ---
        
        // Tier 1 y 2: Se respetan sus cálculos físicos (Trust Physics)
        $tier1->merge($tier2)->each(function ($u) {
            $u->kwh_reconciled = $u->kwh_estimated;
            $u->calibration_status = 'HIGH_CONFIDENCE';
        });

        // Tier 3: Absorben el error humano (Absorb Variance)
        // Factor = Lo que sobra de factura / Lo que calculó el usuario
        $heavyLoadFactor = ($kwhT3 > 0) ? ($remainingForHeavyLoads / $kwhT3) : 0;

        $tier3->each(function ($u) use ($heavyLoadFactor) {
            $u->kwh_reconciled = $u->kwh_estimated * $heavyLoadFactor;
            $u->calibration_status = 'ADJUSTED_TO_BILL';
        });

        return $usages;
    }

    // --- Helpers de Clasificación ---
    
    private function isTier1($usage) {
        // Equipos de Uso Continuo
        $types = ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma', 'Cámaras'];
        return in_array($usage->equipment->type->name ?? '', $types);
    }

    private function isTier2($usage) {
        // Iluminación o Baja Potencia (< 100W)
        $category = $usage->equipment->category->name ?? '';
        $power = $usage->equipment->nominal_power_w ?? 0;
        
        return $category === 'Iluminación' || 
               $category === 'Portátiles' || 
               ($power < 100 && $category !== 'Entretenimiento'); 
    }
}
FASE 4: Resultado EsperadoEntrada: Usuario declara PC Gamer (160 kWh) + Heladera (70 kWh) + Luces (15 kWh) = Total 245 kWh.Factura: 123 kWh.Proceso:Tier 1 (Heladera): Reserva 70 kWh.Tier 2 (Luces): Reserva 15 kWh.Restante: $123 - 70 - 15 = 38$ kWh disponibles.Tier 3 (PC Gamer): Tenía 160 kWh teóricos. Debe encajar en 38 kWh.Ajuste: Factor = $38 / 160 = 0.23$.Salida Final:Heladera: 70 kWh (Lógico).Luces: 15 kWh (Lógico).PC Gamer: 38 kWh (Corregido automáticamente).Suma Total: 123 kWh (Exacto).




# ENERGY_CALC_MASTER.md (Actualización: Lógica "Ballenas y Hormigas")

## 3. Calibración por Gravedad (Weighted Gravity Calibration)

**Problema:**
Distribuir la diferencia de consumo proporcionalmente entre todos los equipos variables es injusto. Un cargador de celular (5W) no tiene la "culpa" de una diferencia de 100 kWh en la factura; el culpable es el Aire Acondicionado (2400W).

**Solución:**
Implementar una lógica de **Bloqueo de Pequeñas Cargas**. Los dispositivos de baja potencia (< 100W) o consumo marginal se consideran "Exactos" y se excluyen del ajuste. Todo el peso de la calibración recae sobre los dispositivos de alta potencia.

### Nuevo Algoritmo de Distribución

**Servicio:** `App\Services\ConsumptionCalibrator.php`

```php
public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
{
    // 1. Identificar "Hormigas" y "Carga Base" (Equipos a PROTEGER)
    // Estos equipos NO sufrirán alteraciones, asumimos que su cálculo es perfecto.
    $protectedUsages = $usages->filter(function ($u) {
        $type = $u->equipment->type->name ?? '';
        $category = $u->equipment->category->name ?? '';
        $power = $u->equipment->nominal_power_w ?? 0;
        $kwh = $u->kwh_estimated;

        // A. Carga Base 24h (Heladeras, Routers)
        if (in_array($type, ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma'])) {
            return true;
        }

        // B. Hormigas (Cargadores, Luces, Ventiladores pequeños)
        // Si consume menos de 100W o es Categoría Iluminación/Portátiles
        // O si su consumo total en el periodo es despreciable (< 5 kWh)
        if ($power < 100 || $category === 'Iluminación' || $category === 'Portátiles' || $kwh < 5.0) {
            return true;
        }

        return false;
    });

    // 2. Identificar "Ballenas" (Equipos a AJUSTAR)
    // Aires, Estufas, PC Gamer, Hornos, Lavarropas (> 100W y uso variable)
    $adjustableUsages = $usages->diff($protectedUsages);

    // 3. Calcular Totales
    $protectedConsumption = $protectedUsages->sum('kwh_estimated');
    $adjustableTheoretical = $adjustableUsages->sum('kwh_estimated');

    // 4. Energía Disponible para las Ballenas
    $remainingForWhales = $invoiceTotalKwh - $protectedConsumption;

    // --- ESCENARIO DE PROTECCIÓN ---
    $protectedUsages->each(function ($u) {
        $u->kwh_reconciled = $u->kwh_estimated;
        $u->adjustment_note = 'LOCKED_LOW_IMPACT';
    });

    // --- ESCENARIO DE CALIBRACIÓN DE BALLENAS ---
    
    // Si la factura es tan chica que no cubre ni a las hormigas (Caso Raro)
    if ($remainingForWhales < 0) {
        // Fallback global de emergencia
        $globalFactor = $invoiceTotalKwh / ($protectedConsumption + $adjustableTheoretical);
        return $usages->map(function ($u) use ($globalFactor) {
            $u->kwh_reconciled = $u->kwh_estimated * $globalFactor;
            return $u;
        });
    }

    // Calculamos el factor SOLO para las Ballenas
    // Ejemplo: Sobran 500 kWh reales. Las Ballenas suman 600 kWh teóricos.
    // Factor = 500 / 600 = 0.83
    $whaleFactor = ($adjustableTheoretical > 0) ? ($remainingForWhales / $adjustableTheoretical) : 0;

    $adjustableUsages->each(function ($u) use ($whaleFactor) {
        $u->kwh_reconciled = $u->kwh_estimated * $whaleFactor;
        $u->adjustment_note = 'HIGH_POWER_ADJUSTMENT';
    });

    return $usages;
}



# ENERGY_CALC_FINAL_V2.md
# Especificación Técnica: Motor de Calibración con Supervivencia Jerárquica

## 1. Objetivo
Lograr una reconciliación exacta con la factura protegiendo la coherencia física de los equipos.
Si la factura es abundante, las "Ballenas" (Clima/PC) absorben el consumo extra.
Si la factura es escasa, las "Ballenas" se sacrifican primero para mantener encendida la Heladera (Carga Base).

---

## FASE 1: Corrección Física (Database)
*(Mantener los seeders de factores de carga definidos previamente: Heladera 0.35, etc.)*

---

## FASE 2: Motor de Calibración (Survival Engine)

**Archivo:** `App\Services\ConsumptionCalibrator.php`

### Lógica de Negocio
El algoritmo no debe hacer un "Ajuste Global" ciego. Debe intentar llenar "cubetas" de consumo en orden de prioridad.

1.  **Cubeta A (Base Load):** Heladeras, Routers.
2.  **Cubeta B (Ants):** Luces, Cargadores.
3.  **Cubeta C (Whales):** Clima, PC, TV.

### Código del Servicio

```php
namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Collection;

class ConsumptionCalibrator
{
    public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
    {
        // 1. CLASIFICACIÓN EN 3 NIVELES
        $baseLoad = $usages->filter(function ($u) {
            $type = $u->equipment->type->name ?? '';
            return in_array($type, ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma']);
        });

        $ants = $usages->filter(function ($u) use ($baseLoad) {
            if ($baseLoad->contains('id', $u->id)) return false;
            $cat = $u->equipment->category->name ?? '';
            $power = $u->equipment->nominal_power_w ?? 0;
            return ($cat === 'Iluminación' || $cat === 'Portátiles' || $power < 100);
        });

        $whales = $usages->reject(function ($u) use ($baseLoad, $ants) {
            return $baseLoad->contains('id', $u->id) || $ants->contains('id', $u->id);
        });

        // 2. CALCULAR NECESIDADES TEÓRICAS
        $kwhBase = $baseLoad->sum('kwh_estimated');
        $kwhAnts = $ants->sum('kwh_estimated');
        $kwhWhales = $whales->sum('kwh_estimated');

        // 3. PROCESO DE LLENADO DE CUBETAS (WATERFALL)
        $remainingInvoice = $invoiceTotalKwh;

        // --- PASO A: Satisfacer Carga Base (Heladera/Router) ---
        if ($remainingInvoice >= $kwhBase) {
            // Hay suficiente para la heladera
            $baseLoad->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated, 'PROTECTED_BASE'));
            $remainingInvoice -= $kwhBase;
        } else {
            // ALERTA ROJA: La factura (ej. 50) es menor que la heladera (ej. 80).
            // Recortamos la heladera porque no queda otra (Medidor roto o casa vacía).
            $factor = ($kwhBase > 0) ? $remainingInvoice / $kwhBase : 0;
            $baseLoad->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated * $factor, 'CRITICAL_BASE_CUT'));
            
            // Las hormigas y ballenas mueren
            $ants->merge($whales)->each(fn($u) => $this->setReconciled($u, 0, 'ZERO_ALLOCATION'));
            
            return $usages; // FIN PREMATURO
        }

        // --- PASO B: Satisfacer Hormigas (Luces) ---
        if ($remainingInvoice >= $kwhAnts) {
            // Hay suficiente para las luces
            $ants->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated, 'PROTECTED_ANT'));
            $remainingInvoice -= $kwhAnts;
        } else {
            // Alcanzó para heladera, pero no para todas las luces.
            // Recortamos luces, matamos ballenas.
            $factor = ($kwhAnts > 0) ? $remainingInvoice / $kwhAnts : 0;
            $ants->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated * $factor, 'PARTIAL_ANT_CUT'));
            $whales->each(fn($u) => $this->setReconciled($u, 0, 'ZERO_ALLOCATION'));
            
            return $usages; // FIN PREMATURO
        }

        // --- PASO C: Distribuir Sobrante a Ballenas (Ponderado) ---
        // Si llegamos aquí, Heladeras y Luces están al 100%. Repartimos lo que sobra.
        
        if ($kwhWhales <= 0) return $usages;

        // Calculamos score ponderado para dar prioridad al Aire sobre la PC
        $totalWeightedScore = $whales->sum(function ($u) {
            return $u->kwh_estimated * $this->getCategoryWeight($u->equipment->category->name);
        });

        $whales->each(function ($u) use ($remainingInvoice, $totalWeightedScore) {
            $weight = $this->getCategoryWeight($u->equipment->category->name);
            $score = $u->kwh_estimated * $weight;
            $share = ($totalWeightedScore > 0) ? ($score / $totalWeightedScore) : 0;
            
            $this->setReconciled($u, $remainingInvoice * $share, "WEIGHTED_ADJUSTMENT (x$weight)");
        });

        return $usages;
    }

    // Helper para asignar y guardar estado
    private function setReconciled($usage, $val, $note) {
        $usage->kwh_reconciled = $val;
        $usage->adjustment_note = $note;
    }

    // Pesos de Voracidad
    private function getCategoryWeight($cat) {
        return match($cat) {
            'Climatización' => 3.0,
            'Cocina' => 1.5,
            'Oficina' => 0.6,
            'Entretenimiento' => 0.6,
            default => 1.0
        };
    }
}




Actualización del Código (Solo la clasificación)Modifica la función de filtrado en ConsumptionCalibrator.php. Vamos a ser mucho más estrictos con quién entra al bote salvavidas de las "Hormigas".PHP// DENTRO DE ConsumptionCalibrator.php

// 1. CLASIFICACIÓN
$baseLoad = $usages->filter(function ($u) {
    // ... (Heladera/Router igual que antes) ...
    $type = $u->equipment->type->name ?? '';
    return in_array($type, ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma']);
});

$ants = $usages->filter(function ($u) use ($baseLoad) {
    if ($baseLoad->contains('id', $u->id)) return false;
    
    $cat = $u->equipment->category->name ?? '';
    $type = $u->equipment->type->name ?? '';
    $power = $u->equipment->nominal_power_w ?? 0;

    // --- CAMBIO CLAVE AQUÍ ---
    
    // Regla 1: Iluminación y Portátiles SIEMPRE son Hormigas (Indispensables/Bajo consumo)
    if ($cat === 'Iluminación' || $cat === 'Portátiles') return true;

    // Regla 2: ELIMINAMOS la regla de "< 100W" genérica.
    // Un Monitor de 50W NO es una hormiga, es parte de la PC.
    // Una TV de 85W NO es una hormiga, es entretenimiento.
    
    // Regla 3: Excepciones muy específicas (opcional)
    // Si quieres proteger el ventilador de techo porque gasta poco, ponlo explícito.
    // Pero recomiendo que sea Ballena para que baje si la factura es crítica.
    
    return false; 
});

// Todo lo demás (PC, Monitor, TV chica, TV grande, Aire) son Ballenas.
$whales = $usages->reject(fn($u) => $baseLoad->contains('id', $u->id) || $ants->contains('id', $u->id));
¿Qué pasará ahora con la Factura de 123 kWh?Hagamos la simulación con la nueva lógica:Base (Heladera/Router): 97 kWh. (Intocable).Restante: 26 kWh.Hormigas (SOLO Luces y Cargadores):Suman aprox 12 kWh (antes sumaban 30 porque incluían monitores).El sistema dice: "¿Alcanzan los 26 kWh libres para los 12 kWh de luces?" -> SÍ.Luces/Cargadores: Se quedan al 100% (Estado PROTECTED_ANT).Nuevo Restante: $26 - 12 = \mathbf{14\ kWh}$.Ballenas (El resto):Aquí entran: PC Gamer, Monitores, TV Grande, TV Chica, Aires.Tienen 14 kWh para repartirse.Resultado: Todos recibirán un poco de energía.La PC Gamer tendrá ~2 kWh.El Monitor tendrá ~0.5 kWh.Coherencia: Ambos bajan drásticamente, pero ambos existen. No tienes el monitor al 100% y la PC al 0%.Ajuste Visual del DashboardCon este cambio, verás esto en el panel:Heladera: 70.56 kWh (Base).Focos/Cargadores: Valores normales (Protegido Hormiga).Monitor PC: 0.50 kWh (Ajustado Ballena).PC Gamer: 2.10 kWh (Ajustado Ballena).Aire Acondicionado: 0.00 kWh (Ajustado Ballena - Apagado por Clima o por Peso 3.0 llevándose el recorte).Esto soluciona la paradoja y hace que el "Recorte Hormiga" desaparezca, ya que ahora sí alcanza la energía para las verdaderas hormigas.





# ENERGY_CALC_COMPLETE.md
# Especificación Técnica: Motor de Cálculo Energético Integral

## 1. Visión General
Sistema de reconciliación de consumo energético que ajusta las estimaciones del usuario para coincidir con la factura real, aplicando reglas de física, clima, comportamiento y jerarquía de supervivencia.

---

## FASE 1: Base de Datos y Factores Técnicos

**Acción:** Ejecutar `FixLoadFactorsSeeder`.
**Objetivo:** Establecer factores de uso realistas (Duty Cycle) antes de cualquier calibración.

| Equipo | Load Factor | Razón Técnica |
| :--- | :--- | :--- |
| **Heladeras / Freezers** | **0.35** | Ciclo de compresor (Carga Base Crítica). |
| **Termotanque Eléctrico** | **0.25** | Carga Base Pesada (Cíclico, ~6h/día reales). |
| **Aires Acondicionados** | **0.50** | Inverter/Termostato (Ballena Climática). |
| **Estufas Eléctricas** | **1.00** | Resistencia constante (Ballena Climática). |
| **PC Gamer / Desktop** | **0.60** | Fuente sobredimensionada (Ballena Ocio). |
| **Lavarropas** | **0.30** | Motor eficiente, resistencia ocasional. |
| **Iluminación** | **1.00** | Consumo constante (Hormiga). |

---

## FASE 2: Servicio de Clima (Climate Logic)

**Regla Especial para Termotanques:**
A diferencia del Aire Acondicionado, el Termotanque consume MÁS en invierno.
* **Si Temperatura < 15°C:** Aplicar factor **x1.25** al consumo del Termotanque (Mayor pérdida térmica, agua de entrada más fría).
* **Si Temperatura > 25°C:** Aplicar factor **x0.85**.

---

## FASE 3: Motor de Calibración (The Survival Engine)

**Archivo:** `App\Services\ConsumptionCalibrator.php`

### Algoritmo de Llenado de Cubetas (Waterfall)

El sistema debe asignar la energía de la factura en orden de prioridad estricta.

#### 1. Definición de Cubetas (Jerarquía)

1.  **Nivel 1: BASE CRÍTICA (Intocables)**
    * *Equipos:* Heladeras, Freezers, Routers, Alarmas.
    * *Acción:* Se satisfacen primero. No se recortan salvo catástrofe.

2.  **Nivel 2: BASE PESADA (Higiene/Confort Básico)**
    * *Equipos:* **Termotanques Eléctricos**, Bombas de Agua.
    * *Acción:* Se satisfacen segundo. Si no hay energía, se recortan antes que la Heladera.

3.  **Nivel 3: HORMIGAS (Infraestructura)**
    * *Equipos:* Iluminación, Cargadores, Ventiladores pequeños (<80W).
    * *Acción:* Se protegen si sobra energía de los niveles anteriores.

4.  **Nivel 4: BALLENAS (Ocio y Clima)**
    * *Equipos:* Aires, Estufas, PC Gamer, TV, Microondas, Lavarropas.
    * *Acción:* Absorben la variabilidad. Si sobra energía, se la quedan (ponderada). Si falta, se apagan.

### Código de Implementación

```php
public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
{
    // --- A. CLASIFICACIÓN ---
    
    // 1. Base Crítica
    $baseCritical = $usages->filter(fn($u) => in_array($u->equipment->type->name, 
        ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma']));

    // 2. Base Pesada (Termotanques)
    $baseHeavy = $usages->filter(fn($u) => in_array($u->equipment->type->name, 
        ['Termotanque Eléctrico', 'Calefón Eléctrico', 'Bomba de Agua']));

    // 3. Hormigas (Luces y Portátiles)
    $ants = $usages->filter(function ($u) use ($baseCritical, $baseHeavy) {
        if ($baseCritical->contains('id', $u->id) || $baseHeavy->contains('id', $u->id)) return false;
        $cat = $u->equipment->category->name ?? '';
        return ($cat === 'Iluminación' || $cat === 'Portátiles');
    });

    // 4. Ballenas (Todo lo demás: PC, Aire, TV, Estufa)
    $whales = $usages->reject(fn($u) => 
        $baseCritical->contains('id', $u->id) || 
        $baseHeavy->contains('id', $u->id) || 
        $ants->contains('id', $u->id)
    );

    // --- B. CÁLCULO DE CONSUMOS TEÓRICOS ---
    $reqCritical = $baseCritical->sum('kwh_estimated');
    $reqHeavy    = $baseHeavy->sum('kwh_estimated');
    $reqAnts     = $ants->sum('kwh_estimated');
    $reqWhales   = $whales->sum('kwh_estimated');

    $remaining = $invoiceTotalKwh;

    // --- C. DISTRIBUCIÓN EN CASCADA (WATERFALL) ---

    // Paso 1: Base Crítica (Heladera)
    if ($remaining >= $reqCritical) {
        $this->fullAlloc($baseCritical, 'BASE_CRITICAL');
        $remaining -= $reqCritical;
    } else {
        // Catástrofe: Factura < Heladera. Recorte total.
        $this->partialAlloc($baseCritical, $remaining, $reqCritical, 'CRITICAL_CUT');
        $this->zeroAlloc($baseHeavy->merge($ants)->merge($whales));
        return $usages;
    }

    // Paso 2: Base Pesada (Termotanque)
    if ($remaining >= $reqHeavy) {
        $this->fullAlloc($baseHeavy, 'BASE_HEAVY');
        $remaining -= $reqHeavy;
    } else {
        // Alcanzó para Heladera, pero no para Termotanque completo.
        $this->partialAlloc($baseHeavy, $remaining, $reqHeavy, 'HEAVY_CUT');
        $this->zeroAlloc($ants->merge($whales));
        return $usages;
    }

    // Paso 3: Hormigas (Luces)
    if ($remaining >= $reqAnts) {
        $this->fullAlloc($ants, 'PROTECTED_ANT');
        $remaining -= $reqAnts;
    } else {
        // Recorte de luces
        $this->partialAlloc($ants, $remaining, $reqAnts, 'ANT_CUT');
        $this->zeroAlloc($whales);
        return $usages;
    }

    // Paso 4: Ballenas (PC, Aire) - Distribución Ponderada
    if ($reqWhales > 0) {
        // Aquí aplicamos los PESOS (Aire x3, PC x0.6)
        $totalScore = $whales->sum(fn($u) => 
            $u->kwh_estimated * $this->getCategoryWeight($u->equipment->category->name)
        );

        $whales->each(function ($u) use ($remaining, $totalScore) {
            $weight = $this->getCategoryWeight($u->equipment->category->name);
            $score = $u->kwh_estimated * $weight;
            $share = ($totalScore > 0) ? ($score / $totalScore) : 0;
            
            $u->kwh_reconciled = $remaining * $share;
            $u->adjustment_note = "WHALE_ADJUSTED (x$weight)";
        });
    }

    return $usages;
}

// --- Helpers ---

private function fullAlloc($collection, $note) {
    $collection->each(function($u) use ($note) {
        $u->kwh_reconciled = $u->kwh_estimated;
        $u->adjustment_note = $note;
    });
}

private function partialAlloc($collection, $available, $required, $note) {
    $factor = ($required > 0) ? $available / $required : 0;
    $collection->each(function($u) use ($factor, $note) {
        $u->kwh_reconciled = $u->kwh_estimated * $factor;
        $u->adjustment_note = $note;
    });
}

private function zeroAlloc($collection) {
    $collection->each(function($u) {
        $u->kwh_reconciled = 0;
        $u->adjustment_note = 'ZERO_ALLOCATION';
    });
}

private function getCategoryWeight($cat) {
    return match($cat) {
        'Climatización' => 3.0, // Aire, Estufa
        'Cocina' => 1.5,        // Horno
        'Oficina' => 0.6,       // PC
        'Entretenimiento' => 0.6, // TV
        default => 1.0
    };
}