# PDR - Project Development Record: ModoAhorro Motor v4

## 1. Visión General
Este documento registra la evolución arquitectónica de **ModoAhorro** hacia un modelo de simulación física (Gemelo Digital). El sistema ha migrado de una gestión de inventario plana a una gestión de activos con comportamiento termodinámico, patrones de habitabilidad declarados por el usuario y un motor de distribución de energía por tanques progresivamente más inteligente.

---

## 2. Problemas Resueltos (Historia)

| Versión | Problema | Solución |
|---|---|---|
| v1 | Ajustes sobrescribían hábitos globales | `has_defined_pattern` protege hábitos congelados |
| v2 | Sin diferenciación tecnológica (Inverter vs On/Off) | `is_inverter` + `energy_label_coefficients` |
| v3 | Motor clasificaba por catálogo, ignorando al usuario | Cascada de tanques basada en comportamiento declarado |
| v4 | `has_defined_pattern` binario no distingue tipos de patrón | Motor v4: criterios técnicos (24h, categoría) + flag usuario |

---

## 3. Arquitectura de Datos

### Tabla `equipment`
- **`has_defined_pattern`** *(bool)*: El usuario declara que este equipo tiene un patrón predecible. El motor lo sub-clasifica técnicamente. **Destino futuro**: migrar a `pattern_type ENUM('inamovible', 'periodico', 'volatil')`.
- **`avg_daily_use_hours`**: Horas de uso diario declaradas por el usuario. Si >= 23.5 AND frecuencia diaria → entra al Tanque Crítico.
- **`usage_frequency`**: Periodicidad declarada (`diario`, `frecuentemente`, `ocasionalmente`, `raramente`, `nunca`). Multiplica el cálculo teórico.
- Campos de activo: `brand`, `model`, `serial_number`, `energy_label`, `is_inverter`, `nominal_power_w`.

### Tabla `equipment_types`
- **`consumption_logic`**: Clasificación termodinámica del tipo (`BASE_LOAD`, `CLIMATE_DEPENDENT`, `CLIMATE_INEFFICIENT`, `CONSTANT_ELASTIC`, `SEASONAL_HABIT`, `BASE_THERMAL_LOSS`). Usado solo para cálculos internos, NO para routing de tanques.
- **`usage_unit`**: Unidad de medida del consumo (`hours`, `cycles`, `people_proportional`). Determina la interfaz de ajuste y el algoritmo de cálculo.
- **`energy_per_cycle`**: kWh por ciclo para línea blanca (Lavarropas, Cafetera, etc.).
- **`social_coefficient`**: Factor de cálculo proporcional a personas (Microondas, Pava eléctrica).
- **`is_thermal_sensitive`**: `true` si el equipo pertenece a la categoría Climatización → entra al Tanque Climático automáticamente.
- **`load_factor`**, **`thermal_efficiency_penalty`**: Ajustes físicos internos del tipo.

### Tabla `equipment_categories`
- 12 categorías orientadas al usuario (Climatización, Refrigeración, Cocina, Lavado, etc.).
- La categoría **no fuerza** el tanque. Solo `is_thermal_sensitive = true` activa el Tanque Climático.
- Las categorías actúan como "calculators enchufables": solo importan si el equipo requiere lógica especial.

### Tabla `equipment_usages`
- Registro bimensual por equipo y factura: `avg_daily_use_hours`, `usage_frequency`, `cycles_per_period`, `is_standby`.
- **`has_defined_pattern` NO se guarda aquí**: vive en `equipment` porque es un atributo del equipo, no del uso.
- **`kwh_reconciled`**: kWh asignado por el motor después de la calibración.
- **`tank_assignment`**: Tanque donde el motor colocó al equipo (1=Certeza, 2=Crítico, 3=Climático, 4=Volátil).

---

## 4. Motor de Energía v4 — Cascada de Tanques

### Cambio fundamental respecto a v3
**v3**: El motor clasificaba equipos usando `consumption_logic` y `determinism_score` del catálogo (seeder). El usuario no tenía control real sobre los tanques.

**v4**: El motor usa **criterios técnicos + decisión del usuario**:
- El usuario decide qué es "patrón fijo". El motor sub-clasifica técnicamente.
- Las categorías especiales (Climatización, Refrigeración) solo determinan el *algoritmo de cálculo*, no el *tanque de entrada*.

### Cascada de clasificación

```
PRE-PROCESO: Calcular _theo_kwh para todos los equipos (ConsumptionAnalysisService)

PASO 0 - Standby (consumo vampiro):
  → Equipos con is_standby = true
  → Consumo = (standby_watts × (24 - horas_activas) × días) / 1000

PASO 1 - Tank Crítico (Tank1BaseService):
  → Criterio: avg_daily_use_hours >= 23.5 AND usage_frequency IN ('diario', 'diariamente')
  → El usuario NO necesita marcar Patrón Fijo (criterio técnico objetivo)
  → Algoritmo interno por categoría:
      Refrigeración  → carga cíclica: (0.25 + people × 0.015) × 24h × días × ajusteClima
      Otras          → _theo_kwh (base load simple)

PASO 2 - Tank Certeza (Tank0CertaintyService):
  → Criterio: has_defined_pattern = true AND NOT Crítico AND NOT Climático
  → El usuario marcó explícitamente que este equipo es predecible
  → Algoritmo: _theo_kwh congelado tal como fue declarado

PASO 3 - Tank Climático (Tank2ClimateService):
  → Criterio: is_thermal_sensitive = true (categoría Climatización)
  → El usuario NO necesita marcar Patrón Fijo (la categoría es la llave)
  → Algoritmo: cooling_days / heating_days de la API × load_factor × room_size_factor
  → Incluye ventiladores: limitados a días con condición estacional activa

PASO 4 - Tank Volátil (Tank3ElasticityService):
  → Criterio: todo lo que no fue asignado en pasos anteriores
  → Algoritmo en 3 pasos:
      A. Deducción social (people_proportional): social_coeff × people × días × freq_factor
      B. Deducción por ciclos (ciclos declarados o estimados del remanente)
      C. Residuo elástico: distribuido proporcionalmente por intensidad y potencia
```

### Invariantes del motor (NO romper)
1. El orden de la cascada es fijo: Standby → Crítico → Certeza → Climático → Volátil.
2. Cada servicio filtra por `tank_assignment === null`.
3. `_theo_kwh` se calcula antes de la cascada.
4. El Tank Volátil NO calcula kWh propios: solo redistribuye `$remainingKwh`.
5. `has_defined_pattern` se persiste en `equipment`, tanto en "Guardar Contexto" como en "Sintonizar".

---

## 5. Interfaz de Usuario — Sintonía Fina v2 (Dos Fases)

### Fase 1: Ajuste Libre (`UsageAdjustmentDetail.vue`)
- Vista plana por **Ambiente** (no por tanque). El usuario no sabe de tanques.
- Inputs adaptativos por tipo de equipo:
  - `hours` → slider de horas + toggle de minutos + selector de frecuencia
  - `cycles` → contador + sugerencia de ciclos si hay historial
  - `people_proportional` → selector de frecuencia (calibra el coeficiente automático)
- Toggle **Patrón Fijo / Uso Variable** para declarar predictibilidad.
- Consumo por equipo calculado en tiempo real con `kwhMap` (computed reactivo).
- Equipos en "No se usó" se atenúan visualmente.

### Fase 2: Resultados del Motor (`EngineResults.vue`)
- Visualización de 4 tanques con kWh total y top 5 equipos.
- Balance: kWh facturado vs kWh calculado vs remanente no asignado.

---

## 6. Correcciones Técnicas Aplicadas en v4

| Área | Corrección |
|---|---|
| Frontend | `calculateKwh()` wrapeado en `kwhMap` computed para garantizar reactividad al mover sliders |
| Frontend | `has_defined_pattern` inicializado desde `item.usage.has_defined_pattern` (no del seeder) |
| Frontend | Nombres largos de equipos usan `break-words` (no `truncate`) |
| Frontend | Precisión de kWh: `toFixed(3)` para consumos < 1 kWh (evita mostrar "0.0") |
| Backend | `ConsumptionAnalysisService`: `people_proportional` ahora multiplica por `frequency_factor` |
| Backend | `getDaysByFrequency`: default corregido de 0.60 a 1.0 (diario = 100%) |
| Backend | `calibrateAndShowResults`: guarda `has_defined_pattern` además de los datos de uso |
| Backend | `Máquina de Afeitar`: `usage_unit` corregido a `hours` (era incorrecto `cycles`) |
| Catálogo | `MasterCleanCatalogueSeeder`: Máquina de Afeitar → `usage_unit = 'hours'` |

---

## 7. Próximos Pasos

- [x] Implementar las 12 Categorías Universales.
- [x] Cargar datos reales de calibración para "Casa 27".
- [x] Vista de ajuste plana por Ambiente (Fase 1 sin tanques).
- [x] Motor v4: clasificación por comportamiento + decisión de usuario.
- [x] Reactividad en tiempo real del consumo por slider.
- [ ] **ARQUITECTURA TARGET**: Migrar `has_defined_pattern boolean` → `pattern_type ENUM('inamovible', 'periodico', 'volatil')` + CategoryCalculators enchufables (ver `rules.md § 7`).
- [ ] Implementar curva de carga variable para equipos Inverter.
- [ ] Crear Analizador de Capacidad (comparar frigorías vs m² de habitación).
- [ ] Agregar Categoría Calefacción con motor climático propio (HDD).
- [ ] Agregar Termotanque con lógica de pérdida térmica independiente.
- [ ] Refactorizar Analizador de Hábitos para sugerir cambios basados en Tank Volátil.

---

**Estado del Proyecto**: Motor v4 funcional. Clasificación por comportamiento declarado (usuario) + criterios técnicos (24h, categoría Climatización). Interfaz de ajuste por ambientes con reactividad en tiempo real. Arquitectura target (calculators enchufables) documentada para próxima sesión.
