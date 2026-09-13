---
description: Arquitectura principal del sistema y motor energético de ModoAhorro.
tags: [arquitectura, motor, energía, stack, servicios, comercial, admin]
last_updated: 2026-08-17
type: architecture
owner: equipo_modoahorro
---

# Documento de Arquitectura del Sistema — ModoAhorro

**Última Actualización:** 2026-08-17  
**Estado:** Activo / En Producción / Beta Cerrada  
**Roles:**
- **Director de Proyecto (Arquitecto):** IA notebookLM
- **Desarrollador Senior (Ejecutor):** IA Antigravity
- **QA & Usuario Final:** Omar

## Para IA
- Siempre consulta este archivo antes de modificar lógica de motor, servicios o estructura de datos.
- Si detectas cambios en el stack tecnológico, notifica y sincroniza con doc/notebook_sync.md.
- Usa los nombres exactos de servicios y perfiles de motor al generar código o documentación.
- Si se actualiza la cascada de tanques, revisa y actualiza los flujos dependientes.

---

## 1. Visión General y Stack Tecnológico

ModoAhorro es una plataforma SaaS de diagnóstico y eficiencia energética basada en software (sin necesidad obligatoria de sensores o hardware físico), capaz de conciliar facturas de servicios públicos reales (luz y gas) contra inventarios de equipamiento, factores climáticos zonales, comportamiento de uso y balances de potencia.

### Stack Tecnológico:
- **Backend:** Laravel 11.x (PHP 8.2+) con arquitectura orientada a Servicios y Dominio (pp/Domain/).
- **Frontend:** Vue.js 3 (Composition API con <script setup>), Inertia.js y Tailwind CSS v4.
- **Base de Datos:** MySQL / SQLite (en tests).
- **APIs Externas:** Open-Meteo / Climate Service (series térmicas y grado-días de refrigeración/calefacción).

---

## 2. Capa de Dominio Comercial y Modularidad de Rubros (App\Domain\Commercial\)

A partir de la versión de agosto 2026, el módulo comercial se estructura mediante el **Patrón Strategy + Registry**, permitiendo añadir rubros y sub-rubros sin tocar el motor central (*Open/Closed Principle*).

`
app/Domain/Commercial/
 ├── Contracts/
 │    └── CommercialProfileInterface.php        <-- Contrato estricto
 ├── Registry/
 │    └── CommercialProfileRegistry.php         <-- Auto-descubrimiento y catálogo
 └── Profiles/
      ├── AbstractModularCommercialProfile.php
      ├── Gastronomy/
      │    ├── GastronomyBaseProfile.php
      │    ├── IceCreamShopProfile.php          <-- Heladería Artesanal (Frío 24/7 + Mantecado)
      │    ├── PizzeriaProfile.php              <-- Pizzería & Rotisería (Hornos + Extracción)
      │    ├── CoffeeShopProfile.php            <-- Cafetería & Bar (Calderas continuas)
      │    └── RestaurantProfile.php            <-- Restaurante tradicional
      ├── Retail/
      │    └── RetailShopProfile.php            <-- Tiendas y locales comerciales
      └── Office/
           └── CorporateOfficeProfile.php       <-- Oficinas y servicios corporativos
`

### Principales Parámetros de los Perfiles Comerciales:
- **StandbyMultiplier:** Factor de carga continua ininterrumpible (ej: Heladerías = 1.35).
- **ThermalSensitivity:** Multiplicador de estrés térmico estival sobre compresores (ej: Heladerías = 1.45).
- **CriticalCategories:** Categorías que entran al balance de línea base 24/7 (Refrigeración comercial, cámaras, servidores).
- **ProcessCategories:** Categorías de elaboración y transformación (Mantecadoras, hornos, extracción).
- **SuggestedAppliances:** Catálogo pre-cargado para acelerar el onboarding de nuevos comercios.

---

## 3. Arquitectura del Motor de Energía por Tanques (Energy Engine)

El motor desglosa el consumo total de la factura en **Tanques Energéticos**:

`
                       ┌──────────────────────────────────────┐
                       │           FACTURA DE LUZ             │
                       └──────────────────┬───────────────────┘
                                          │
    ┌──────────────────┬──────────────────┼──────────────────┬──────────────────┐
    ▼                  ▼                  ▼                  ▼                  ▼
[ TANQUE 0 ]       [ TANQUE 1 ]       [ TANQUE 2 ]       [ TANQUE 3 ]       [ TANQUE 4 ]
Certeza / Standby   Línea Base         Climatización      Proceso /          Variable
(Vampiro 24h)       (Operativa)        (Térmico / Clima)  Elasticidad        (Remanente)
`

1. **Tanque 0 (Certeza / Standby):** Equipos 24h continuos con consumo fijo o pasivo (has_defined_pattern + no Crítico + no Climático).
2. **Tanque 1 (Base Operativa / Crítico):** Equipos con horario fijo de apertura/cierre, turnos o uso continuo diario de 24h.
3. **Tanque 2 (Climatización):** Aires acondicionados y splits condicionados por la temperatura exterior calculada mediante la API climática.
4. **Tanque 3 (Proceso / Elasticidad):** Maquinaria productiva pesada (mantecadoras, hornos, extracción).
5. **Tanque 4 (Variable / Remanente):** Distribución proporcional o cálculo teórico remanente según potencia e intensidad de uso.

### Componentes Nucleares del Motor

#### A. EnergyEngineService (Teórico Puro)
Orquestador principal del motor de energía. Ejecuta la cascada de tanques en orden fijo. Cada servicio de tanque filtra por 	ank_assignment === null para no reprocesar artefactos ya clasificados.

#### B. ConsumptionAnalysisService
Pre-calcula el _theo_kwh de cada equipo antes de la cascada de tanques:
- TURNS_BASED: W × turns × turnDuration × días
- SERVICE_HOURS: W × (closes_at - opens_at) × días

#### C. ClimateService
Integración con APIs meteorológicas (Open-Meteo / Visual Crossing) para inyectar cooling_days y heating_days del período evaluado.

#### D. Capa de Persistencia (Desacoplada)
- **saveContextOnly**: Persiste ajustes del usuario (horas, ciclos, frecuencia, has_defined_pattern) sin ejecutar el motor.
- **calibrateAndShowResults**: Persiste ajustes + ejecuta motor + retorna resultados a EngineResults.vue. Validado por un Gatekeeper de tolerancia (95% - 120%) para asegurar honestidad matemática.

### Clasificación por Tanques (Reglas en Cascada)

| Tanque | Criterio de entrada | Quién decide | Algoritmo interno |
|---|---|---|---|
| **Crítico (Línea Base)** | vg_daily_use_hours >= 24 AND usage_frequency IN ('diario') | Motor (técnico) | Categoría determina el algoritmo: Refrigeración=especial, resto=base load simple |
| **Climático** | category = 'Climatización y Ambiente' | Motor (técnico) | API clima → días activos → ventiladores limitados por condición estacional |
| **Certeza** | has_defined_pattern = true AND no cayó en Crítico/Climático | Usuario | kWh teórico congelado tal como fue declarado |
| **Volátil / Proceso** | Todo lo que no cayó en ninguno anterior | Motor | Cálculo teórico directo sin redistribución forzada |

### Principio de Categorías como Calculators
Las categorías **no fuerzan** el tanque de un equipo — solo determinan el **algoritmo de cálculo** dentro del tanque:
- Un router que se apaga de noche → NO es Crítico (no llega a 24h) → va a Certeza o Volátil.
- La categoría Refrigeración no fuerza Crítico; lo que lo fuerza es que la heladera opere 24h diariamente.
- Agregar una nueva categoría con lógica especial NO requiere modificar los tanques existentes.

### Representación Visual del Exceso (v8)
Cuando el Total Teórico supera a la Factura:
- El exceso se descuenta del Tanque Climático (primera fuente de incertidumbre).
- Se agrega al Tanque Variable como capa oscura adicional (	4_excess).
- Ambas capas se apilan en la misma barra con diferente tono de verde lima.
- El resultado neto de la barra coincide visualmente con la línea de factura (sin zona roja externa).
- La misma lógica aplica en EngineResults.vue, ConsumptionReal.vue y TimeAnalysis.vue.

---

## 4. Flujo de Datos y Workflow de Usuario

### Flujo de Datos

`
1. Entidad     → espacio físico, personas, metros cuadrados, localidad, rubro
2. Equipos     → catálogo por tipo y categoría, potencia, etiqueta energética
3. Factura     → kWh reales del medidor, periodo (start_date → end_date)
4. Clima       → ClimateService inyecta cooling_days y heating_days del periodo
5. Ajuste      → Usuario calibra horas/ciclos/frecuencia y marca Patrón Fijo
6. Motor       → Cascada de tanques → Suma de _theo_kwh → Cálculo de Energía Residual
7. Resultados  → EngineResults.vue → top items por tanque + balance Teórico vs Real
`

### Workflow del Usuario — Sintonía Fina (2 Fases)

El usuario **no conoce los tanques**. Solo ajusta sus equipos.

**Fase 1 — Ajuste Libre (UsageAdjustmentDetail.vue)**
- Vista plana organizada por Ambiente (habitación), no por tanque.
- Por equipo el usuario ajusta: horas/minutos por día, periodicidad, ciclos (si aplica).
- Marca **Patrón Fijo** en equipos con comportamiento predecible y reproducible.
- Los equipos people_proportional (Router, Microondas) tienen selector de Frecuencia para calibrar el coeficiente automático.

**Fase 2 — Sintonizar Motor (EngineResults.vue)**
- El motor calcula el Teórico Puro en tanques.
- Visualización de **Doble Stack**: Los tanques se reordenan visualmente (Certeza -> Variable -> Base -> Clima) para priorizar hábitos.
- **Zona de Exceso**: Sombreado dinámico para todo consumo que supere la línea de factura.
- **Diagnóstico Automático**: Cruce inteligente de días de calor/frío de la API con el exceso detectado.
- **Exceso Absorbido (v8)**: El exceso teórico se descuenta del tanque Climático y se agrega al Variable con dos tonos (lima base + lima oscuro).
- **Proyección Total sin Ajuste**: Tooltip explicativo para el cliente final.
- **Ahorro Solar**: Módulo interactivo que dimensiona sistemas fotovoltaicos y térmicos con sliders dinámicos para calcular ROI.

---

## 5. Módulos Especializados del Sistema

### Módulo Consumo Fantasma (Standby) — v8 / v10
- **Ruta**: GET /recomendaciones/consumo-fantasma → Standby.vue
- **Toggle Backend**: POST /recomendaciones/consumo-fantasma/{equipment}/toggle → RecommendationController@toggleStandby → StandbyAnalysisService::toggleEquipmentStandby.
- **Arquitectura de Interfaz**: Panel superior de KPI métricas fijas (shrink-0) con tarjetas dinámicas (*Consumo Actual*, *Costo Estimado*, *Ahorro Logrado/Potencial*) y cuadrícula de equipos con scroll vertical independiente (overflow-y-auto).
- **Filtros de Inclusión**: Excluye iluminación, portátiles, equipos con default_standby_power_w = 0 y equipos inactivos.

### Módulo Perfil de Usuario y Seguridad (v10)
- **Rutas**:
  - GET /perfil → ProfileController@edit → Profile/Edit.vue
  - PUT /perfil → ProfileController@update (nombre, email con reglas de unicidad)
  - PUT /perfil/password → ProfileController@updatePassword (validación de contraseña actual y confirmación)
- **Componente**: Profile/Edit.vue con gestión de credenciales, avatar, rol (*Super Admin / Usuario*), feedback en vivo y resumen de entidades administradas.

### Infraestructura — Arquitectura Master-Detail Split-View (v10)
- **Ruta**: GET /gestion/infraestructura → InfrastructureController@index → Entities/Infrastructure/Index.vue
- **Estructura**: Doble panel con alturas sincronizadas y scrolls independientes (h-full min-h-0 overflow-hidden):
  - *Panel Izquierdo*: Cabecera fija con conteo de ambientes, listado vertical con scroll propio y tarjeta inferior fija del ambiente activo.
  - *Panel Derecho*: Barra de acción fija (*+ Añadir Equipo*, nombre del ambiente) y grilla de equipos en scroll independiente.

### Arquitectura del Panel de Super Administrador (Segmentos & SaaS)
La consola de administración para usuarios con is_super_admin = true está estructurada en **4 Segmentos Independientes**:
1. **Dashboard (LayoutDashboard):** Monitoreo global de la red, cantidad de cuentas, tipos de equipos cargados y MRR mensual.
2. **Configuración & Catálogos (Settings):**
   - **Catálogo Maestro:** Edición de potencias nominales, penalidades térmicas y tanques por defecto.
   - **Modelos Oficiales & Clientes:** Bandeja de aportes comunitarios para homologación.
   - **Matriz de Eficiencia:** Curvas y coeficientes IRAM de consumo.
   - **Benchmarks & ROI:** Modelos de reposición eficiente y enlaces monetizables.
3. **Gestión de Usuarios (Users):**
   - **Cuentas & Roles:** Control de usuarios, reseteo de claves y permisos de Super Admin.
   - **Reseteos de Clave:** Generación asistida de enlaces temporales de acceso y forzado directo de contraseñas.
   - **Pagos & Suscripciones:** Modelo 100% centrado en el usuario (1 Usuario = 1 Plan) con control de cupos de entidades (max_entities) y extensiones manuales de vigencia.
4. **APIs & Conectores (KeyRound):**
   - Conectores externos con Mercado Libre (búsqueda y precios), CAMMESA/ENRE (tarifas mayoristas) y Open-Meteo (grados-día).
   - Gestión de claves públicas y secretos de Webhooks.

---

## 6. Estructura de la Base de Datos (	ablas/)

- entities: Entidades físicas (Hogares, Oficinas, Comercios) con campos usiness_category y usiness_subcategory.
- ooms: Ambientes físicos asociados a cada entidad.
- equipment: Artefactos físicos instalados con potencias, horas de uso y patrones.
- equipment_types: Catálogo maestro de tipos de equipos y tanques por defecto.
- equipment_categories: Agrupaciones lógicas de artefactos.
- equipment_models: Modelos comerciales homologados y aportes de comunidad con autocompletado.
- equipment_benchmarks: Modelos de mercado de referencia para cálculo de ROI y recomendaciones.
- energy_label_coefficients: Multiplicadores de etiquetas de eficiencia energética (A+++ a G).
- plans: Planes SaaS (Gratuito, Premium, Enterprise) con precios, cupos de entidades y tipos permitidos.
- password_reset_tokens: Tokens de 64 caracteres para recuperación asistida de cuentas.
- contracts: Contratos de suministro eléctrico con distribuidoras.
- invoices: Facturas eléctricas vinculadas con consumo en kWh, cargos fijos y períodos.
- equipment_usages: Registro histórico conciliado por artefacto y tanque asignado.

---

## 7. Principios de Diseño y Estándares UI

- **Física-First**: El motor simula comportamiento termoeléctrico, no solo suma números.
- **Usuario-Árbitro**: El usuario valida patrones. El motor sub-clasifica técnicamente.
- **Categorías Enchufables**: Cada categoría especial tiene su propio calculator interno. Escalable por diseño.
- **Estética Premium (Tailwind v4 Rules)**: Gradientes sutiles (g-gradient-to-br from-slate-900 to-slate-800), bordes ounded-[24px]/[32px], glassmorphism (ackdrop-blur-md bg-white/10 border border-white/20). Colores semánticos globales: energy-solar (amber-500), energy-success (emerald-500), energy-danger (rose-500). Ver .agents/skills/visual/SKILL.md.
- **Visualización Honesta**: El exceso teórico se integra limpiamente al Uso Variable (dos tonos), evitando zonas rojas que alarmen sin contexto.
- **Open/Closed**: Agregar un nuevo tipo de equipo o categoría no requiere modificar el código existente.
- **Confianza (Testing)**: Cambios en el motor se validan con suite de pruebas para evitar derivas en la distribución.

---

## 8. Preparación Futura: Multi-Vector (Luz + Gas) e IoT

- **Multi-Vector:** Estructura preparada para el enum EnergySource (ELECTRICITY en kWh, NATURAL_GAS en m³, SOLAR).
- **IoT & Medidores Inteligentes:** La arquitectura permite que los tanques sustituyan o calibren su cálculo matemático con telemetría real proveniente de APIs de sensores externos.
- **Arquitectura Target de Patrones**: Migrar has_defined_pattern boolean → pattern_type ENUM('inamovible', 'periodico', 'volatil') y formalizar los CategoryCalculators como clases independientes registradas en un dispatcher dentro de EnergyEngineService.
