---
description: Registro de desarrollo, decisiones de producto y evolución arquitectónica de ModoAhorro.
tags: [PDR, evolución, arquitectura, historial, decisiones, comercial, admin]
last_updated: 2026-09-25
type: record
owner: equipo_modoahorro
---

# Registro de Desarrollo de Producto (PDR) — ModoAhorro

**Proyecto:** ModoAhorro  
**Versión Actual:** 2.2 (Excelencia y Modernización Backend con Skills Laravel 13)  
**Última Actualización:** 2026-09-25  
**Roles:**
- **Director de Proyecto (Arquitecto):** IA notebookLM
- **Desarrollador Senior (Ejecutor):** IA Antigravity
- **QA & Usuario Final:** Omar

## Para IA
- Consulta este archivo antes de proponer cambios estructurales o de lógica de motor.
- Si resuelves un problema o implementas una nueva versión, documenta el cambio en la tabla de problemas resueltos y en el changelog.
- Sincroniza con doc/ARCHITECTURE.md y doc/notebook_sync.md tras cada actualización relevante.

---

## 1. Historial de Decisiones de Producto (Changelog de Decisiones)

### Tabla Histórica de Problemas Resueltos (v1 — v13)

| Versión | Problema | Solución |
|---|---|---|
| **v1** | Ajustes sobrescribían hábitos globales | has_defined_pattern protege hábitos congelados |
| **v2** | Sin diferenciación tecnológica (Inverter vs On/Off) | is_inverter + energy_label_coefficients |
| **v3** | Motor clasificaba por catálogo, ignorando al usuario | Cascada de tanques basada en comportamiento declarado |
| **v4** | has_defined_pattern binario no distingue tipos de patrón | Motor v4: criterios técnicos (24h, categoría) + flag usuario |
| **v5** | Caja Negra de compresión alteraba datos reales para forzar coincidencia con factura | **Arquitectura Teórico Puro**: Motor calcula consumo real, no comprime, y calcula un **Residual Matemático**. |
| **v6** | Falta de contexto climático en resultados y confusión visual entre tanques | **Diagnóstico Climático**: Inyección de días de calor/frío en resultados. Reordenamiento visual pro-usuario (Certeza->Variable->Base->Clima) y sombreado de exceso (Stripes). |
| **v7** | Módulo Solar estático y desconectado | **Proyecto Solar Interactivo**: Sliders dinámicos para área y habitantes. Cálculo de ahorro por tipo de combustible (Gas/Elec). Layout Single-Page (sin scroll). |
| **v8** | Visualización de exceso confusa y Consumo Fantasma sin interactividad | **Integración Visual de Exceso**: El exceso teórico se absorbe en Uso Variable (dos tonos). Eliminación del marcador FACTURADO. Gráficos de evolución temporal sincronizados. **Consumo Fantasma Interactivo**: Listado real de equipos agrupado por categoría con toggles funcionales. Tarjetas de ahorro potencial vs. ahorro logrado. Cumplimiento estricto de Tailwind v4 Design Rules. |
| **v9** | Expansión al sector comercial (B2B) - Caso Restaurantes/Oficinas | **Soporte Comercial (B2B)**: Inyección de perfiles de motor (Gastronomy, Retail, Office). Nuevas lógicas de cálculo: TURNS_BASED y SERVICE_HOURS. Adaptación de UI a perfiles industriales. |
| **v10** | Usabilidad en pantallas densas, precisión decimal y módulo de perfil | **Evolución UX Integral & Módulo Perfil**: (1) Creación de ProfileController y Profile/Edit.vue para gestión de usuario y seguridad. (2) Arquitectura Master-Detail de doble panel con scroll independiente en Infraestructura. (3) Podio Top 3, etiquetas fluidas (lex-wrap) y precisión de centavos en Coste por Equipo. (4) Normalización de fechas bimestrales y dropdown en Ajuste de Uso. (5) Panel KPI fijo y scroll aislado en Consumo Fantasma. (6) Alineación con Tarifa Valle en Optimización de Horarios. |
| **v11** | Cuellos de botella climáticos, incoherencias de clasificación y servicios duplicados | **Optimización y Refactorización Laravel**: (1) Memoización de consultas climáticas en ConsumptionAnalysisService (suite de tests 6x más rápida: de 30.6s a 4.7s). (2) Corrección termodinámica en ACS removiendo bombas de agua. (3) Desbloqueo de equipos térmicos con patrón fijo en Tank2ClimateService y sincronización de tiers en AnalysisController. (4) Filtro de categorías excluidas en StandbyAnalysisService. (5) Ambientes del sistema desacoplados y centralizados en config/entity_types.php. (6) Eliminación de servicios huérfanos y duplicados garantizando PSR-4 estricto. |
| **v12** | Vulnerabilidades de dependencias y ausencia de cabeceras HTTP de protección | **Blindaje de Seguridad Laravel (Skill laravel-security)**: (1) Actualización de dependencias y resolución del 100% de vulnerabilidades detectadas por composer audit (0 advertencias). (2) Creación y registro del middleware SecurityHeaders inyectando X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN, X-XSS-Protection y Referrer-Policy. (3) Forzado de esquema HTTPS en ambiente de producción en AppServiceProvider::boot(). (4) Validación automatizada de cabeceras de respuesta en la suite de tests. |
| **v13** | Duplicación de lógica de temas de interfaz (~1.000 líneas), ausencia de composables y componentes reutilizables | **Buenas Prácticas de Frontend Vue 3 (Skill ue-best-practices)**: (1) Creación de esources/js/Composables/useTheme.js y useFormatters.js. (2) Eliminación del 100% de la lógica de colorimetría y diseño duplicada en 17 archivos de vistas. (3) Creación de componentes reutilizables accesibles (Modal.vue, StatCard.vue). (4) Optimización de reactividad mediante shallowRef en estados primitivos de modales y filtros. (5) Build de Vite optimizado y 0 advertencias en ESLint. |
| **v14** | Discrepancias con skills Laravel (sintaxis string en rutas, controladores con validación inline, ausencia de prevención N+1 y consultas en loop) | **Refactorización y Excelencia Backend (Skill laravel-best-practices)**: (1) Migración del 100% de `routes/web.php` a sintaxis canónica `[Controller::class, 'method']`. (2) Extracción de validaciones a Form Requests (`SaveContractRequest`, `SaveRoomRequest`, `SaveEquipmentRequest`, `UpdateEntityProfileRequest`). (3) Activación de `Model::preventLazyLoading` en `AppServiceProvider`. (4) Carga ansiosa preventiva (`loadMissing` y `with(['equipment.room'])`) en servicios del motor (`EnergyEngineService`, `ConsumptionAnalysisService`, `Tank1BaseService`). (5) Modernización de modelos a PHP 8.2+ con `casts(): array` y `Attribute::make()`. (6) Optimización de consulta en `AdminController@userPayments`. (7) 59/59 tests en verde y 0 alertas en `composer audit`. |

---

### [2026-08-17] — Implementación de Arquitectura Modular de Perfiles Comerciales
- **Contexto:** Necesidad de auditar y modelar comercios con perfiles térmicos y de proceso específicos (especialmente Heladerías Artesanales con frío negativo 24/7 y picos de mantecado).
- **Decisión Tomada:** Se eliminó el match y enum rígido comercio_type. Se creó el namespace App\Domain\Commercial\ con un CommercialProfileRegistry extensible.
- **Sub-rubros incorporados:**
  1. heladeria_artesanal (Heladería Artesanal & Fábrica de Frío).
  2. pizzeria (Pizzería & Empanadas).
  3. cafeteria (Cafetería & Bar).
  4. estaurante_general (Restaurante integral).
  5. etail_general (Comercio al público).
  6. oficina_servicios (Oficinas profesionales).
- **Impacto en Frontend:** Selector en 2 niveles en Edit.vue con tarjeta interactiva de sensibilidad térmica.

### [2026-08-17] — Rediseño de la Landing Page para Beta Cerrada
- **Contexto:** La landing page no reflejaba adecuadamente el estado de Beta Privada ni la adaptabilidad comercial.
- **Decisiones Tomadas:**
  - Se configuró el botón de *Tu Comercio* con badge BETA PRIVADA y enlace a WhatsApp (5492644533704) con mensaje de consulta por rubro.
  - Se añadieron acentos y secciones oscuras/degradadas con el color de marca #009966 en la sección Hero, capturas de pantalla y CTA final.
  - Se integró el banner oficial modo_ahorro_banner.png en la cabecera.

### [2026-08-17] — Suite Completa de Super Administrador & Arquitectura de Navegación Segmentada
- **Contexto:** Se requería una suite de administración integral (Sistema) para gestionar el catálogo maestro, los modelos de mercado aportados por la comunidad, las curvas IRAM de eficiencia, los benchmarks de reemplazo y el control de usuarios, credenciales y suscripciones SaaS.
- **Decisiones Tomadas:**
  1. **Segmentación de Barra Lateral Primaria (4 Bloques):** Se dividió la barra vertical izquierda en 4 segmentos independientes:
     - Dashboard: Panel global y métricas de red.
     - Configuración & Catálogo (Settings): Catálogo Maestro, Modelos Oficiales, Matriz de Eficiencia (A+++ a G) y Benchmarks de Mercado.
     - Usuarios (Users): Cuentas & Roles, Reseteos de Contraseña asistidos y Facturación/Planes.
     - APIs & Conectores (KeyRound): Conectores Mercado Libre, CAMMESA/ENRE, Open-Meteo y Webhooks.
  2. **Modelos Comerciales & Inteligencia de Clientes (/sistema/modelos):** Sistema de homologación de artefactos con autocompletado en tiempo real en la carga de infraestructura.
  3. **Matriz de Eficiencia Energética (/sistema/eficiencia):** Editor interactivo de multiplicadores IRAM con restablecimiento a valores de fábrica.
  4. **Benchmarks & Motor de Reemplazos (/sistema/benchmarks):** Unificación de EquipmentBenchmark con cálculo de ROI, precio de mercado y links monetizables de afiliados.
  5. **Gestión de Usuarios, Reseteos & Pagos (/sistema/usuarios/*):**
     - Cuentas & Roles con toggle de Super Admin y protección contra auto-democión.
     - Generación de enlaces seguros de recuperación (tokens de 64 caracteres) y forzado directo de claves.
     - Gestión de planes SaaS centrada 100% en el usuario (Gratuito, Premium, Enterprise) con control de cupos de entidades y extensiones de vigencia.

---

## 2. Mapa de Rutas y Navegación Clave

| Ruta | Nombre de Ruta | Controlador / Vista | Descripción |
| :--- | :--- | :--- | :--- |
| / | welcome | welcome.blade.php | Landing page institucional con capturas y CTAs |
| /dashboard | dashboard | DashboardController@index | Panel principal de métricas y resumen de entidad activa |
| /gestion/entidad/perfil | gestion.entity.edit | EntityController@edit (Entity/Edit.vue) | Configuración física y comercial de la entidad |
| /gestion/infraestructura | gestion.infrastructure.index | InfrastructureController@index | Gestión de ambientes y artefactos |
| /gestion/contratos | gestion.contracts.index | ContractController@index | Gestión de contratos y distribuidoras |
| /gestion/facturas | gestion.invoices.index | InvoiceController@index | Carga y gestión de facturas de luz |
| /analisis/ajuste-uso | nalisis.usage-adjustment.index | UsageAdjustmentController@index | Sintonía fina y calibración de tanques |
| /sistema/administracion | sistema.admin | AdminController@dashboard | Panel principal de Super Administrador |
| /sistema/catalogo | sistema.catalogue | AdminController@catalogue | Catálogo Maestro de equipos y física |
| /sistema/modelos | sistema.models | AdminController@equipmentModels | Modelos oficiales & inteligencia de comunidad |
| /sistema/eficiencia | sistema.efficiency | AdminController@efficiencyLabels | Matriz de coeficientes de eficiencia IRAM |
| /sistema/benchmarks | sistema.benchmarks | AdminController@benchmarks | Benchmarks de mercado y cálculo de ROI |
| /sistema/usuarios | sistema.users | AdminController@users | Gestión de usuarios y asignación de roles |
| /sistema/usuarios/reseteos | sistema.users.resets | AdminController@userResets | Reseteos y enlaces de recuperación de clave |
| /sistema/usuarios/pagos | sistema.users.payments | AdminController@userPayments | Facturación SaaS, planes y suscripciones |
| /sistema/apis | sistema.apis | AdminController@apis | Conectores externos y credenciales de API |

---

## 3. Arquitectura de Datos

### Tabla equipment
- **has_defined_pattern** *(bool)*: El usuario declara que este equipo tiene un patrón predecible. El motor lo sub-clasifica técnicamente. **Destino futuro**: migrar a pattern_type ENUM('inamovible', 'periodico', 'volatil').
- **vg_daily_use_hours**: Horas de uso diario declaradas por el usuario. Si >= 23.5 AND frecuencia diaria → entra al Tanque Crítico.
- **usage_frequency**: Periodicidad declarada (diario, recuentemente, ocasionalmente, aramente, 
unca). Multiplica el cálculo teórico.
- **staff_count**, **isitors_count**: Cantidad de personal y comensales/visitantes. Reemplaza people_count para cálculos proporcionales finos.
- **service_turns**: Cantidad de turnos operativos (ej: Almuerzo/Cena). Multiplicador para lógicas TURNS_BASED.
- **usage_unit**: Unidad de medida del consumo (hours, cycles, people_proportional). Determina la interfaz de ajuste y el algoritmo de cálculo.
- **energy_per_cycle**: kWh por ciclo para línea blanca (Lavarropas, Cafetera, etc.).
- **kwh_reconciled**: kWh asignado por el motor después de la calibración.

---

## 4. Motor de Energía v5 — Arquitectura Teórico Puro

### Cambio fundamental respecto a v4 (El Fin de la Compresión)
**v4**: El motor intentaba que la suma de los tanques coincidiera exactamente con la factura, usando el Tank4 como esponja elástica y un factor de compresión artificial. Esto rompía la confianza matemática del sistema.

**v5 (Teórico Puro)**: El motor **NO COMPRIME**. Calcula estrictamente el consumo en base a hábitos y clima (honestidad matemática). La diferencia con la factura se considera puramente **Energía Residual** (exceso o faltante).
- Se elimina la lógica de distribución elástica forzada.
- Gatekeeper de Tolerancia: Solo permite guardar ajustes si el Teórico Puro cae en un rango lógico de la factura (**95% - 120%**).
- Si el ajuste es >120%, el usuario está sobre-declarando. Si es <95%, está sub-declarando u olvidó equipos.

### Cascada de clasificación

`
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

PASO 4 - Tank Variable (Uso Variable):
  → Criterio: todo lo que no fue asignado en pasos anteriores
  → Algoritmo simplificado: Cálculo Teórico directo basado en horas o frecuencia.

PASO 5 - Cálculo Residual:
  → Energía Residual = Factura (Billed) - Suma Teórica de Tanques
  → Este valor se reporta visualmente, sin intentar esconderlo dentro del Tank 4.
`

### Invariantes del motor (NO romper)
1. El orden de la cascada es fijo: Standby → Crítico → Certeza → Climático → Volátil.
2. Cada servicio filtra por 	ank_assignment === null.
3. _theo_kwh se calcula antes de la cascada.
4. El Tank Volátil NO calcula kWh propios arbitrarios: solo redistribuye el saldo remanente honesto.
5. has_defined_pattern se persiste en equipment, tanto en Guardar Contexto como en Sintonizar.

---

## 5. Estado de la Suite de Tests

- **Tests Unitarios:** 8 pruebas (Cálculo de análisis, registros comerciales, física de perfiles).
- **Tests de Feature:** 43 pruebas (Gestión física, contratos, facturas, perfil entidad, unificación de cuotas, motor adaptativo, infraestructura, cabeceras de seguridad).
- **Estado Global:** **51 tests pasando (100% éxito), 262 aserciones.**

---

## 6. Backlog y Próximos Pasos (Roadmap)

1. **Piloto de Heladería Artesanal:** Validar la carga de artefactos (Mantecadora, Pasteurizador, Pozos) y la conciliación con factura real en San Juan.
2. **Soporte Multi-Vector (Gas Natural):** Incorporar EnergySource en el catálogo de artefactos y permitir carga de facturas de distribuidoras de gas.
3. **Ingesta de Sensores IoT:** Endpoint API/Webhook para recibir telemetría de medidores de potencia y sensores de temperatura en cámaras de frío.
4. **Pasarela de Pagos (Mercado Pago / Stripe):** Automatización del webhook de cobro recurrente para renovación automática de membresías.
5. **Arquitectura Target de Patrones:** Migrar has_defined_pattern boolean → pattern_type ENUM('inamovible', 'periodico', 'volatil') y formalizar los CategoryCalculators como clases independientes registradas en un dispatcher dentro de EnergyEngineService.
