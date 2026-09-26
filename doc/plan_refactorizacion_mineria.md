# 🏔️ Plan de Refactorización: ModoAhorro → Campamentos Mineros
## *Basado en auditoría completa del sistema — Branch: `feature/mineria-compre-local`*

---

## 📋 0. Decisiones de Diseño (Preguntas Respondidas)

### 0.1 ¿Qué pasa con los perfiles Hogar / Comercio / Industria?

**Decisión:** NO se eliminan. Se **extiende** el sistema con nuevos tipos mineros.

El archivo maestro `config/entity_types.php` define los tipos en un mapa PHP. La arquitectura es Open/Closed: agregar un tipo nuevo no rompe nada existente.

| Tipo Actual | Estado en Minería |
| :--- | :--- |
| `hogar` | Se mantiene para uso residencial (producto base) |
| `oficina` | ✅ **Se reutiliza directamente** para pabellones administrativos mineros |
| `comercio` | Se mantiene para uso comercial (producto base) |
| **`pabellon`** (nuevo) | Pabellón de alojamiento minero (dormitorios, 24/7, sin horario comercial) |
| **`campamento`** (futuro) | Entidad paraguas que agrupa múltiples pabellones |

### 0.2 ¿Dejamos "Oficina"? ¿Existen pabellones administrativos?

**Decisión: SÍ, se mantiene y se adapta a minería sin tocar el código.**

Todo campamento minero tiene su equivalente de oficina:
* **Sala de Control / Sala de Supervisión** → `CorporateOfficeProfile` ya aplica perfectamente.
* **Enfermería / Guardia Médica** → Oficina con equipos de salud.
* **Oficinas de RRHH / Seguridad** → Perfil oficina estándar.
* **Sala de Comunicaciones** → `Servidor / Rack IT` ya está catalogado (150-2000W, `BASE_LOAD`).

El `CorporateOfficeProfile` ya modela: *"Horario administrativo estricto, alta densidad de puestos informáticos, servidores y climatización por zonas"*. **Es la definición exacta de una oficina de campamento minero.**

Lo único que cambia: el `staff_count` ahora es la dotación del turno administrativo, y `service_turns` mapea a turnos de guardia.

### 0.3 ¿Cómo se llama `Invoice` en contexto minero?

**Decisión:** El **modelo Laravel `Invoice` se mantiene** (renombrarlo implicaría cascada de cambios en 8+ servicios). Lo que cambia es la **capa de presentación y el vocabulario del usuario**.

| Contexto | Nombre de usuario | Modelo interno |
| :--- | :--- | :--- |
| Residencial / Comercial | "Factura" | `Invoice` |
| **Campamento Minero** | **"Lectura de Tablero"** / **"Registro de Consumo"** | `Invoice` (mismo modelo) |

**Campos a agregar por migración (no rompen lo existente):**
| Campo nuevo | Tipo | Descripción |
| :--- | :--- | :--- |
| `source_type` | ENUM | `'mine_grid'`, `'generator'`, `'solar_hybrid'` |
| `shift_code` | string | Código de turno (`'A'`, `'B'`, `'C'`, `'D'`) |
| `demand_kw_peak` | float | Demanda pico registrada en el período |
| `generator_hours` | float | Horas de uso de grupo electrógeno |
| `occupancy_count` | int | Dotación activa del pabellón en el período |

**En la UI (Vue 3):** Solo cambia el label. La prop `invoice_number` se muestra como "N° de Lectura", `start_date`/`end_date` como "Período del Turno", `total_amount` como "Costo Asignado al Módulo".

### 0.4 ¿Cómo implementamos el sistema de recomendación de reemplazo?

**Buena noticia:** `ReplacementService` **ya existe** y ya hace exactamente esto.

**Cómo funciona actualmente:**
1. El cliente carga sus equipos con marca/modelo/antigüedad.
2. `ReplacementService::generateOpportunities()` compara el equipo real contra el catálogo `equipment_benchmarks` (DB).
3. Calcula `potential_savings_kwh`, `payback_months` y genera un `verdict`.
4. Si el equipo tiene >10 años, aplica penalización +15%. Si tiene etiqueta energética mala, +10%.

**Lo que hay que agregar para minería:**
* Nuevos registros en la tabla `equipment_benchmarks` con equipos de alta montaña eficientes:
  * "Convector Eléctrico 1500W con termostato" vs "Panel infrarrojo onda larga 800W"
  * "Termotanque eléctrico 3000W" vs "Calefón solar tubos de vacío + respaldo eléctrico 1000W"
  * "Estufa de resistencia sin termostato" vs "Calefactor programable con sensor de presencia"
* Nuevas `equipment_categories` para minería (ver Sprint 1).
* Adaptar el `verdict` para mostrar equivalentes en litros de diésel en vez de solo ARS.

---

## 🔍 1. Hallazgos Clave de la Auditoría

### Lo que YA EXISTE y se reutiliza sin tocar:

| Componente | Archivo | Relevancia para Minería |
| :--- | :--- | :--- |
| `TURNS_BASED` logic | `Tank3ElasticityService` | Turnos A/B/C mineros (14x14, 7x7) — ya implementado |
| `ThermalProfileService` | `app/Services/ThermalProfileService.php` | `sheet_metal_no_insulation = -15` ya penaliza pabellones metálicos sin aislación. Factor E = 1.8 = máximo consumo |
| `CorporateOfficeProfile` | `app/Domain/Commercial/Profiles/Office/` | Aplicable sin cambios a pabellones administrativos |
| `Servidor / Rack IT` | `MasterCleanCatalogueSeeder` | 150-2000W, `BASE_LOAD`, det=0.98 — sala de servidores de mina |
| `ClimateService` + Open-Meteo | `app/Services/ClimateService.php` | Ya trae `wind_speed`, `shortwave_radiation`, `sunshine_duration` de coordenadas andinas |
| `Tank2ClimateService` | `app/Services/Tanks/Tank2ClimateService.php` | `BASE_TEMP_HEATING = 18°C` → en cordillera casi todos los días son `heating_days` → motor ya lo calcula |
| `ReplacementService` | `app/Services/ReplacementService.php` | Motor de ROI de reemplazo ya funcional — solo necesita benchmarks mineros |
| `MaintenanceService` | `app/Services/MaintenanceService.php` | Tareas de mantenimiento con vencimiento — aplicable a equipos de campamento |
| `SolarWaterService` (ΔT real) | `app/Services/Solar/SolarWaterService.php` | A -15°C cordillera: ΔT = 62°C — mayor ahorro demostrado automáticamente |
| `SolarPowerService` | `app/Services/Solar/SolarPowerService.php` | Solo ajustar HSP de 4.5 → 6.5 para Puna sanjuanina |
| `extra_attributes` JSON en Equipment | Modelo `Equipment` | Metadata industrial libre: serial ATEX, certificación de seguridad, etc. |

### Lo que FALTA y debe crearse:

| Componente | Esfuerzo | Sprint |
| :--- | :--- | :--- |
| Tipo de entidad `pabellon` en `config/entity_types.php` | Bajo | Sprint 1 |
| `MiningCampProfile.php` en Domain | Medio | Sprint 1 |
| Categorías de equipo industriales (Compresores, Calderas, Industrial) | Medio | Sprint 1 |
| Tipos de equipo mineros en seeder | Medio | Sprint 1 |
| Migración `Invoice`: `source_type`, `shift_code`, `occupancy_count` | Bajo | Sprint 1 |
| Migración `Entity`: campo `camp_shift_type` (14x14 / 7x7) | Bajo | Sprint 1 |
| Benchmarks de equipos eficientes para alta montaña | Medio | Sprint 2 |
| UI: Labels "Lectura de Tablero" en vistas mineras | Bajo | Sprint 2 |
| Seeder "Campamento Cordillera Sanjuanina" (demo) | Bajo | Sprint 3 |
| Vista Vue 3 Dashboard Minero (modo oscuro industrial) | Alto | Sprint 3 |

---

## 🗓️ 2. Plan de Sprints de Refactorización

### Sprint 0 — Pre-Sprint: Configuración de Rama (YA HECHO ✅)
- [x] Rama `feature/mineria-compre-local` creada.
- [x] `doc/plan_hackaton.md` guardado.
- [x] `doc/informe_postulacion_mineria.md` guardado y actualizado.
- [x] `doc/plan_refactorizacion_mineria.md` (este archivo) creado.

---

### Sprint 1 (Oct 19 – Oct 25): Fundamentos del Modelo Minero

**Objetivo:** El sistema reconoce campamentos mineros como entidades de primera clase.

#### 1.1 Nuevo tipo de entidad `pabellon` en `config/entity_types.php`
```php
'pabellon' => [
    'label'              => 'Pabellón de Campamento',
    'icon'               => 'building-2',
    'has_business_hours' => false,        // 24/7 como hogar
    'default_rooms'      => [
        'Dormitorios',
        'Baños y Vestuarios',
        'Sala de Estar / Comedor',
        'Portátiles',
        'Temporales',
    ],
    'recommendations' => [
        'solar_panels'     => true,
        'water_heater'     => true,   // Calefón solar tubos de vacío
        'replacements'     => true,
        'standby_analysis' => true,
        'maintenance'      => true,
        'thermal'          => true,   // CRÍTICO en alta montaña
        'vacation'         => false,  // No aplica en campamento
        'grid_optimization'=> false,  // No hay tarifas horarias en mina
    ],
]
```

> **Nota de escalabilidad:** `oficina` se mantiene como está. Pabellones administrativos simplemente usan `type = 'oficina'` con `usage_type = 'mining_admin'`.

#### 1.2 Nuevo `MiningCampProfile.php`
- Crear en `app/Domain/Commercial/Profiles/Mining/MiningCampProfile.php`.
- `getCategoryKey()` → `'pabellon'`
- `getCriticalCategories()` → `['Refrigeración', 'Conectividad y Seguridad', 'Calefacción Industrial']`
- `getStandbyMultiplier()` → `1.05` (campamento tiene menos vampiros que oficina)
- `getThermalSensitivity()` → `1.50` (alta montaña, pabellón metálico sin aislación)
- `getDefaultShiftType()` → `'14x14'`
- Registrar en `CommercialProfileRegistry`.

#### 1.3 Nuevas Categorías de Equipos Mineros (seeder)
```
CALEF_IND  → Calefacción Industrial
AGUA_IND   → Agua y Bombeo Industrial  
COMPR      → Compresores y Aire Industrial
COCINA_IND → Cocina y Alimentación Industrial
LAVADO_IND → Lavado Industrial
ILUM_IND   → Iluminación Industrial
SEGURIDAD  → Seguridad y Detección (nueva)
```

#### 1.4 Nuevos Tipos de Equipos Mineros (seeder)
| Equipo | Potencia | Categoría | Tank | Logic |
| :--- | :--- | :--- | :--- | :--- |
| Convector Eléctrico de Pared | 500-2000W | CALEF_IND | T2 | `CLIMATE_DEPENDENT` |
| Panel Radiante Infrarrojo | 400-1500W | CALEF_IND | T2 | `CLIMATE_DEPENDENT` |
| Caldera a Gas Propano | 5000-30000W | CALEF_IND | T1 | `BASE_THERMAL_LOSS` |
| Traceado Eléctrico (por metro) | 20-30W/m | CALEF_IND | T1 | `BASE_LOAD` |
| Termotanque Industrial 300L | 3000-6000W | AGUA_IND | T1 | `BASE_THERMAL_LOSS` |
| Bomba Centrífuga Industrial | 1500-7500W | AGUA_IND | T1 | `TURNS_BASED` |
| Compresor de Tornillo | 7500-30000W | COMPR | T1 | `CONTINUOUS_COMMERCIAL` |
| Cocina Industrial (Anafe Gas) | 3000-12000W | COCINA_IND | T3 | `TURNS_BASED` |
| Lavarropas Industrial | 2000-5000W | LAVADO_IND | T3 | `TURNS_BASED` |
| Luminaria LED Industrial | 100-400W | ILUM_IND | T0 | `BASE_LOAD` |
| Detector de Gas / CO | 5-15W | SEGURIDAD | T0 | `BASE_LOAD` |
| UPS / Grupo Electrógeno | 5000-50000W | — | meta | `BASE_LOAD` |

#### 1.5 Migración: Campos mineros en `invoices`
```php
Schema::table('invoices', function (Blueprint $table) {
    $table->enum('source_type', ['mine_grid','generator','solar_hybrid'])->nullable();
    $table->string('shift_code')->nullable();       // 'A', 'B', 'C', 'D'
    $table->float('demand_kw_peak')->nullable();    // kW demanda pico
    $table->float('generator_hours')->nullable();   // Horas de generador
    $table->integer('occupancy_count')->nullable(); // Personas en el período
});
```

#### 1.6 Migración: Campos mineros en `entities`
```php
Schema::table('entities', function (Blueprint $table) {
    $table->enum('camp_shift_type', ['14x14','7x7','30x10','custom'])->nullable();
    $table->integer('camp_capacity')->nullable();   // Capacidad máxima del pabellón
    $table->float('floor_area_m2')->nullable();     // m² del módulo (ya puede existir)
    $table->enum('module_type', ['prefab','container','permanent'])->nullable();
});
```

**Tests a escribir:** `PabellonEntityTest`, `MiningInvoiceFieldsTest`

---

### Sprint 2 (Oct 26 – Nov 01): Motor de Buenas Prácticas y Benchmarks

**Objetivo:** El motor calcula la Línea Base de Consumo Responsable por pabellón y compara con la lectura real.

#### 2.1 Adaptar `SolarPowerService` para Alta Montaña
```php
// En SolarPowerService.php, cambiar la constante o hacerla configurable:
const PEAK_SUN_HOURS_ANDEAN = 6.5;   // Puna sanjuanina vs 4.5 urbano
const PANEL_POWER_W = 550;           // Sin cambio
const SYSTEM_EFFICIENCY = 0.82;      // Levemente mejor en altitud (aire más limpio)
```

#### 2.2 Adaptar `SolarWaterService` para Costo en Diésel
```php
// Agregar nuevo método:
public function calculateMiningROI(int $people, float $dieselCostPerLiter, float $minTemp): array
{
    // Usa el mismo ΔT real de la cordillera
    // Convierte el ahorro de kWh a litros de diésel evitados
    // Factor: 1 litro diésel = ~3.57 kWh (rendimiento 0.28 L/kWh)
    $dieselEquivalent = $savedKwh * 0.28;
    $dieselSavings = $dieselEquivalent * $dieselCostPerLiter;
    ...
}
```

#### 2.3 Benchmarks de Equipos Eficientes para Alta Montaña
Agregar registros en `equipment_benchmarks` (tabla usada por `ReplacementService`):

| Equipo Actual | Equipo Recomendado | Ahorro Estimado | Observación Minera |
| :--- | :--- | :--- | :--- |
| Convector resistencia 1500W | Panel infrarrojo onda larga 900W | 40% | Más eficiente con puertas que se abren frecuente |
| Termotanque eléctrico 3000W/150L | Calefón solar tubos de vacío + eléctrico 1000W | 70-80% | Viable en cordillera: irradiancia 6-7 kWh/m²/día |
| Lavarropas doméstico 2500W | Lavarropas industrial EcoWash 3000W | 30% por ciclo | Mejor aprovechamiento de carga |
| Estufa sin termostato 1500W | Estufa programable con sensor 1200W | 35% | Evita derroche en módulo vacío |
| Luminaria halógena 300W | LED industrial 100W | 67% | + vida útil mayor en frío |

#### 2.4 Motor de Línea Base (`BaselineEngine`)
Nuevo servicio `app/Services/Mining/BaselineEngine.php`:
```php
class BaselineEngine
{
    /**
     * Calcula el consumo esperado bajo buenas prácticas
     * dada la dotación del turno y el catálogo de equipos del pabellón.
     */
    public function calculateBaseline(
        Entity $pabellon,
        array $shiftSchedule,    // ['faena' => ['07:00','19:00'], 'descanso' => ['19:00','07:00']]
        int   $occupancyCount,   // Personas en el período
        int   $days              // Días del período
    ): array {
        // Por equipo: usa su potencia × horas responsables × días
        // Horas responsables = horas de uso ECO durante turno de faena + horas plenas en descanso
        // Output: ['baseline_kwh' => float, 'by_equipment' => array, 'by_room' => array]
    }

    /**
     * Calcula el desvío entre la línea base y la lectura real del tablero.
     */
    public function calculateDeviation(float $baseline, float $actual): array
    {
        $deltaKwh   = $actual - $baseline;
        $deltaPct   = $baseline > 0 ? ($deltaKwh / $baseline) * 100 : 0;
        $liters     = $deltaKwh * 0.28;                     // Factor diésel
        $co2Kg      = $deltaKwh * 0.27;                     // Factor CO₂
        $usdCost    = $liters * config('mining.diesel_usd_per_liter', 1.35);

        return [
            'delta_kwh'   => round($deltaKwh, 1),
            'delta_pct'   => round($deltaPct, 1),
            'liters_wasted' => round($liters, 1),
            'co2_kg'      => round($co2Kg, 1),
            'usd_wasted'  => round($usdCost, 2),
            'verdict'     => $this->getVerdict($deltaPct),  // 'OK', 'WARN', 'CRITICAL'
        ];
    }
}
```

#### 2.5 Sistema de 4 Salidas de Valor (automatizado)
Nuevo servicio `app/Services/Mining/DeviationOutputService.php`:
```php
class DeviationOutputService
{
    // Genera las 4 salidas según el desvío detectado:
    public function generate(array $deviation, int $consecutivePeriods): array
    {
        return [
            'training'    => $this->buildTrainingReport($deviation),   // 📚 Siempre
            'penalty'     => $consecutivePeriods >= 3
                              ? $this->buildPenaltyRecord($deviation)  // ⚠️ Si reiterado
                              : null,
            'green_wave'  => $deviation['delta_pct'] <= 5
                              ? $this->buildGreenBadge()               // 🌿 Si cumple
                              : null,
            'replacement' => $this->buildReplacementROI($deviation),   // 🔧 Si estructural
        ];
    }
}
```

**Tests a escribir:** `BaselineEngineTest`, `DeviationOutputServiceTest`

---

### Sprint 3 (Nov 02 – Nov 07): Dashboard Minero y Seeder de Demo

**Objetivo:** Demo navegable para el pitch del 18 de noviembre.

#### 3.1 Seeder "Campamento Veladero – Demo Hackatón"
```
Campamento: "Campamento Base Veladero" (Coord: -29.35, -70.05)
├── Pabellón A-01: 40 personas, turno 14x14, módulo prefab
│   ├── 20 × Convector Eléctrico 1500W
│   ├── 2  × Termotanque Industrial 300L
│   ├── LED Industrial pasillo × 8
│   └── Desvío simulado: +65% (CRITICAL) → activa las 4 salidas
├── Pabellón B-02: 35 personas, turno 14x14, módulo container
│   ├── 18 × Panel Radiante 900W (equipo más eficiente)
│   ├── 1  × Calefón Solar Tubos de Vacío
│   └── Desvío simulado: -3% (OK) → Onda Verde 🌿
├── Pabellón C-03: 38 personas, turno 7x7
│   └── Desvío simulado: +107% (CRITICAL + reiterado) → activa Penalización ⚠️
└── Oficina Supervisión: 8 personas (tipo 'oficina')
    ├── 6 × PC de Escritorio 250W
    ├── 2 × Servidor Rack IT 400W
    └── 1 × Aire VRF 6000W (único caso de clima en oficina)
```

#### 3.2 Vista Vue 3: `resources/js/Pages/Mining/Dashboard.vue`
- **Diseño:** Modo oscuro industrial (fondo `slate-900`, acentos en amber/orange).
- **KPIs en tiempo de compilación:**
  - `🔴 Pabellones en desvío crítico: 2`
  - `⚡ Diésel desperdiciado hoy: 286 L`
  - `🌿 Pabellones con Onda Verde: 1`
  - `💰 USD evitados este mes si se corrige: $11.832`
- **Componentes:**
  - `PavillionCard.vue`: Tarjeta por pabellón con medidor de desvío (gauge visual).
  - `BaselineComparisonChart.vue`: Barra horizontal Estimado vs Real.
  - `DeviationOutputPanel.vue`: Las 4 salidas de valor según el desvío.

#### 3.3 Adaptación de labels en UI existente
En vistas mineras (`pabellon` type):
- "Factura" → "Lectura de Tablero"
- "Monto Total" → "Costo Asignado al Módulo (Diésel Equivalente)"
- "Distribuidora" → "Fuente de Suministro" (Mine Grid / Generador / Solar)
- "N° de Factura" → "N° de Lectura / ID de Período"

---

### Sprint 4 (Nov 08 – Nov 09): Entregables Oficiales del Hackatón

#### Entregables requeridos por las bases:
- [ ] **PDF de 5 carillas** usando `doc/informe_postulacion_mineria.md` como guión.
- [ ] **Video pitch ≤5 min:** Demo en vivo → crear campamento → cargar lectura de tablero → ver desvío → ver las 4 salidas → recomendación de reemplazo de termotanque por solar.
- [ ] **Declaración de APIs e IA:** Open-Meteo (clima), ningún modelo de lenguaje en producción, algoritmos propios de física térmica.

---

## 🔧 3. Decisiones Técnicas de Arquitectura

### 3.1 ¿Cómo escalar a futuro sin romper el sistema base?

```
config/entity_types.php
├── hogar           → ModoAhorro Residencial (sin tocar)
├── oficina         → ModoAhorro Oficina / Pabellón Admin Minero
├── comercio        → ModoAhorro Comercial (sin tocar)
├── pabellon  [NEW] → ModoAhorro Mining Camp
└── (futuro) campamento_industrial → Planta de procesamiento
```

La clave es que `app/Domain/Commercial/Registry/CommercialProfileRegistry.php` auto-descubre perfiles. Agregar `MiningCampProfile` es una sola línea de registro.

### 3.2 ¿Cómo se nombran las entidades en minería?

| Concepto Minero | Modelo en BD | Tipo / Campo |
| :--- | :--- | :--- |
| Campamento completo | `Entity` | `type = 'campamento'` (futuro) o agrupación por locality |
| Pabellón de alojamiento | `Entity` | `type = 'pabellon'` |
| Pabellón administrativo / Oficina | `Entity` | `type = 'oficina'`, `usage_type = 'mining_admin'` |
| Módulo / Sección del pabellón | `Room` | `name = 'Dormitorios A-01'` |
| Equipo instalado | `Equipment` | Con tipo del catálogo minero |
| Lectura de tablero (quincena) | `Invoice` | `source_type = 'generator'`, `shift_code = 'A'` |

### 3.3 Supresión de UI irrelevante para minería

En el tipo `pabellon`, las siguientes secciones de recomendaciones se desactivan:
- ❌ `vacation` → No hay "vacaciones" en un campamento minero 24/7.
- ❌ `grid_optimization` → No hay tarifas horarias en red privada de mina.
- ❌ `dynamic_pricing` → Sin tarifa variable.

### 3.4 `UtilityCompany` → `SupplySource` en contexto minero

Para campamentos, el `proveedor_id` del contrato mapea a la fuente de suministro:
- `"Generador Caterpillar 500kVA"` → `source_type = 'generator'`
- `"Subestación MINA-001"` → `source_type = 'mine_grid'`
- `"Sistema Híbrido Solar+Diesel"` → `source_type = 'solar_hybrid'`

El seeder agrega estas "empresas" ficticias en `utility_companies` para el demo.

---

## 📊 4. Resumen de Brechas y Esfuerzo

| Tarea | Sprint | Archivos Principales | Esfuerzo |
| :--- | :--- | :--- | :--- |
| Tipo `pabellon` en entity_types.php | S1 | `config/entity_types.php` | 🟢 Bajo |
| `MiningCampProfile.php` | S1 | `app/Domain/Commercial/Profiles/Mining/` | 🟡 Medio |
| Categorías equipos industriales (seeder) | S1 | `MasterCleanCatalogueSeeder.php` | 🟡 Medio |
| Tipos equipos mineros (seeder) | S1 | `MasterCleanCatalogueSeeder.php` | 🟡 Medio |
| Migración campos en `invoices` | S1 | Nueva migración | 🟢 Bajo |
| Migración campos en `entities` | S1 | Nueva migración | 🟢 Bajo |
| Ajuste constantes `SolarPowerService` | S2 | `Solar/SolarPowerService.php` | 🟢 Bajo |
| `calculateMiningROI()` en SolarWaterService | S2 | `Solar/SolarWaterService.php` | 🟢 Bajo |
| Benchmarks equipos alta montaña | S2 | Seeder `equipment_benchmarks` | 🟡 Medio |
| `BaselineEngine.php` (nuevo servicio) | S2 | `app/Services/Mining/` | 🔴 Alto |
| `DeviationOutputService.php` (nuevo) | S2 | `app/Services/Mining/` | 🟡 Medio |
| Seeder "Campamento Veladero Demo" | S3 | `database/seeders/` | 🟡 Medio |
| Vista `Mining/Dashboard.vue` | S3 | `resources/js/Pages/Mining/` | 🔴 Alto |
| Labels UI para tipo `pabellon` | S3 | Vistas existentes + nuevo tipo | 🟢 Bajo |
| PDF + Video pitch | S4 | doc/ + grabación | 🟡 Medio |

**Total estimado:** ~3 semanas de desarrollo (Sprint 1-3) + 2 días de entregables (Sprint 4).
