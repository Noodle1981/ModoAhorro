# Arquitectura del Software: ModoAhorro

## 1. Stack Tecnológico
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue.js 3 con Inertia.js (Monolito Moderno)
- **Estilos**: Tailwind CSS v4 (Aesthetics Premium)
- **Base de Datos**: Relacional (SQLite en desarrollo / MariaDB en prod)
- **Comunicación**: Protocolo Inertia.js — sin APIs REST tradicionales, el servidor renderiza el estado inicial y el cliente navega sin recargas.

---

## 2. Componentes Nucleares (The Engine)

### A. EnergyEngineService (Teórico Puro)
Orquestador principal del motor de energía. Ejecuta la cascada de tanques en orden fijo:
Standby → TankCrítico → TankCerteza → TankClimático → TankVariable.

#### A.1 Engine Profiles (Estrategias de Negocio) [NUEVO]
Inyecta lógica específica según el rubro de la entidad para no saturar el orquestador:
- **`GastronomyEngineProfile`**: Lógica de turnos y comensales.
- **`RetailEngineProfile`**: Lógica de horarios de atención y clientes.
- **`OfficeEngineProfile`**: Lógica de jornada laboral y empleados.

### B. ConsumptionAnalysisService
Pre-calcula el `_theo_kwh` de cada equipo antes de la cascada de tanques.
Contiene los algoritmos de cálculo específicos:
- **Por horas**: `W × h × días × loadFactor × labelCoeff`
- **Por ciclos**: `energy_per_cycle × ciclos_declarados`
- **Proporcional a personas**: `social_coefficient × (people_count | visitors_count) × días × frequencyFactor`
- **Lógicas B2B**:
    - `TURNS_BASED`: `W × turns × turnDuration × días`
    - `SERVICE_HOURS`: `W × (closes_at - opens_at) × días`
- **Heladera especial**: modelo de carga cíclica `(0.25 + people × 0.015) × 24h × días × ajusteClima`

### C. ClimateService
Integración con APIs meteorológicas (Visual Crossing).
- Calcula `cooling_days` y `heating_days` para el periodo de la factura.
- Provee el multiplicador térmico para la fórmula del TankClimático.
- Modo fallback por proximidad si la API no responde.

### D. Servicios de Tanque (Cascada)

```
Tank0CertaintyService  → Tanque Certeza (has_defined_pattern + no Crítico + no Climático)
Tank1BaseService       → Tanque Crítico (24h + diario)
Tank2ClimateService    → Tanque Climático (categoría Climatización)
Tank3ElasticityService → Tanque Variable (Uso variable por hábitos directos, sin distribución artificial)
```

Cada servicio filtra por `tank_assignment === null` para no reprocessar equipos ya asignados.

### E. Capa de Persistencia (Desacoplada)
- **`saveContextOnly`**: Persiste ajustes del usuario (horas, ciclos, frecuencia, `has_defined_pattern`) sin ejecutar el motor. Endpoint liviano.
- **`calibrateAndShowResults`**: Persiste ajustes + ejecuta motor + retorna resultados a `EngineResults.vue`. Validado por un Gatekeeper de tolerancia (95% - 120%) para asegurar honestidad matemática.

---

## 3. Workflow del Usuario — Sintonía Fina (2 Fases)

El usuario **no conoce los tanques**. Solo ajusta sus equipos.

**Fase 1 — Ajuste Libre (`UsageAdjustmentDetail.vue`)**
- Vista plana organizada por Ambiente (habitación), no por tanque.
- Por equipo el usuario ajusta: horas/minutos por día, periodicidad, ciclos (si aplica).
- Marca **Patrón Fijo** en equipos con comportamiento predecible y reproducible.
- Los equipos `people_proportional` (Router, Microondas) tienen selector de Frecuencia para calibrar el coeficiente automático.

**Fase 2 — Sintonizar Motor (`EngineResults.vue`)**
- El motor calcula el Teórico Puro en 4 tanques.
- Visualización de **Doble Stack**: Los tanques se reordenan visualmente (`Certeza -> Variable -> Base -> Clima`) para priorizar hábitos.
- **Zona de Exceso**: Sombreado dinámico (rayas de peligro) para todo consumo que supere la línea de factura.
- **Diagnóstico Automático**: Tarjeta inteligente que cruza los días de calor/frío de la API con el exceso detectado para dar una explicación técnica al usuario.
- **Exceso Absorbido (v8)**: El exceso teórico ya no se visualiza como zona roja. Se descuenta del tanque Climático y se agrega al Visual Variable con dos tonos (lima base + lima oscuro), manteniendo la trazabilidad sin alarmar visualmente. Eliminado el marcador invasivo "FACTURA" de la barra.
- **Proyección Total sin Ajuste**: renombre del antiguo "Consumo Teórico Calculado" con tooltip explicativo para el cliente final.
- **Ahorro Solar**: Módulo interactivo que dimensiona sistemas fotovoltaicos (paneles) y térmicos (agua caliente) con sliders dinámicos para ajustar área y habitantes, calculando el ROI según el combustible actual.
- Muestra top 5 equipos por tanque + Energía Residual (Faltante o Exceso) en la barra de distribución.

---

## 4. Clasificación por Tanques (Motor v4)

### Reglas de entrada (aplicadas en cascada)

| Tanque | Criterio de entrada | Quién decide | Algoritmo interno |
|---|---|---|---|
| **Crítico** | `avg_daily_use_hours >= 24` AND `usage_frequency IN ('diario')` | Motor (técnico) | Categoría determina el algoritmo: Refrigeración=especial, resto=base load simple |
| **Climático** | `category = 'Climatización y Ambiente'` | Motor (técnico) | API clima → días activos → ventiladores limitados por condición estacional |
| **Certeza** | `has_defined_pattern = true` AND no cayó en Crítico/Climático | Usuario | kWh teórico congelado tal como fue declarado |
| **Volátil** | Todo lo que no cayó en ninguno anterior | Motor | Cálculo teórico directo sin redistribución forzada ni "esponja elástica" |

### Principio de Categorías como Calculators
Las categorías **no fuerzan** el tank de un equipo — solo determinan el **algoritmo de cálculo** dentro del tanque.
- Un router que se apaga de noche → NO es Crítico (no llega a 24h) → va a Certeza o Volátil.
- La categoría `Refrigeración` no fuerza Crítico; lo que lo fuerza es que la heladera corre 24h diariamente.
- Agregar una nueva categoría con lógica especial NO requiere modificar los tanques existentes.

### Representación Visual del Exceso (v8)
Cuando el Total Teórico supera a la Factura:
- El exceso se descuenta del Tanque Climático (primera fuente de incertidumbre).
- Se agrega al Tanque Variable como capa oscura adicional (`t4_excess`).
- Ambas capas se apilan en la misma barra con diferente tono de verde lima.
- El resultado neto de la barra coincide visualmente con la línea de factura (sin zona roja externa).
- La misma lógica aplica en `EngineResults.vue`, `ConsumptionReal.vue` y `TimeAnalysis.vue`.

---

## 5. Flujo de Datos

```
1. Entidad     → espacio físico, personas, metros cuadrados, localidad
2. Equipos     → catálogo por tipo y categoría, potencia, etiqueta energética
3. Factura     → kWh reales del medidor, periodo (start_date → end_date)
4. Clima       → ClimateService inyecta cooling_days y heating_days del periodo
5. Ajuste      → Usuario calibra horas/ciclos/frecuencia y marca Patrón Fijo
6. Motor       → Cascada de tanques → Suma de _theo_kwh → Cálculo de Energía Residual
7. Resultados  → EngineResults.vue → top items por tanque + balance Teórico vs Real
```

---

## 6. Principios de Diseño

- **Física-First**: El motor simula comportamiento termoeléctrico, no solo suma números.
- **Usuario-Árbitro**: El usuario valida patrones. El motor sub-clasifica técnicamente.
- **Categorías Enchufables**: Cada categoría especial tiene su propio calculator interno. Escalable por diseño.
- **Estética Premium (Tailwind v4 Rules)**: Gradientes sutiles (`bg-gradient-to-br from-slate-900 to-slate-800`), bordes `rounded-[24px]/[32px]`, glassmorphism (`backdrop-blur-md bg-white/10 border border-white/20`). Colores semánticos globales: `energy-solar` (amber-500), `energy-success` (emerald-500), `energy-danger` (rose-500). Nunca hardcodear variantes cuando corresponde un color semántico.
- **Visualización Honesta**: El exceso teórico se integra limpiamente al Uso Variable (dos tonos), evitando zonas rojas que alarmen sin contexto.
- **Open/Closed**: Agregar un nuevo tipo de equipo o categoría no requiere modificar el código existente.
- **Confianza (Testing)**: Cambios en el motor se validan con suite de pruebas para evitar derivas en la distribución.

### Módulo Consumo Fantasma (Standby) — v8
- **Ruta**: `GET /recomendaciones/consumo-fantasma` → `Standby.vue`
- **Toggle Backend**: `POST /recomendaciones/consumo-fantasma/{equipment}/toggle` → `RecommendationController@toggleStandby` → `StandbyAnalysisService::toggleEquipmentStandby`.
- **Vista**: Listado real de equipos agrupado por categoría. Cada fila tiene: nombre, ambiente, potencia standby, horas en espera, costo mensual, y un switch interactivo.
- **Lógica de Ahorro**: La tarjeta KPI verde alterna dinámicamente entre "Ahorro Potencial" (si no se desenchufó nada) y "Ahorro Logrado" (si algún equipo está desenchufado), calculado desde `totalRealizedSavings`.
- **Filtros de Inclusión**: `StandbyAnalysisService` excluye: iluminación, portátiles, equipos con `default_standby_power_w = 0`, y equipos inactivos.


---

## 7. Arquitectura Objetivo — Próxima Evolución

Ver `rules.md § 7` para el detalle completo.

**Resumen**: Migrar `has_defined_pattern boolean` → `pattern_type ENUM('inamovible', 'periodico', 'volatil')` y formalizar los CategoryCalculators como clases independientes registradas en un dispatcher dentro de `EnergyEngineService`. Esto materializa el principio Open/Closed en código concreto.
