# SOLAR_COVERAGE_LOGIC.md
# Lógica de Cobertura Solar (Espacio vs. Demanda)

## 1. Inputs del Usuario
* `available_area_m2`: Espacio declarado por el usuario (Techo/Fondo).
* `max_monthly_consumption_kwh`: Consumo pico (de la factura).
* `avg_monthly_consumption_kwh`: Consumo promedio anual.

## 2. Constantes Técnicas
* `PANEL_POWER_W`: 550 (Watts por panel moderno).
* `AREA_PER_PANEL`: 2.0 (m² por panel, incluye anclajes).
* `PEAK_SUN_HOURS`: 4.5 (Horas sol pico promedio conservador).
* `SYSTEM_EFFICIENCY`: 0.80 (Pérdidas térmicas/cableado).

## 3. Algoritmo de Cobertura (The Solar Match)

```php
public function calculateSolarCoverage($availableArea, $maxConsumption, $avgConsumption)
{
    // A. ¿Cuánto NECESITA? (Target)
    // KwP necesarios para cubrir el consumo máximo
    $targetKwp = $maxConsumption / (self::PEAK_SUN_HOURS * 30 * self::SYSTEM_EFFICIENCY);
    $targetPanels = ceil($targetKwp * 1000 / self::PANEL_POWER_W);
    $targetArea = $targetPanels * self::AREA_PER_PANEL;

    // B. ¿Cuánto CABE? (Limit)
    $maxPanelsFit = floor($availableArea / self::AREA_PER_PANEL);
    $maxKwpFit = ($maxPanelsFit * self::PANEL_POWER_W) / 1000;

    // C. Comparación y Resultado
    $panelsToInstall = min($targetPanels, $maxPanelsFit);
    $kwpToInstall = ($panelsToInstall * self::PANEL_POWER_W) / 1000;
    
    // Generación Estimada del sistema resultante
    $monthlyGeneration = $kwpToInstall * self::PEAK_SUN_HOURS * 30 * self::SYSTEM_EFFICIENCY;

    // Porcentajes de Cobertura
    $coverageSummer = min(100, ($monthlyGeneration / $maxConsumption) * 100);
    $coverageWinter = min(100, ($monthlyGeneration / $avgConsumption) * 100); // Usamos promedio como proxy de invierno/media

    return [
        'scenario' => ($maxPanelsFit >= $targetPanels) ? 'FULL_COVERAGE' : 'PARTIAL_COVERAGE',
        'system_size_kwp' => $kwpToInstall,
        'panels_count' => $panelsToInstall,
        'area_used' => $panelsToInstall * self::AREA_PER_PANEL,
        'area_remaining' => $availableArea - ($panelsToInstall * self::AREA_PER_PANEL),
        'coverage_summer' => round($coverageSummer, 1),
        'coverage_winter' => round($coverageWinter, 1),
        'monthly_generation_kwh' => round($monthlyGeneration, 1)
    ];
}
```

# ENERGY_TIERS_LOGIC.md
# Definición de Lógica de Calibración: Base, Hormigas y Ballenas

## 1. Concepto General
Para reconciliar el consumo teórico (lo que el usuario declara) con la factura real, no podemos ajustar todos los equipos por igual. Debemos clasificarlos en **3 Niveles (Tiers)** según su comportamiento físico y dependencia del usuario.

## 2. Definiciones de Categorías

### TIER 1: Carga Base (The Base) - "Los Intocables"
* **Definición:** Equipos que funcionan 24/7 o son críticos para la seguridad/infraestructura. No dependen del comportamiento humano inmediato.
* **Comportamiento:** Tienen prioridad absoluta en el llenado de energía. Se protegen al 100% salvo que la factura sea catastróficamente baja.
* **Equipos Típicos:**
    * Heladeras / Freezers (Cíclicos).
    * Routers Wifi / Modems.
    * Alarmas / Cámaras de Seguridad.
    * Termotanques Eléctricos (Base Pesada).

### TIER 2: Hormigas (The Ants) - "Los Estructurales"
* **Definición:** Equipos de muy bajo consumo o infraestructura básica (luces). Ajustarlos genera desconfianza porque su impacto es marginal.
* **Comportamiento:** Se protegen en segundo lugar. Si sobra energía después de la Base, las Hormigas comen todo lo que necesitan.
* **Equipos Típicos:**
    * **Iluminación:** Focos LED, Tubos (Categoría: `Iluminación`).
    * **Cargadores:** Celulares, Tablets (Categoría: `Portátiles`).
* **Exclusión Importante:** Monitores, TVs pequeñas o Ventiladores **NO** son hormigas (son Ballenas pequeñas), ya que deben apagarse si se apaga el equipo principal.

### TIER 3: Ballenas (The Whales) - "Los Ajustables"
* **Definición:** Equipos de alto consumo, uso variable, ocio o climatización. Aquí reside el 90% del error de estimación del usuario.
* **Comportamiento:** Absorben la variabilidad.
    * Si la factura es alta, las Ballenas crecen.
    * Si la factura es baja, las Ballenas se sacrifican (bajan a 0 si es necesario).
* **Equipos Típicos:**
    * Aires Acondicionados / Estufas.
    * PC Gamer / Monitores / Notebooks.
    * Televisores / Consolas.
    * Lavarropas / Microondas / Hornos.

---

## 3. Algoritmo de Distribución (Survival Waterfall)

El servicio `ConsumptionCalibrator` debe implementar esta lógica de cascada:

### Reglas de Clasificación (Código)

```php
// 1. Es BASE si...
$isBase = in_array($type, ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma', 'Termotanque Eléctrico']);

// 2. Es HORMIGA si... (NO es Base) Y (Es Iluminación O Portátil)
// NOTA: Ser estricto aquí. No incluir monitores ni ventiladores.
$isAnt = !$isBase && ($category === 'Iluminación' || $category === 'Portátiles');

// 3. Es BALLENA si...
$isWhale = !$isBase && !$isAnt;