# ModoAhorro - Contexto del Proyecto

## Resumen
**ModoAhorro** es un sistema SaaS de gestión energética inteligente para hogares y pequeñas empresas. Permite auditar, simular y optimizar el consumo eléctrico mediante diagnósticos automáticos, recomendaciones de eficiencia y proyecciones de ahorro con energías renovables.

El sistema evoluciona desde una herramienta de auditoría pasiva hacia gestión activa con IoT y gemelos digitales.

## Stack Tecnológico
- **Backend**: Laravel 10/11, PHP 8.2+
- **Frontend**: Blade Templates + Bootstrap 5 + Chart.js
- **Base de datos**: SQLite (desarrollo) / MySQL (producción)
- **Patrón**: MVC con capa de Services (`App\Services\`)

## Servicios Principales

### Motor Energético v3 (Núcleo)
| Servicio | Propósito |
|----------|-----------|
| `EnergyEngineService` | **Motor principal**: Distribuye consumo en 3 tanques virtuales (Base/Clima/Elasticidad) |
| `ClimateService` | Integración con Open-Meteo API, cálculo de Grados-Día (HDD/CDD) |
| `ThermalProfileService` | Evaluación de eficiencia térmica del hogar (A-E), multiplicadores para Tanque 2 |
| `ConsumptionAnalysisService` | Orquestador de análisis de consumo, integra Motor v3 y genera reportes |

### Módulos Especializados
| Servicio | Propósito |
|----------|-----------|
| `SolarPowerService` | Calculadora de viabilidad fotovoltaica |
| `SolarWaterHeaterService` | Análisis de termotanques solares |
| `VacationService` | Detección de anomalías y checklists de viaje |
| `GridOptimizerService` | Arbitraje de tarifas (Peak Shifting) |
| `ReplacementService` | Recomendaciones de equipos eficientes |
| `MaintenanceService` | Seguimiento de mantenimiento de equipos |
| `StandbyAnalysisService` | Análisis de consumo fantasma (vampiro) |

## Estado Actual (Febrero 2026)

**Motor Energético v3 - COMPLETADO:**
- ✅ Sistema de 3 Tanques (Base 24/7, Clima, Elasticidad)
- ✅ Integración con Open-Meteo para datos climáticos reales
- ✅ Wizard Térmico para calificación de hogares (A-E)
- ✅ Dashboard de Auditoría (`/admin/audit/dashboard`)
- ✅ Sistema de Audit Logs en `equipment_usages`
- ✅ Tests unitarios (`ClimateServiceTest`, `EnergyEngineTest`)

**Módulos Funcionales:**
- Gestión de entidades, habitaciones, equipos
- Gestión de facturas y contratos
- Panel de consumo (calculado vs facturado)
- Módulo de vacaciones
- Análisis solar (paneles + termotanque)
- Optimización de red (tarifas horarias)

**Limpieza Técnica Reciente:**
- Eliminadas tablas obsoletas: `devices`, `device_usages`, `utility_companies`
- Eliminados modelos: `Device.php`, `UtilityCompany.php`, `ConsumptionCalibrator.php`
- Consolidación de proveedores en modelo `Contract`
- Unificación de cálculos climáticos (base 18°C/24°C)

## Convenciones

### Código
- **Naming DB**: snake_case (`equipment_usages`, `created_at`)
- **Naming PHP**: camelCase para métodos, PascalCase para clases
- **Services**: Un servicio por módulo, no mezclar lógicas

### Git
```
feat: nueva funcionalidad
fix: corrección de bug
refactor: mejora de código
docs: documentación
test: tests
```

### Testing
```bash
php artisan test                    # Suite completa
php artisan test:anomaly            # Lógica de anomalías
php artisan test:grid-optimization  # Optimización de red
php artisan test:climate            # APIs climáticas
```

## Documentación Clave
- [MANUAL_INTEGRAL.md](../docs/MANUAL_INTEGRAL.md) - Manual técnico completo
- [ROADMAP.md](../ROADMAP.md) - Sprints y estado del proyecto
- [docs/modules/](../docs/modules/) - Documentación de cada módulo
- [docs/logic/](../docs/logic/) - Lógica de cálculos

## Instalación Rápida
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
npm run dev  # En otra terminal
```

---
*Archivo de contexto para compartir entre IAs y desarrolladores*
