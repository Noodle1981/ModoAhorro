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

| Servicio | Propósito |
|----------|-----------|
| `ConsumptionAnalysisService` | Motor de cálculo de consumo y proyecciones |
| `ConsumptionCalibrator` | Calibración Base/Hormigas/Elefantes |
| `SolarPowerService` | Calculadora de viabilidad fotovoltaica |
| `SolarWaterHeaterService` | Análisis de termotanques solares |
| `VacationService` | Detección de anomalías y checklists de viaje |
| `GridOptimizerService` | Arbitraje de tarifas (Peak Shifting) |
| `ClimateDataService` | Integración con APIs climáticas |
| `ThermalScoreService` | Evaluación de envolvente térmica |
| `ReplacementService` | Recomendaciones de equipos eficientes |
| `MaintenanceService` | Seguimiento de mantenimiento de equipos |
| `StandbyAnalysisService` | Análisis de consumo fantasma (vampiro) |

## Estado Actual
Ver [ROADMAP.md](../ROADMAP.md) para el estado detallado de sprints.

**Completado (~70%):**
- Gestión de entidades, habitaciones, equipos
- Gestión de facturas y contratos
- Panel de consumo (calculado vs facturado)
- Módulo de vacaciones
- Módulo térmico (Confort Térmico)
- Análisis solar (paneles + termotanque)
- Optimización de red (tarifas horarias)

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
