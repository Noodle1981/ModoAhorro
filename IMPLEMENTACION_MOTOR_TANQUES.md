# Refactorización del Motor de Tanques — Clasificación por Patrón Fijo del Usuario

## Objetivo

El motor actual clasifica los equipos en Tanques usando reglas del **catálogo** (seeder):
`consumption_logic`, `determinism_score`, `is_thermal_sensitive`. Esto ignora la intención del usuario.

El motor nuevo debe clasificar usando **exclusivamente** el flag `has_defined_pattern` que el
**usuario activa manualmente** en la vista "Sintonía Fina". El catálogo solo sirve como
sub-clasificador *dentro* de los equipos que el usuario ya marcó como fijos.

---

## Filosofía del Nuevo Motor (Leer atentamente)

```
USUARIO → marca "Patrón Fijo" en los equipos que conoce que son predecibles
MOTOR   → recibe la lista y sub-clasifica los fijos en Tanques según criterios técnicos
MOTOR   → lo que no es fijo va todo a Tank Variable, y recibe el kWh sobrante

Flujo:
  1. Tank CRÍTICO   ← Patrón Fijo + (24h ó Refrigeración ó Conectividad/Seguridad)
  2. Tank CLIMÁTICO ← Patrón Fijo + categoría Climatización (is_thermal_sensitive = true)
  3. Tank CERTEZA   ← Patrón Fijo + cualquier otra categoría (IT, Entret., Lavado, etc.)
  4. Tank VARIABLE  ← Sin Patrón Fijo (todo lo demás, recibe el kWh sobrante)
```

**Regla de oro**: Ningún servicio de tanque debe leer `consumption_logic` ni `determinism_score`
para decidir si un equipo *entra* a ese tanque. Solo `has_defined_pattern` decide si
el equipo es fijo. El catálogo solo aplica para los cálculos de kWh (load_factor, penalizaciones, etc.).

---

## Contexto: Estado Actual del Código

### Archivos a modificar

| Archivo | Problema actual |
|---|---|
| `app/Services/Tanks/Tank0CertaintyService.php` | Filtra por `has_defined_pattern OR determinism_score >= 0.9`. El `determinism_score` del catálogo no debe determinar la entrada al tanque. |
| `app/Services/Tanks/Tank1BaseService.php` | Filtra por `consumption_logic === 'BASE_LOAD'` ignorando completamente al usuario. |
| `app/Services/Tanks/Tank2ClimateService.php` | Filtra por `is_thermal_sensitive AND NOT SEASONAL_HABIT`. Correcto en categoría, pero debe agregar el gate de `has_defined_pattern`. |
| `app/Services/Tanks/Tank3ElasticityService.php` | Toma todo lo que sobra. Correcto conceptualmente, pero debe asegurarse que reciba solo los que NO tienen `has_defined_pattern`. |
| `app/Http/Controllers/AnalysisController.php` | Método `calibrateAndShowResults` no guarda `has_defined_pattern` en el `EquipmentUsage`. |

---

## Cambios por Archivo

---

### [MODIFY] `app/Services/Tanks/Tank0CertaintyService.php`

**Función nueva del filtro**:
```php
// NUEVO (CORRECTO):
// Solo entran equipos que el USUARIO marcó como Patrón Fijo
// Y que NO sean ni de Refrigeración/Conectividad (→ Tank Crítico)
// NI de Climatización (→ Tank Climático)
return $eq->has_defined_pattern === true
    && !$this->isCritical($eq)
    && !($eq->type?->is_thermal_sensitive);
```

**Método `isCritical()`**:
```php
private function isCritical(Equipment $eq): bool
{
    $criticalCategories = ['Refrigeración', 'Conectividad y Seguridad'];
    $categoryName = $eq->type?->category?->name ?? '';
    $hours = $eq->avg_daily_use_hours ?? 0;

    return in_array($categoryName, $criticalCategories)
        || $hours >= 23.5;
}
```

---

### [MODIFY] `app/Services/Tanks/Tank1BaseService.php`

**Función nueva del filtro**:
```php
// NUEVO (CORRECTO):
return $eq->tank_assignment === null
    && $eq->has_defined_pattern === true
    && $this->isCritical($eq);
```

---

### [MODIFY] `app/Services/Tanks/Tank2ClimateService.php`

**Función nueva del filtro**:
```php
// NUEVO: Solo entran los climáticos que el usuario marcó como Patrón Fijo
return $eq->tank_assignment === null
    && $eq->has_defined_pattern === true
    && $eq->type?->is_thermal_sensitive === true;
```

---

### [MODIFY] `app/Http/Controllers/AnalysisController.php`

**En `calibrateAndShowResults`**, persistir el flag antes de calibrar:
```php
$equipment = Equipment::find($eqId);
if ($equipment) {
    $equipment->update([
        'has_defined_pattern' => $data['has_defined_pattern'] ?? false,
    ]);
}
```

---

## Criterios de Verificación

1. **Escenario 1**: Sin patrones fijos → Todo a Tank Variable.
2. **Escenario 2**: Heladera (24h) como patrón fijo → Heladera a Tank Crítico, resto a Variable.
3. **Escenario 3**: Aire Split como patrón fijo → Aire Split a Tank Climático.
4. **Escenario 4**: PC Gamer como patrón fijo → PC Gamer a Tank Certeza.
