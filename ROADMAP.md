# ModoAhorro - Roadmap del Proyecto

## 🎯 Visión General

Sistema SaaS de gestión energética inteligente que evoluciona desde ajuste manual hasta gemelo digital con IoT.

---

## 📊 Estado Actual (Completado ~70%)

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

**6. Rutas por Tipo de Entidad** ✅ NUEVO
- Controladores específicos: `HomeEntityController`, `OfficeEntityController`, `TradeEntityController`
- Rutas separadas: `/entities/home/*`, `/entities/office/*`, `/entities/trade/*`
- 40 rutas por tipo (CRUD, rooms, invoices, recommendations, thermal, vacation)
- Vistas específicas para Hogar (index, create, show, edit)
- Vistas pendientes para Oficina y Comercio
- Seeders: `DatosHogarSeeder`, `DatosOficinaSeeder`, `DatosComercioSeeder`

---

### 📋 Pendiente: Rutas por Tipo de Entidad

**Completado:**
- [x] `HomeEntityController` + 40 rutas + 4 vistas
- [x] `OfficeEntityController` + 33 rutas (sin vistas)
- [x] `TradeEntityController` + 33 rutas (sin vistas)
- [x] Rutas legacy mantenidas para compatibilidad

**Por hacer:**
- [ ] Crear vistas para `/entities/office/*` (copiar de home)
- [ ] Crear vistas para `/entities/trade/*` (copiar de home)  
- [ ] Migración: campos `opens_at`, `closes_at`, `operating_days` para oficina/comercio
- [ ] Tests de rutas para cada tipo
- [ ] Remover rutas legacy cuando migración esté completa

## 🚀 Roadmap por Sprints

### **SPRINT 0: Factor de Carga y Eficiencia** ⚡ CRÍTICO
*Objetivo: Cálculos realistas basados en física de equipos*

**Estado:** 🔴 Sin implementar (bloquea precisión del MVP)

#### Problema Actual
El sistema calcula consumo como: `Potencia × Horas × Días`

Esto asume que:
- Los equipos funcionan al 100% de su potencia nominal (irreal)
- No hay pérdidas energéticas (imposible)

**Resultado:** Consumo calculado **476% mayor** que el facturado en casos reales.

#### Solución Técnica
Implementar fórmula física correcta:

```
Energía Secundaria (facturada) = (P × h × d × FC) / η
```

Donde:
- **P** = Potencia nominal (kW)
- **h** = Horas de uso
- **d** = Días en período
- **FC** = Factor de Carga (duty cycle)
- **η** = Eficiencia del equipo

#### Tipos de Proceso y Valores

| Tipo de Proceso | Factor de Carga | Eficiencia | Ejemplos |
|-----------------|----------------|-----------|----------|
| Motor | 0.7 | 0.9 | Aires, ventiladores, bombas |
| Resistencia | 1.0 | 0.6 | Calefactores, hornos, estufas |
| Electrónico | 0.7 | 0.8 | PC, TV, notebooks, decos |
| Motor & Resistencia | 0.8 | 0.82 | Lavarropas con calentamiento |
| Magnetrón | 0.7 | 0.6 | Microondas |
| Electroluminiscencia | 1.0 | 0.9 | LEDs |

#### Tareas
- [ ] **Migración:** Agregar `process_type`, `load_factor`, `efficiency` a `equipment_types`
- [ ] **Seeder:** Asignar tipo de proceso a todos los equipos (~70 tipos)
- [ ] **Servicio:** Modificar `ConsumptionAnalysisService::calculateEquipmentConsumption()`
- [ ] **Testing:** Verificar que consumo calculado ≈ facturado (85-115%)
- [ ] **Documentación:** Agregar explicación en panel de consumo

#### Entregables
- ✅ Cálculos basados en física real
- ✅ Precisión >85% entre calculado y facturado
- ✅ Transparencia (mostrar FC y η en tooltips)
- ✅ Base sólida para todos los módulos de análisis

#### Impacto
- **Antes:** Aire 2500W × 8h × 70d = 1400 kWh ❌
- **Después:** (2.5kW × 8h × 70d × 0.7) / 0.9 = **1089 kWh** ✅ (~22% menos, más realista)

**Tiempo estimado:** 2-3 horas  
**Prioridad:** 🔴 CRÍTICA - Debe completarse antes de Sprint 1

---

### **SPRINT 1: Validación y Trazabilidad** (1 semana)
*Objetivo: Evitar desviaciones absurdas y rastrear equipos en el tiempo*

**Dependencia:** ✅ Sprint 0 completado

#### Tareas
- [ ] Crear `ValidationService` para comparar consumos
- [ ] Agregar campos `installed_at` y `removed_at` a equipos
- [ ] Implementar alertas de desviación en panel
- [ ] Filtrar equipos por período de factura
- [ ] Agregar campo `usage_locked` a facturas

#### Entregables
- Sistema de alertas (verde/amarillo/rojo)
- Equipos solo aparecen si existían en el período
- Opción de bloquear/desbloquear períodos

---

### **SPRINT 2: Asistencia Climática** (2 semanas)
*Objetivo: Sugerencias automáticas para climatización*

#### Tareas
- [ ] Crear `ClimateDataService` (integración Open-Meteo)
- [ ] Crear tabla `climate_data` (cache)
- [ ] Crear `UsageSuggestionService` (cálculo de sugerencias)
- [ ] Agregar campos climáticos a `equipment_usages`
- [ ] UI: mostrar sugerencias en vista de ajuste
- [ ] UI: indicadores en panel de consumo

#### Entregables
- Sugerencias automáticas para equipos de climatización
- Indicador "🌡️ Ajustado con datos climáticos"
- Precisión estimada por equipo

---

### **SPRINT 3: Catálogo de Reemplazos** (2 semanas)
*Objetivo: Recomendar equipos eficientes*

#### Tareas
- [ ] Crear tabla `efficient_equipment_catalog`
- [ ] Crear `ReplacementRecommendationService`
- [ ] Seeder con equipos eficientes (A+++, A++)
- [ ] Cálculo de ROI (ahorro vs costo)
- [ ] Vista de recomendaciones
- [ ] Comparativa lado a lado

#### Entregables
- Catálogo de equipos eficientes
- Recomendaciones automáticas de reemplazo
- Cálculo de ROI y tiempo de recuperación

---

### **SPRINT 4: Módulo de Vacaciones** (1 semana)
*Objetivo: Ajustar consumo por ausencias*

#### Tareas
- [ ] Crear tabla `absence_periods`
- [ ] Crear `VacationService`
- [ ] CRUD de períodos de ausencia
- [ ] Ajuste automático de consumo
- [ ] Integración con cálculo de uso

#### Entregables
- Gestión de vacaciones/viajes
- Reducción automática de consumo
- Indicador en panel

---

### **SPRINT 5: Análisis de Standby** (1 semana)
*Objetivo: Identificar consumo fantasma*

#### Tareas
- [ ] Crear `StandbyAnalysisService`
- [ ] Identificar equipos con standby
- [ ] Calcular consumo en standby
- [ ] Recomendaciones de ahorro
- [ ] Vista de análisis

#### Entregables
- Reporte de consumo standby
- Ahorro potencial
- Recomendaciones (regletas, etc.)

---

### **SPRINT 6: Uso Horario Inteligente** (2 semanas)
*Objetivo: Optimizar uso según tarifa*

#### Tareas
- [ ] Crear tabla `time_of_use_rates` (tarifas por horario)
- [ ] Crear `TimeOfUseService`
- [ ] Análisis de uso actual vs óptimo
- [ ] Recomendaciones de cambio de horario
- [ ] Cálculo de ahorro potencial

#### Entregables
- Análisis de uso horario
- Recomendaciones (ej: lavarropa de noche)
- Ahorro estimado

---

### **SPRINT 7: Calefón Solar** (1 semana)
*Objetivo: Evaluar viabilidad de calefón solar*

#### Tareas
- [ ] Crear `SolarWaterHeaterService`
- [ ] Calcular consumo actual de agua caliente
- [ ] Estimar ahorro con calefón solar
- [ ] ROI y tiempo de recuperación
- [ ] Recomendaciones de modelos

#### Entregables
- Análisis de viabilidad
- ROI de calefón solar
- Catálogo de proveedores

---

### **SPRINT 8: Paneles Solares** (2 semanas)
*Objetivo: Evaluar viabilidad de energía solar*

#### Tareas
- [ ] Crear `SolarPanelService`
- [ ] Calcular área disponible (m² de techo)
- [ ] Estimar potencia instalable
- [ ] Calcular generación estimada
- [ ] ROI y tiempo de recuperación
- [ ] Integración con API de radiación solar

#### Entregables
- Análisis de viabilidad solar
- Potencia recomendada
- ROI y ahorro anual
- Proveedores sugeridos

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

1. ✅ **Sprint 0: Factor de Carga** (2-3 horas) - BLOQUEANTE
2. Revisar resultados del Sprint 0 (consumo calculado debe ≈ facturado)
3. Crear `task.md` para Sprint 1
4. Implementar `ValidationService`
5. Testing manual del flujo completo
