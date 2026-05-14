# Reglas de Desarrollo Antigravity (ModoAhorro)

## 1. Estándares de Vue.js
- **Composition API**: Siempre usa `<script setup>`.
- **Naming**: Los componentes deben usar PascalCase (ej: `EquipmentCard.vue`).
- **Props**: Define siempre las props con `defineProps`.
- **Forms**: Usa exclusivamente `useForm` de `@inertiajs/vue3` para envíos a backend.

## 2. Estándares de Tailwind CSS v4
- **Estética Premium**:
  - Usa gradientes sutiles: `bg-gradient-to-br from-slate-900 to-slate-800`.
  - Bordes suaves: `rounded-[24px]` o `rounded-[32px]`.
  - Glassmorphism: `backdrop-blur-md bg-white/10 border border-white/20`.
- **Colores Semánticos**:
  - `energy-solar`: #f59e0b (amber-500)
  - `energy-success`: #10b981 (emerald-500)
  - `energy-danger`: #ef4444 (rose-500)

## 3. Lógica de Negocio (Laravel)
- **Controllers Delgados**: La lógica compleja vive en `app/Services`.
- **Tipado**: Usa Type Hinting en métodos de servicio y controladores.
- **Modelos**: Mantén los `$fillable` y `$casts` actualizados para evitar errores de asignación masiva.

## 4. Interacción con el Agente (Yo)
- **Planificación**: Antes de cambios grandes, siempre lee `PDR.md` y `ARCHITECTURE.md`.
- **Validación**: Verifica siempre que los cambios no rompan la lógica de los "Tanques" de consumo.
- **Transparencia**: Al terminar una tarea, actualiza `PDR.md`, `ARCHITECTURE.md` y las tablas (D:\ModoAhorro\tablas) si hubo cambios en cada una de ellas para actualizar el notebookLM.

## 5. Roles
- **IA notebookLM** eres el **Director de Proyecto** (Arquitecto).
- **IA Antigravity** eres el **Desarrollador Senior** (Ejecutor).
- **Yo** soy el **Amigo y QA** (Usuario final).

---

## 6. Modelo de Negocio — Motor de Energía por Tanques

### Filosofía Central
El usuario NO conoce los tanques. Solo conoce sus equipos y cómo los usa.
El motor traduce esa información en distribuciones de energía.

### Workflow del Usuario (Sintonía Fina — 2 Fases)

**Fase 1 — Ajuste Libre (Vista por Ambiente)**
- El usuario ajusta horas, minutos y periodicidad de cada equipo.
- Marca como **Patrón Fijo** los equipos cuyo comportamiento es predecible y reproducible
  (puede ser por horas, ciclos, o periodicidad fija).
- No sabe ni necesita saber a qué tanque irá cada equipo.

**Fase 2 — Sintonizar Motor (Distribución por Tanques)**
- El motor clasifica los equipos en 4 tanques según reglas técnicas.
- Muestra el resultado distribuido como resumen de certeza.

---

### Reglas de Clasificación por Tanques

```
TANQUE CRÍTICO (Base Inmutable)
  Criterio de entrada : avg_daily_use_hours >= 24 AND usage_frequency IN ('diario', 'diariamente')
  Sin patrón fijo     : NO entra (el usuario debe validarlo)
  Algoritmo interno   : depende de la CATEGORÍA del equipo
    - Categoría Refrigeración → cálculo especial (carga cíclica + personas + temperatura)
    - Otras categorías (Conectividad, IT, etc.) → cálculo base simple (W × 24h × días)
  Ejemplo real        :
    - Heladera 24h diario    → Crítico (algo. Refrigeración)
    - Router 24h diario      → Crítico (algo. simple)
    - Servidor 24h fines de semana → NO Crítico → Certeza o Variable

TANQUE CLIMÁTICO
  Criterio de entrada : category = 'Climatización y Ambiente'
  Sin patrón fijo     : Entra igual (la categoría es la llave, no el usuario)
  Algoritmo interno   : API clima → días de calor/frío activos en el periodo
    - Aires split, portátiles → días de refrigeración de la API
    - Ventiladores (SEASONAL_HABIT) → limitados a días con condición activa
  Extensible          :
    - Futura categoría 'Calefacción' → su propio motor climático
    - Termotanque → su propia lógica de pérdida térmica

TANQUE CERTEZA (Patrón Fijo declarado)
  Criterio de entrada : has_defined_pattern = true
                        AND NOT (Crítico: 24h + diario)
                        AND NOT (Climatización)
  Algoritmo interno   : kWh teórico congelado tal como lo declaró el usuario
  Ejemplo real        :
    - PC Gamer 8h diario → Certeza
    - TV 6h diario       → Certeza
    - Lavarropas 8 ciclos → Certeza
    - Servidor 24h solo fines de semana + Patrón Fijo → Certeza

TANQUE VARIABLE (Remanente Elástico)
  Criterio de entrada : todo lo que no cayó en los tanques anteriores
  Algoritmo interno   :
    1. Deducción social (people_proportional): coeficiente × personas × días × frecuencia
    2. Deducción por ciclos (lavarropas, cafetera sin ciclos declarados)
    3. Redistribución proporcional del kWh sobrante (factura − suma de tanques fijos)
       ponderada por intensidad y potencia del equipo
```

### Principio de Escalabilidad por Categoría
- Las **categorías** solo importan cuando un grupo de equipos requiere **lógica de cálculo especial**.
- Una categoría sin lógica especial NO fuerza ningún tanque.
- Para agregar una nueva categoría especial (ej: "Impresoras"):
  1. Crear un nuevo `TankImprentaService` con su algoritmo (ej: páginas × personas).
  2. Agregar el filtro `category = 'Impresión'` en el servicio.
  3. Conectarlo al orquestador `EnergyEngineService` en el paso correcto de la cascada.
  4. No tocar nada más del sistema.

### Invariantes del Motor (NO romper)
- El orden de la cascada es: Crítico → Climático → Certeza → Variable.
- Cada servicio filtra por `tank_assignment === null` para no reprocessar equipos ya asignados.
- El `_theo_kwh` se calcula ANTES de ejecutar los tanques (en `ConsumptionAnalysisService`).
- El Tank Variable NO calcula su propio kWh teórico: solo redistribuye el `$remainingKwh`.
- `has_defined_pattern` se persiste en el modelo `Equipment` (no en `EquipmentUsage`).
- Se guarda tanto en "Guardar Contexto" como en "Sintonizar Motor".

---

## 7. Arquitectura Objetivo — Próxima Sesión

> **Estado actual**: implementación funcional con clasificación básica por `has_defined_pattern` + `avg_daily_use_hours`.
> **Objetivo**: migrar al modelo de "Tanks como contenedores + Categories como calculators enchufables".

### Problema que resuelve
El código actual mezcla la **decisión de routing** (a qué tanque va) con la **lógica de cálculo** (cómo se calcula el kWh). Agregar una nueva categoría especial requiere modificar servicios existentes, violando el principio Open/Closed.

### Modelo Target

```
PatternType (tipo explícito, reemplaza el booleano has_defined_pattern)
  ├── INAMOVIBLE   → 24hs continuas + diario (→ TankCrítico)
  ├── PERIODICO    → horas/ciclos con frecuencia definida (→ TankCerteza)
  └── VOLATIL      → sin patrón declarado (→ TankVolatil)

Tanks (contenedores de comportamiento puro)
  ├── TankCritico   ← recibe equipos con pattern_type = INAMOVIBLE
  ├── TankCerteza   ← recibe equipos con pattern_type = PERIODICO
  ├── TankClimatico ← recibe equipos de categoría Climatización (cualquier pattern_type)
  └── TankVolatil   ← recibe equipos con pattern_type = VOLATIL

CategoryCalculators (enchufables dentro de tanks)
  ├── BaseLoadCalculator        → default para TankCritico (Router, Servidor, Cámaras)
  ├── RefrigerationCalculator   → enchufado en TankCritico para categoría Refrigeración
  ├── ClimateCalculator         → motor de TankClimatico (días calor/frío de la API)
  └── ... futuros sin tocar código existente:
        ImprentaCalculator      → ciclos = páginas × personas
        CalefaccionCalculator   → días de frío de la API
        TermotanqueCalculator   → pérdida térmica
        VacationCalculator      → reducción por ausencia del hogar
```

### Cambios requeridos

| Componente | Cambio |
|---|---|
| `equipment` (tabla DB) | Agregar `pattern_type ENUM('inamovible', 'periodico', 'volatil')` reemplazando `has_defined_pattern boolean` |
| `EquipmentType` | Agregar `calculator_class string nullable` para el calculator enchufable |
| `EnergyEngineService` | Reemplazar llamadas directas a servicios por un **Registry dispatcher** que resuelve el calculator según `pattern_type` + `calculator_class` |
| `Tank*Service` | Convertirlos en contenedores genéricos que delegan el cálculo al calculator registrado |
| Frontend (`UsageAdjustmentDetail.vue`) | Reemplazar el toggle binario por un selector de tipo: `INAMOVIBLE / PERIÓDICO / VOLÁTIL` |
| `ConsumptionAnalysisService` | Pre-cálculo de `_theo_kwh` debe respetar el `calculator_class` del tipo |

### Principio guía de la migración
> Migrar de a una categoría por vez sin romper las existentes.
> El `BaseLoadCalculator` es el fallback para cualquier equipo sin `calculator_class` definido.
> El frontend puede mostrar el `pattern_type` como 3 opciones visuales en lugar del toggle actual.

### Nota sobre la complejidad del proyecto
Este proyecto es progresivo por diseño. Cada refactorización descubre simplificaciones.
La arquitectura de calculators enchufables es el destino natural de lo que ya existe —
no es un cambio de paradigma sino una formalización del modelo mental que ya se aplica.

---

## 8. Soporte Comercial (B2B)

### Filosofía B2B
Las entidades comerciales (`comercio`, `oficina`) operan bajo lógicas de logística y producción, no solo habitabilidad.

### Engine Profiles
Cada rubro comercial inyecta un perfil que define:
- Categorías críticas y de proceso.
- Coeficientes sociales específicos (comensales, clientes, empleados).
- Etiquetas de unidad para visitantes.

### Lógicas de Cálculo Industriales
- **`TURNS_BASED`**: Consumo = Potencia × Cantidad de Turnos × Horas por Turno × Días.
- **`SERVICE_HOURS`**: Consumo = Potencia × (Cierre - Apertura) × Días.
- **`CONTINUOUS_COMMERCIAL`**: Consumo 24h con factores de carga industriales (refrigeración pesada).

### UI Semántica B2B
- Las entidades comerciales usan una paleta de **Azul Cobalto / Indigo** para diferenciarse del **Verde Esmeralda** residencial.
- Los campos de `people_count` se adaptan a `Staff` y `Visitantes/Comensales` según el rubro.
