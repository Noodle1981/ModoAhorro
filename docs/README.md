# 📚 Documentación - Modo Ahorro

Bienvenido a la documentación técnica del proyecto **Modo Ahorro**. Esta guía te ayudará a navegar por todos los recursos disponibles.

---

## 🚀 Inicio Rápido

| Documento | Descripción |
|-----------|-------------|
| [ROADMAP_MAESTRO.md](ROADMAP_MAESTRO.md) | **Visión completa del proyecto**: Fases, hitos, métricas y roadmap a 3 años |
| [MANUAL_INTEGRAL.md](MANUAL_INTEGRAL.md) | Guía técnica completa: arquitectura, módulos, instalación |
| [credenciales.md](credenciales.md) | Credenciales de desarrollo y testing |

---

## 🔍 Documentación por Categoría

### **📊 Estado del Proyecto**

#### [ROADMAP_MAESTRO.md](ROADMAP_MAESTRO.md) 🌟
El documento maestro que define la visión completa del proyecto:
- Estado actual (MVP funcional)
- Problemas críticos identificados en auditoría
- 5 fases de desarrollo (Estabilización → Optimización → IA → IoT → Monetización)
- Hitos clave y métricas de éxito
- Visión a largo plazo (2-3 años)

#### [AUDITORIA.MD](AUDITORIA.MD) 🔬
Auditoría técnica completa del motor de cálculo de consumos:
- Arquitectura del sistema (3 fases)
- 7 problemas identificados (2 críticos, 2 altos, 2 medios, 1 bajo)
- Fortalezas del sistema
- 5 recomendaciones prioritarias con acciones específicas
- Diagrama de flujo completo

#### [TECHNICAL_DEBT.md](TECHNICAL_DEBT.md) ⚠️
Registro de deuda técnica y mejoras pendientes:
- Problemas críticos, importantes y mejoras futuras
- Refactorings pendientes
- Lecciones aprendidas
- Checklist de calidad por sprint

#### [PRODUCTION_CHECKLIST.md](PRODUCTION_CHECKLIST.md) ✅
Checklist de preparación para producción:
- Módulos faltantes
- Configuración de sistema
- Seguridad y entorno
- Verificación final (QA)

---

### **🏗️ Arquitectura y Módulos**

#### [MANUAL_INTEGRAL.md](MANUAL_INTEGRAL.md)
Guía técnica completa del proyecto:
- Visión general y arquitectura
- Servicios principales
- Módulos del sistema
- Guía de instalación
- Comandos de testing

#### Módulos Implementados

| Módulo | Archivo | Descripción |
|--------|---------|-------------|
| **Optimización de Red** | [modules/GRID_OPTIMIZATION_MODULE.md](modules/GRID_OPTIMIZATION_MODULE.md) | Peak shifting y arbitraje de tarifas horarias |
| **Vacaciones** | [modules/VACATION_MODULE.md](modules/VACATION_MODULE.md) | Detección de anomalías y checklists |
| **Mantenimiento** | [modules/MAINTENANCE_MODULE.md](modules/MAINTENANCE_MODULE.md) | Seguimiento de salud de equipos |
| **Standby** | [modules/STANDBY_IMPLEMENTATION.md](modules/STANDBY_IMPLEMENTATION.md) | Análisis de consumo vampiro |
| **Confort Térmico** | [modules/THERMAL_COMFORT_MODULE.md](modules/THERMAL_COMFORT_MODULE.md) | Optimización de climatización |
| **Reemplazo** | [modules/REPLACEMENT_MODULE.md](modules/REPLACEMENT_MODULE.md) | ROI de reemplazo de equipos |
| **Medidor Inteligente** | [modules/SMART_METER_DEMO.md](modules/SMART_METER_DEMO.md) | Simulador IoT en tiempo real |
| **Precios Dinámicos** | [modules/DYNAMIC_PRICING_MODULE.md](modules/DYNAMIC_PRICING_MODULE.md) | Integración con tarifas variables |

---

### **⚙️ Lógica de Negocio**

| Documento | Descripción |
|-----------|-------------|
| [logic/ENERGY_CALC_FIX.md](logic/ENERGY_CALC_FIX.md) | Corrección de cálculo de energía |
| [logic/calibration_strategy.md](logic/calibration_strategy.md) | Estrategia de calibración jerárquica |
| [logic/Smart Weighted Calibration.md](logic/Smart%20Weighted%20Calibration.md) | Calibración ponderada inteligente |
| [logic/load_factors.md](logic/load_factors.md) | Factores de carga por tipo de equipo |
| [logic/SOLAR_COVERAGE_LOGIC.MD](logic/SOLAR_COVERAGE_LOGIC.MD) | Lógica de cobertura solar fotovoltaica |
| [logic/SOLAR_WATER_LOGIC.md](logic/SOLAR_WATER_LOGIC.md) | Lógica de termotanque solar |
| [logic/DATA_ANOMALY_LOGIC.md](logic/DATA_ANOMALY_LOGIC.md) | Detección de anomalías en datos |

---

### **🔌 Integraciones**

| Documento | Descripción |
|-----------|-------------|
| [integrations/CLIMATE_API_INTEGRATION.md](integrations/CLIMATE_API_INTEGRATION.md) | Integración con Open-Meteo API para datos climáticos |

---

### **📦 Archivo Histórico**

Documentos obsoletos o de referencia histórica:

| Documento | Descripción |
|-----------|-------------|
| [archive/MASTER_PLAN_OLD.md](archive/MASTER_PLAN_OLD.md) | Plan maestro anterior (obsoleto) |
| [archive/SPRINT_1_NEXT_STEPS.md](archive/SPRINT_1_NEXT_STEPS.md) | Pasos siguientes del Sprint 1 |
| [archive/ETAPAS_DESARROLLO.md](archive/ETAPAS_DESARROLLO.md) | Etapas de desarrollo originales |
| [archive/planes_y_entidades.md](archive/planes_y_entidades.md) | Documentación de planes y entidades |
| [archive/walkthrough_energy_fix.md](archive/walkthrough_energy_fix.md) | Walkthrough de corrección de energía |

---

## 🛠️ Guías de Desarrollo

### Instalación y Setup

```bash
# 1. Clonar repositorio
git clone <repo-url>
cd ModoAhorroFINAL26

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Base de datos
touch database/database.sqlite
php artisan migrate --seed

# 5. Iniciar servidores
php artisan serve
npm run dev
```

### Credenciales de Desarrollo

Ver [credenciales.md](credenciales.md) para usuarios de prueba.

### Testing

```bash
# Tests automatizados
php artisan test

# Tests personalizados
php artisan test:anomaly
php artisan test:grid-optimization
php artisan test:climate
php artisan test:vacation
```

---

## 📋 Convenciones y Estándares

### Estructura de Código
- **Controllers**: Lógica de presentación únicamente
- **Services**: Lógica de negocio compleja
- **Models**: Eloquent ORM + relaciones
- **Migrations**: Solo nuevas (nunca modificar antiguas)

### Nomenclatura
- **Variables**: `camelCase`
- **Métodos**: `camelCase`
- **Clases**: `PascalCase`
- **Tablas**: `snake_case` (plural)
- **Columnas**: `snake_case`

### Git Commits
Formato semántico:
```
feat: Agregar validación pre-calibración
fix: Corregir cálculo de standby
docs: Actualizar ROADMAP_MAESTRO
refactor: Extraer lógica a ValidationService
test: Agregar tests para ConsumptionCalibrator
```

---

## 🎯 Próximos Pasos

Según el [ROADMAP_MAESTRO.md](ROADMAP_MAESTRO.md), las prioridades inmediatas son:

### **FASE 0: Estabilización del Motor** (2-3 semanas)
1. ✅ Auditoría completa realizada
2. ⏳ Refactorización de clasificación de equipos
3. ⏳ Validación pre-calibración
4. ⏳ Mejoras en manejo climático
5. ⏳ Audit trail y logging

Ver detalles completos en [ROADMAP_MAESTRO.md](ROADMAP_MAESTRO.md#fase-0-estabilización-del-motor-de-cálculo).

---

## 📞 Contacto y Soporte

- **Documentación Técnica**: Este directorio (`docs/`)
- **Issues**: GitHub Issues
- **Credenciales**: [credenciales.md](credenciales.md)

---

**Última actualización**: 2026-01-21  
**Versión de documentación**: 1.0

> 💡 **Tip**: Comienza por el [ROADMAP_MAESTRO.md](ROADMAP_MAESTRO.md) para entender la visión completa, luego consulta el [MANUAL_INTEGRAL.md](MANUAL_INTEGRAL.md) para detalles técnicos.
