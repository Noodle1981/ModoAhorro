# MASTER_PLAN_MODO_AHORRO.md
# Especificación Técnica Integral: Plataforma de Gestión Energética

## 1. Visión General
El objetivo es refactorizar el motor de cálculo ("Modo Ahorro") para que pase de una estimación teórica imprecisa a una **Reconciliación Precisa** basada en la factura real, e integrar módulos de **Energía Solar** y **Recomendaciones de Eficiencia** con lógica financiera realista.

---

## MÓDULO A: MOTOR DE CÁLCULO Y CALIBRACIÓN (CORE)

### 1. Actualización de Base de Datos (Física Realista)
Necesitamos corregir los factores de consumo y habilitar la lógica de Standby.

**A. Seeders (`FixLoadFactorsSeeder`):**
Actualizar `equipment_types` con valores que reflejen el Ciclo de Trabajo (Duty Cycle).
* **Heladeras / Freezers:** `0.35` (Carga Base Crítica).
* **Termotanque Eléctrico:** `0.25` (Carga Base Pesada).
* **Aires Acondicionados:** `0.50` (Ballena Climática).
* **Estufas Eléctricas:** `1.00` (Ballena Climática - Resistiva).
* **PC Gamer:** `0.60` (Fuente sobredimensionada).
* **Lavarropas:** `0.30`.
* **Iluminación:** `1.00`.

**B. Estructura Standby:**
Agregar a `equipment_categories`:
* `allows_standby` (boolean): Define si la categoría permite consumo fantasma (Ej: False para Iluminación/Resistencias, True para TV/PC).

### 2. Servicio de Cálculo Base (`ConsumptionCalculator`)
**Refactorización:**
1.  **Eliminar eficiencia:** La fórmula es `Potencia * Horas * Días * LoadFactor`. No dividir por eficiencia.
2.  **Lógica Standby:**
    * Si `category->allows_standby` es TRUE:
    * `ConsumoTotal = (PotenciaActiva * HorasUso) + (PotenciaStandby * (24 - HorasUso))`.

### 3. Motor de Calibración (`ConsumptionCalibrator`)
**Lógica:** Reconciliar el cálculo teórico con el total de la factura (`Invoice->kwh_total`) usando el algoritmo **"Survival Waterfall"** (Cascada de Supervivencia).

**Clasificación de Equipos:**
1.  **TIER 1 (Base Intocable):** Heladeras, Routers, Alarmas, Termotanques. (Prioridad Máxima).
2.  **TIER 2 (Hormigas Protegidas):** Iluminación, Cargadores. (Prioridad Media).
    * *Nota:* Monitores, TVs y Ventiladores NO son hormigas, son Ballenas (deben apagarse si la PC se apaga).
3.  **TIER 3 (Ballenas Ajustables):** PC, Aire, Estufa, TV, Microondas. (Absorben la diferencia).

**Algoritmo de Distribución:**
1.  **Llenar Base:** Restar consumo Base de la Factura. Si no alcanza, recortar Base (Alerta Crítica).
2.  **Llenar Hormigas:** Si sobra, restar consumo Hormigas. Si no alcanza, recortar Hormigas.
3.  **Llenar Ballenas:** El remanente se distribuye entre las Ballenas usando **Pesos de Voracidad**:
    * **Climatización (Aire/Estufa):** Peso **3.0** (Prioridad de consumo).
    * **Cocina:** Peso **1.5**.
    * **Electrónica (PC/TV):** Peso **0.6**.

---

## MÓDULO B: ENERGÍA SOLAR FOTOVOLTAICA (PV)

### Lógica de Dimensionamiento (Espacio vs. Demanda)
No calcular "cuánto cabe", sino "cuánto se necesita".

**Algoritmo:**
1.  **Meta:** Cubrir el Consumo Pico (Verano) de las facturas históricas.
2.  **Espacio:** Calcular cuántos paneles caben en el área disponible (`m2 / 2`).
3.  **Resultado:** El menor entre (Lo que cabe) y (Lo que se necesita).

### Lógica de Ahorro Financiero (No sobre-prometer)
* **Ahorro Mensual:** `min(Generación_Estimada, Monto_Factura_Mes)`.
* *Explicación:* No prometer ahorros superiores a la factura (salvo venta de excedentes explícita).
* **Visualización:** Mostrar "% Cobertura Verano" y "% Cobertura Invierno" por separado.

### Lógica de Baterías (Respaldo)
* Calcular consumo horario de **TIER 1 (Base)** + **TIER 2 (Hormigas)**.
* Estimación: `Horas_Autonomia = Capacidad_Bateria / Consumo_Hora_Critico`.

---

## MÓDULO C: TERMOTANQUES SOLARES (TÉRMICA)

### Motor Termodinámico
Calcular ahorro basado en calentar agua, comparando contra la fuente actual.

**Entradas:** Personas (Litros: Personas * 50).
**Fórmula:** $Q = Litros \times \Delta T$. (Convertir kcal a kWh).
**Comparador (ROI):**
* **Vs. Electricidad:** Ahorro muy alto (ROI ~12 meses).
* **Vs. Gas Garrafa (GLP):** Ahorro crítico (ROI ~14 meses).
* **Vs. Gas Natural:** Ahorro medio (ROI ~24 meses).

**Factor Climático:**
* Verano: Cobertura 100%.
* Invierno: Cobertura 60% (Pre-calentado).

---

## MÓDULO D: RECOMENDADOR DE EFICIENCIA (UPGRADES)

### Estrategia: "Asumir, Preguntar, Refinar"
Detectar oportunidades de ahorro en equipos "Ballena".

1.  **Escaneo:** Identificar equipos con alto consumo reconciliado (ej. Aire > $20k/mes).
2.  **Hipótesis Pesimista:** Si no hay dato de antigüedad, asumir que es **Viejo/Ineficiente** para mostrar el ahorro máximo potencial.
3.  **Wizard Interactivo:**
    * Tarjeta en Dashboard: *"Podrías ahorrar $X cambiando tu Aire"*.
    * Al hacer clic, preguntar: "¿Es Inverter?" / "¿Tiene más de 10 años?".
    * Recalcular ROI con la respuesta.

---

## RESUMEN DE FLUJO DE DATOS

1.  **Input:** Usuario carga inventario y facturas.
2.  **Proceso 1 (Física):** `ConsumptionCalculator` estima consumo teórico (incluyendo Standby si corresponde).
3.  **Proceso 2 (Calibración):** `ConsumptionCalibrator` ajusta los valores para coincidir con la factura (Protegiendo Heladera/Luces, ajustando PC/Aire).
4.  **Proceso 3 (Oportunidades):**
    * Módulo Solar usa el Consumo Histórico para dimensionar paneles.
    * Módulo Solar usa el Consumo Histórico para dimensionar paneles.
    * Módulo Recomendador usa el Consumo Reconciliado para sugerir cambios de electrodomésticos.

## ESTADO DE AVANCE (Roadmap)

### Fase 1: Core de Cálculo
- [x] Refactorización de `ConsumptionAnalysisService`.
- [x] Integración de API Climática (Open-Meteo).
- [x] Lógica de Calibración (Survival Waterfall).

### Fase 2: Módulos de Valor Agregado
- [x] **Módulo Solar Térmico (Calefones)**: Implementado. Cálculo de ahorro vs Gas/Electricidad.
- [x] **Módulo de Mantenimiento**: Implementado. Penalización de consumo por tareas vencidas.
- [ ] **Módulo Solar Fotovoltaico**: Pendiente.
- [ ] **Módulo de Recomendaciones (Upgrades)**: Pendiente.

### Fase 3: Refinamiento
- [ ] Implementación de Standby (Pospuesto).
- [ ] Reportes PDF.