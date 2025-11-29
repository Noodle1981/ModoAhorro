# SOLAR_WATER_LOGIC.md
# Lógica de Dimensionamiento y Ahorro para Termotanques Solares

## 1. Estimación de Demanda (Litros Diarios)

**Input Usuario:**
* `adults_count` (int)
* `children_count` (int)
* `usage_habit` (Enum: 'Conservador', 'Normal', 'Derrochador')

**Algoritmo:**
1.  **Litros Base:** `(adults * 50) + (children * 60)`.
2.  **Factor Hábito:**
    * Conservador (Duchas cortas): x0.8
    * Normal: x1.0
    * Derrochador (Baños inmersión/Duchas largas): x1.3
3.  **Demanda Total Diaria (L):** `LitrosBase * FactorHabito`.

*Ejemplo: 4 personas (Normal) = 200 Litros/día.*

## 2. Dimensionamiento del Equipo (Sizing)

El equipo solar se vende por litros de tanque.

**Regla de Selección:**
* **< 150 L:** Equipo de 150L (Hasta 3 personas).
* **150 - 200 L:** Equipo de 200L (3-4 personas).
* **201 - 250 L:** Equipo de 250L (4-5 personas).
* **> 250 L:** Equipo de 300L (5-6+ personas).

## 3. Cálculo Energético (El Motor Termodinámico)

Para saber cuánto ahorra, calculamos cuánta energía se necesita para calentar ese agua.

**Fórmula:** $Q = m \times Cp \times \Delta T$
* `m`: Litros diarios.
* `Cp`: 1 kcal/kg°C (Calor específico del agua).
* `Delta T`: Diferencia entre agua fría (entrada) y caliente (salida).

**Simulación Climática (Usando API Clima):**
* **Temp. Entrada (Agua de red):** Se estima como `Temp_Ambiente_Minima - 2°C` (El suelo aísla).
* **Temp. Objetivo:** 45°C (Ducha caliente).
* **Energía Requerida (Kcal):** `Litros * (45 - Temp_Entrada)`.

**Conversión a kWh (Unidad Universal):**
`1000 Kcal = 1.16 kWh`.

## 4. Cálculo de Ahorro Financiero (ROI)

Aquí comparamos la energía solar vs. el combustible actual.

### A. Energía que aporta el Sol (Solar Fraction)
* **Verano:** El sol cubre el **90-100%** de la demanda.
* **Invierno:** El sol cubre el **50-60%** (Pre-calienta el agua de 10°C a 30°C, el calentador actual hace el resto hasta 45°C).
* **Promedio Anual:** Asumimos **75% de Ahorro**.

### B. Costo del Combustible Actual (El Dolor)

**Caso 1: Electricidad (Termotanque Eléctrico)**
* `Gasto_Mensual = (Energia_Requerida_kWh_Mes / Eficiencia_0.95) * Precio_kWh`.
* *Ahorro:* `Gasto_Mensual * 0.75`.

**Caso 2: Gas Envasado (Garrafa 10kg)**
* Poder Calorífico GLP: ~12.8 kWh por kg.
* Energía por Garrafa: ~128 kWh.
* Eficiencia Calefón: 0.65 (Se pierde calor por chimenea).
* Energía Efectiva por Garrafa: `128 * 0.65 = ~83 kWh`.
* `Garrafas_Necesarias = Energia_Requerida_kWh_Mes / 83`.
* *Ahorro:* `Garrafas_Necesarias * Precio_Garrafa * 0.75`.

**Caso 3: Gas Natural**
* Similar al GLP, usando precio m³ y poder calorífico 9.3 kWh/m³.

---

## 5. Salida para el Dashboard (Visualización)

**Tarjeta: Termotanque Solar**
* **Equipo Recomendado:** 200 Litros.
* **Cubre:** 4 Personas.
* **Combustible Actual:** Gas Envasado.
* **Ahorro Estimado:** **$35,000 / mes**. (Equivale a dejar de comprar 3 garrafas al mes).
* **Amortización:** 14 meses.



# Especificación Técnica: Calculadora de Termotanques Solares

## 1. Objetivo
Estimar el ahorro económico y energético de instalar un Termotanque Solar, comparándolo contra la fuente de energía actual del usuario (Gas Natural, GLP/Garrafa o Electricidad).

---

## 2. Entradas de Datos (Inputs)

### Del Usuario:
* `occupants_count` (int): Cantidad de personas en el hogar (Default: 4).
* `current_source` (enum): 'GAS_NATURAL', 'GAS_GLP', 'ELECTRICITY', 'WOOD'.

### Constantes del Sistema (Configuración):
* `TARGET_TEMP`: 45°C (Temperatura de uso confortable).
* `INLET_TEMP_SUMMER`: 20°C.
* `INLET_TEMP_WINTER`: 10°C.
* `SOLAR_CONTRIBUTION_YEARLY`: 0.75 (El sol cubre el 75% de la demanda anual).
* `PILOT_LIGHT_CONSUMPTION`: 4.0 (m³ de gas/mes desperdiciados por piloto).

### Precios de Referencia (Benchmarks - Actualizables):
* `PRICE_GAS_NATURAL`: $/m³ (Tarifa local).
* `PRICE_GAS_GLP_10KG`: $ (Precio garrafa promedio).
* `PRICE_ELECTRICITY`: $/kWh (Del módulo de facturas).
* `COST_SOLAR_KIT_150L`: $ (Precio equipo + instalación).
* `COST_SOLAR_KIT_200L`: $.
* `COST_SOLAR_KIT_300L`: $.

---

## 3. Lógica de Dimensionamiento (Sizing)

Determinar el tamaño del equipo según la cantidad de personas.

**Regla:** 50 Litros por persona.

```php
public function determineSystemSize(int $people): array
{
    if ($people <= 3) return ['liters' => 150, 'cost' => COST_SOLAR_KIT_150L];
    if ($people <= 4) return ['liters' => 200, 'cost' => COST_SOLAR_KIT_200L];
    return ['liters' => 300, 'cost' => COST_SOLAR_KIT_300L]; // 5+ personas
}
4. Motor Termodinámico (Energy Engine)Calcular cuánta energía se necesita para calentar el agua.Fórmula: $Q (kcal) = Litros \times (T_{final} - T_{inicial})$Conversión: $1000 kcal = 1.163 kWh$.PHPpublic function calculateEnergyDemand(int $people)
{
    $dailyLiters = $people * 50;
    
    // Promedio ponderado anual de Delta T (45°C - 15°C promedio entrada)
    $deltaT = 30; 
    
    $dailyKcal = $dailyLiters * $deltaT;
    $dailyKwh = $dailyKcal / 860; // 1 kWh = 860 kcal aprox.
    
    return [
        'monthly_kwh_thermal' => $dailyKwh * 30, // Energía térmica necesaria al mes
        'liters_per_month' => $dailyLiters * 30
    ];
}