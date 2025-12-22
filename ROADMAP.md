# ModoAhorro - Roadmap del Proyecto

## 🎯 Visión General

Sistema SaaS de gestión energética inteligente que evoluciona desde ajuste manual hasta gemelo digital con IoT.

---

## 📊 Estado Actual (Completado ~100%)

### ✅ Módulos Implementados

**1. Gestión de Entidades**
- Planes (Gratuito, Premium, Enterprise)
- Entities (Hogar, Comercio, Oficina)
- Rooms (habitaciones personalizables)
- Usuarios multi-entidad

**2. Gestión de Equipos**
- CRUD de equipos por habitación
- 8 categorías (incluye Portátiles)
- Potencia nominal (W)
- Estado activo/inactivo

**3. Gestión de Facturas**
- Contratos con proveedores
- Facturas con período de consumo
- Consumo facturado vs calculado

**4. Ajuste de Uso**
- Frecuencia: diario, semanal, ocasional
- Horas/día, días/semana
- Agrupación por habitación
- Preservación de histórico

**5. Panel de Consumo**
- Comparación facturado vs calculado
- Desglose por categoría
- Lista detallada de equipos

**6. Rutas por Tipo de Entidad** ✅ COMPLETADO
- Controladores específicos: `HomeEntityController`, `OfficeEntityController`, `TradeEntityController`
- Rutas separadas: `/entities/home/*`, `/entities/office/*`, `/entities/trade/*`
- 40 rutas por tipo (CRUD, rooms, invoices, recommendations, thermal, vacation)
- Vistas específicas para Hogar, Oficina y Comercio (index, create, show, edit)
- Seeders: `DatosHogarSeeder`, `DatosOficinaSeeder`, `DatosComercioSeeder`

**7. UI Refactoring (Tailwind + UI Kit)** ✅ COMPLETADO (Dic 2024)
- Migración completa de Bootstrap a Tailwind CSS
- Sistema de componentes UI Kit (Card, Button, Badge, Table, Input, Select, Alert, Stat-Card)
- Interactividad con Alpine.js (tabs, toggles, sliders)
- Vistas refactorizadas:
  - Dashboard y autenticación (login, register)
  - Entities (home, office, trade)
  - Rooms, Equipment, Invoices, Contracts
  - Consumption Panel (panel, cards, show)
  - Recommendations (solar, standby, thermal, replacements)
  - Usage Adjustments (index, edit, show)

---

### 📋 Completado: Rutas por Tipo de Entidad

**Completado:**
- [x] `HomeEntityController` + 40 rutas + 4 vistas Tailwind
- [x] `OfficeEntityController` + 33 rutas + 4 vistas Tailwind
- [x] `TradeEntityController` + 33 rutas + 4 vistas Tailwind
- [x] Vistas para `/entities/office/*` con UI Kit
- [x] Vistas para `/entities/trade/*` con UI Kit
- [x] Rutas legacy mantenidas para compatibilidad
- [x] UI Kit Components (Card, Button, Badge, Table, Input, Select, Alert)

**Por hacer (mejoras opcionales):**
- [ ] Migración: campos `opens_at`, `closes_at`, `operating_days` para oficina/comercio
- [ ] Tests de rutas para cada tipo
- [ ] Remover rutas legacy cuando migración esté completa

## 🚀 Roadmap por Sprints

### **SPRINT 0: Calibración Inteligente de Consumo** ✅ COMPLETADO
*Objetivo: Ajustar consumo calculado al facturado usando lógica de categorías*

**Estado:** ✅ COMPLETADO (Dic 2024)

#### Problema Resuelto
El cálculo simple `Potencia × Horas × Días` generaba valores muy altos porque:
- Es difícil recordar días y horas exactas de uso
- No considera variabilidad estacional

**Solución:** Sistema de **Calibración Inteligente** que limita el consumo calculado al facturado y redistribuye proporcionalmente.

#### Implementación: Sistema "Base / Hormigas / Elefantes"

| Categoría | Ejemplos | Política |
|-----------|----------|----------|
| **BASE CRÍTICA** | Heladera, Router, Alarmas | Intocables - 24h, asignación completa |
| **BASE PESADA** | Termotanque, Bomba de Agua | Esenciales, posible recorte |
| **HORMIGAS** | Luces, Cargadores, Portátiles | Bajo consumo, protegidos |
| **ELEFANTES** | Aires, Calefactores, PCs, TVs | Absorben el delta (ajuste ponderado) |

#### Algoritmo Waterfall
1. Primero se asigna 100% a BASE CRÍTICA
2. Luego a BASE PESADA
3. Después a HORMIGAS
4. El **remaining** se distribuye a ELEFANTES con **pesos por categoría**:
   - Climatización: x3.0 (mayor incertidumbre)
   - Cocina: x1.5
   - Oficina/Entretenimiento: x0.6

#### Archivos Implementados
- ✅ `ConsumptionCalibrator.php` - Lógica de calibración
- ✅ `calibration_strategy.md` - Documentación detallada
- ✅ Integración con `ConsumptionAnalysisService`

#### Resultados de Tests
| Factura | Estimado | Calibrado | Precisión |
|---------|----------|-----------|-----------|
| Verano 624 kWh | 278 kWh | 624 kWh | ✅ 100% |
| Otoño 123 kWh | 228 kWh | 123 kWh | ✅ 100% |
| Otoño 83 kWh | 257 kWh | 83 kWh | ✅ 100% |
| Invierno 78 kWh | 217 kWh | 78 kWh | ✅ 100% |

**Documentación:** [calibration_strategy.md](docs/logic/calibration_strategy.md)

---

### **SPRINT 1: Validación y Trazabilidad** ✅ COMPLETADO (90%)
*Objetivo: Evitar desviaciones absurdas y rastrear equipos en el tiempo*

**Estado:** ✅ COMPLETADO (Dic 2024) - Solo falta bloqueo de facturas

#### Implementado
- ✅ `ValidationService` con cálculo de desviación y alertas (verde <10%, amarillo <30%, rojo >30%)
- ✅ Campos `installed_at` y `removed_at` en equipos (migración 2025_12_02)
- ✅ Alertas de desviación en panel de consumo
- ✅ Sugerencias automáticas de ajuste

#### Pendiente Menor
- [ ] Campo `usage_locked` en facturas para bloquear períodos auditados

#### Archivos Implementados
- ✅ [ValidationService.php](app/Services/Core/ValidationService.php)
- ✅ Migración `add_installation_dates_to_equipment.php`

**Documentación:** [walkthrough_energy_fix.md](docs/archive/walkthrough_energy_fix.md)

---

### **SPRINT 2: Asistencia Climática** ✅ COMPLETADO
*Objetivo: Sugerencias automáticas para climatización*

**Estado:** ✅ COMPLETADO (Nov 2024)

#### Implementado
- ✅ `ClimateDataService` (12KB) - Integración Open-Meteo
- ✅ Tabla `climate_data` con cache de datos climáticos
- ✅ `UsageSuggestionService` (7.5KB) - Cálculo de sugerencias
- ✅ Días calor/frío para ajuste automático de climatización

**Documentación:** [CLIMATE_API_INTEGRATION.md](docs/integrations/CLIMATE_API_INTEGRATION.md)

---

### **SPRINT 3: Catálogo de Reemplazos** ✅ COMPLETADO
*Objetivo: Recomendar equipos eficientes*

**Estado:** ✅ COMPLETADO (Dic 2024)

#### Implementado
- ✅ Tabla `efficiency_benchmarks` - Catálogo de eficiencia
- ✅ `ReplacementService` - Recomendaciones de reemplazo
- ✅ Servicios por tipo: HogarRecommendationService, OficinaRecommendationService, ComercioRecommendationService
- ✅ Cálculo de ROI y ahorro potencial

**Documentación:** [REPLACEMENT_MODULE.md](docs/modules/REPLACEMENT_MODULE.md)

---

### **SPRINT 4: Módulo de Vacaciones** ✅ COMPLETADO
*Objetivo: Ajustar consumo por ausencias*

**Estado:** ✅ COMPLETADO (Nov 2024)

#### Implementado
- ✅ `VacationService` (9.6KB) - Checklists personalizados
- ✅ Marcado de facturas anómalas automático
- ✅ Reglas: Conectividad, Refrigeración, Termotanque, Vampiro, Iluminación
- ✅ Detección de períodos vacacionales

**Documentación:** [VACATION_MODULE.md](docs/modules/VACATION_MODULE.md)

---

### **SPRINT 5: Análisis de Standby** ✅ COMPLETADO
*Objetivo: Identificar consumo fantasma*

**Estado:** ✅ COMPLETADO (Nov 2024)

#### Implementado
- ✅ `StandbyAnalysisService` - Análisis de consumo vampiro
- ✅ Identificación de equipos con standby
- ✅ Cálculo de consumo fantasma
- ✅ Recomendaciones de ahorro integradas

**Documentación:** [STANDBY_IMPLEMENTATION.md](docs/modules/STANDBY_IMPLEMENTATION.md)

---

### **SPRINT 6: Optimización de Red (Grid)** ✅ COMPLETADO
*Objetivo: Optimizar uso según tarifa horaria*

**Estado:** ✅ COMPLETADO (Nov 2024)

#### Implementado
- ✅ `GridOptimizerService` - Arbitraje de tarifas
- ✅ Análisis Peak Shifting (horas pico vs valle)
- ✅ Cálculo de ahorro por desplazamiento horario
- ✅ Recomendaciones automáticas

**Documentación:** [GRID_OPTIMIZATION_MODULE.md](docs/modules/GRID_OPTIMIZATION_MODULE.md)

---

### **SPRINT 7: Calefón/Termotanque Solar** ✅ COMPLETADO
*Objetivo: Evaluar viabilidad de calefón solar*

**Estado:** ✅ COMPLETADO (Nov 2024)

#### Implementado
- ✅ `SolarWaterService` (6.3KB) - Cálculos térmicos
- ✅ Cálculo de consumo actual de agua caliente
- ✅ Estimación de ahorro con calefón solar
- ✅ ROI y tiempo de recuperación

**Documentación:** [SOLAR_WATER_LOGIC.md](docs/logic/SOLAR_WATER_LOGIC.md)

---

### **SPRINT 8: Paneles Solares** ✅ COMPLETADO
*Objetivo: Evaluar viabilidad de energía solar*

**Estado:** ✅ COMPLETADO (Nov 2024)

#### Implementado
- ✅ `SolarPowerService` (2.6KB) - Cálculo fotovoltaico
- ✅ Cálculo de área disponible (m² de techo)
- ✅ Estimación de potencia instalable
- ✅ ROI y tiempo de recuperación
- ✅ Integración con datos de radiación solar (via ClimateDataService)

**Documentación:** [SOLAR_COVERAGE_LOGIC.md](docs/logic/SOLAR_COVERAGE_LOGIC.MD)

---

### **SPRINT 9: Dashboard Ejecutivo** (1 semana)
*Objetivo: Métricas para CEOs/inversores*

#### Tareas
- [ ] Crear `ExecutiveDashboardService`
- [ ] Métricas agregadas (usuarios, ahorro total)
- [ ] Proyecciones con IoT
- [ ] Gráficos de impacto
- [ ] Exportación a PDF

#### Entregables
- Dashboard ejecutivo
- Reporte de impacto
- Proyecciones de crecimiento

---

### **SPRINT 10: Preparación IoT** (2 semanas)
*Objetivo: API para medidores inteligentes*

#### Tareas
- [ ] Crear tabla `equipment_readings`
- [ ] Crear `IoTDataService`
- [ ] API REST para recibir lecturas
- [ ] Integración con cálculo existente
- [ ] Documentación de API

#### Entregables
- API REST documentada
- Sistema de autenticación de dispositivos
- Dashboard de dispositivos conectados

---

## 🏗️ Arquitectura de Services

```
app/Services/
├── Core/
│   ├── ConsumptionAnalysisService.php ✅ (EXISTE - Requiere Sprint 0)
│   └── ValidationService.php (Sprint 1)
│
├── Climate/
│   ├── ClimateDataService.php (Sprint 2)
│   └── UsageSuggestionService.php (Sprint 2)
│
├── Recommendations/
│   ├── ReplacementRecommendationService.php (Sprint 3)
│   ├── StandbyAnalysisService.php (Sprint 5)
│   └── TimeOfUseService.php (Sprint 6)
│
├── Lifestyle/
│   └── VacationService.php (Sprint 4)
│
├── Solar/
│   ├── SolarWaterHeaterService.php (Sprint 7)
│   └── SolarPanelService.php (Sprint 8)
│
├── Analytics/
│   └── ExecutiveDashboardService.php (Sprint 9)
│
└── IoT/
    └── IoTDataService.php (Sprint 10)
```

---

## 📏 Principios de Desarrollo

### 1. **Un Service por Módulo**
Cada funcionalidad tiene su propio Service. No mezclar lógicas.

### 2. **Testing por Service**
Cada Service debe tener tests unitarios básicos.

### 3. **Migraciones Incrementales**
Nunca modificar migraciones antiguas. Crear nuevas.

### 4. **Documentación Continua**
Actualizar README.md con cada sprint completado.

### 5. **Git Commits Semánticos**
```
feat: nueva funcionalidad
fix: corrección de bug
refactor: mejora de código
docs: documentación
test: tests
```

---

## 🎯 Hitos Clave

- **Mes 1**: Sprints 1-2 → MVP mejorado con validación y clima
- **Mes 2**: Sprints 3-4 → Recomendaciones básicas
- **Mes 3**: Sprints 5-6 → Análisis avanzados
- **Mes 4**: Sprints 7-8 → Energías renovables
- **Mes 5**: Sprints 9-10 → Dashboard ejecutivo + IoT ready

---

## 📊 Métricas de Éxito

- **Precisión**: >85% entre calculado y facturado
- **Adopción**: >70% de usuarios ajustan sus consumos
- **Ahorro**: Promedio de 15% identificado por usuario
- **Satisfacción**: NPS >50

---

## 🚨 Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Desviación de alcance | Alta | Alto | Roadmap estricto, un sprint a la vez |
| APIs externas caídas | Media | Medio | Fallbacks, cache de datos |
| Complejidad técnica | Media | Alto | Arquitectura modular, testing |
| Falta de datos reales | Alta | Medio | Seeders realistas, beta testers |

---

## 💡 Próximos Pasos Inmediatos

1. ✅ **Sprint 0-8**: Todos completados
2. ✅ **UI Refactoring**: Tailwind CSS + UI Kit completado
3. ⏳ **Sprint 9: Dashboard Ejecutivo** - Próximo a implementar
4. ⏳ **Sprint 10: Preparación IoT** - API para medidores inteligentes
5. **Opcional**: Tests de rutas y migraciones adicionales
