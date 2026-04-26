# IMPLEMENTACION.MD — Motor de Cálculo V3 (ModoAhorro)

## Arquitectura: Dos Capas Separadas

### Capa 1: Visual (Categorías de UI)
Sirven solo para organizar la interfaz. El usuario las usa para encontrar sus equipos. **No determinan el tanque de cálculo.**

Categorías:
- Conectividad y Seguridad
- Refrigeración
- Climatización
- Agua Caliente
- Línea Blanca
- Informática y Ocio
- Electrodomésticos
- Iluminación y Otros

### Capa 2: Lógica (Flags del Motor)
El `ConsumptionAnalysisService` ignora el nombre de categoría y lee estos atributos:

| Flag / Atributo | Tanque | Criterio |
|---|---|---|
| `has_defined_pattern == true` | **T1: Certeza** | Usuario "congeló" el patrón. Se resta primero, intocable. |
| `consumption_logic == 'BASE_LOAD'` | **T2: Base** | Ciclos automáticos 24hs (Heladeras, Routers). |
| `is_thermal_sensitive == true` | **T3: Clima** | Depende del exterior. Cruza con HDD/CDD. |
| todo lo demás | **T4: Variables** | Absorbe el remanente. Ajuste proporcional por peso. |

> **Standby**: Se calcula aparte para Recomendaciones. NO entra en la bolsa de tanques.

---

## Cambios a Implementar

### Fase 1 — Motor (TankServices)

**Tank0CertaintyService.php** — Criterio de selección:
```php
// ACTUAL (solo tipo)
$eq->type->determinism_score >= 0.9

// NUEVO (también flag de usuario)
$eq->has_defined_pattern === true || $eq->type->determinism_score >= 0.9
```

**Tank2ClimateService.php** — Excluir `SEASONAL_HABIT`:
```php
// Agregar condición: los equipos con SEASONAL_HABIT NO van a T3
&& $eq->type->consumption_logic !== 'SEASONAL_HABIT'
```

**Tank3ElasticityService.php** — Lógica especial para `SEASONAL_HABIT`:
```php
// Si CDD == 0 en el periodo, el consumo del equipo se fuerza a 0
if ($eq->type->consumption_logic === 'SEASONAL_HABIT' && $cdd == 0) {
    $periodKwh = 0;
}
```

---

### Fase 2 — Clasificador UI

**AnalysisController.php** — Método `getEquipmentTier()`:
```php
private function getEquipmentTier($equipment): string {
    if ($equipment->has_defined_pattern || $equipment->type?->determinism_score >= 0.9)
        return 'certeza';
    if ($equipment->type?->consumption_logic === 'BASE_LOAD')
        return 'base_critica';
    if ($equipment->type?->is_thermal_sensitive && $equipment->type?->consumption_logic !== 'SEASONAL_HABIT')
        return 'climatizacion';
    return 'uso_variable';
}
```

---

### Fase 3 — Catálogo y Seeders

**MasterCleanCatalogueSeeder.php** — Renombrar categorías (sin numeraciones, sin referencias a tanques en el nombre).

**Nueva migración** — Renombrar categorías existentes en BD sin perder equipos asociados (UPDATE, no truncate).

**Nuevos tipos a agregar** (el usuario agrega los modelos manualmente, solo se crea la categoría):
- Ventiladores → categoría `Climatización`, `consumption_logic = 'SEASONAL_HABIT'`
- Termotanques → categoría `Agua Caliente`, `consumption_logic = 'BASE_THERMAL_LOSS'`

---

### Fase 4 — Lógica BASE_THERMAL_LOSS (Termotanques)

Split 70/30: 70% del consumo calculado → T2 (Base), 30% → T3 (ajuste climático por HDD).
Implementar en `Tank1BaseService` y `Tank2ClimateService`.

---

### Fase 5 — Standby

Excluir `is_standby == true` del prorrateo del remanente en `Tank3ElasticityService`.
Registrar el consumo vampiro en `audit_logs` como dato informativo.

---

## Orden de Ejecución

1. Fase 1: Corregir criterios en los 4 TankServices.
2. Fase 2: Sincronizar `getEquipmentTier()`.
3. Fase 3: Migración de renombrado + Seeder actualizado.
4. Fase 4: Implementar `BASE_THERMAL_LOSS`.
5. Fase 5: Excluir standby de la bolsa.
6. Verificar con `php artisan test tests/Feature/3\ Analisis\ y\ Ahorro/` y exportar tablas.
