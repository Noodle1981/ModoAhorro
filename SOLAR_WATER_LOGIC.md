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