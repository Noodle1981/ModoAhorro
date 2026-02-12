# Motor Energético v3 - Arquitectura de 3 Tanques

## Resumen
El Motor Energético v3 es el núcleo de cálculo de consumo de ModoAhorro. Reemplaza la lógica anterior de "Calibrador" por un sistema jerárquico de **3 Tanques Virtuales** que distribuye el consumo total de una factura entre los equipos del hogar de forma inteligente y transparente.

## Problema que Resuelve
**Antes (v2):** El sistema intentaba "adivinar" el consumo de cada equipo usando un calibrador opaco. Si la suma no coincidía con la factura, aplicaba factores de corrección arbitrarios.

**Ahora (v3):** El motor:
1. Identifica equipos de **consumo constante** (Tanque 1: Base 24/7).
2. Estima el **consumo climático** usando datos reales del clima y la eficiencia térmica del hogar (Tanque 2).
3. Distribuye el **remanente** entre el resto de equipos según su intensidad (Tanque 3: Elasticidad).

## Arquitectura

### Servicios Involucrados
```
EnergyEngineService (Orquestador)
    ├── ClimateService (Grados-Día HDD/CDD)
    ├── ThermalProfileService (Eficiencia A-E)
    └── ConsumptionAnalysisService (Integración y Reportes)
```

### Tanque 1: Base (24/7)
**Equipos:** Heladera, Router WiFi, cualquier equipo que funcione 24 horas.
**Cálculo:**
```php
consumo_base = Σ (potencia_watts × 24h × load_factor × días_periodo) / 1000
```
**Ejemplo:** Heladera de 150W con `load_factor=0.35` (ciclos de motor):
```
150W × 24h × 0.35 × 30 días = 37.8 kWh/mes
```

### Tanque 2: Clima
**Equipos:** Aire acondicionado, calefacción, ventiladores.
**Cálculo:**
```php
// 1. Obtener Grados-Día del clima real
$degreeDays = ClimateService::getDegreeDays($lat, $lon, $startDate, $endDate);
$hdd = $degreeDays['heating_days'];  // Días acumulados de frío
$cdd = $degreeDays['cooling_days'];  // Días acumulados de calor

// 2. Obtener multiplicador de eficiencia térmica del hogar
$multiplier = ThermalProfileService::calculateMultiplier($entity);
// A=1.0 (eficiente), E=1.8 (ineficiente)

// 3. Estimar consumo
$consumo_clima = ($hdd × potencia_calefaccion + $cdd × potencia_refrigeracion) 
                 × $multiplier × load_factor / 1000;
```

**Ejemplo:** Casa con etiqueta **C** (multiplicador 1.4), estufa de 2000W, 10 días de frío acumulados:
```
10 HDD × 2000W × 1.4 × 1.0 (load_factor resistencia) / 1000 = 28 kWh
```

### Tanque 3: Elasticidad
**Equipos:** TV, PC, luces, microondas (todo lo que no es Base ni Clima).
**Cálculo:**
```php
$remanente = $factura_total_kwh - $tanque1 - $tanque2;

// Distribuir proporcionalmente según "peso" de cada equipo
$peso_equipo = potencia × horas_uso × intensidad_factor;
$consumo_equipo = ($peso_equipo / Σ pesos) × $remanente;
```

**Ejemplo:** Factura de 500 kWh, Tanque 1 = 100 kWh, Tanque 2 = 150 kWh.
- Remanente: 250 kWh
- TV (100W × 5h × 0.85 = 425 puntos)
- PC Gamer (500W × 4h × 0.7 = 1400 puntos)
- Total puntos: 1825

Consumo TV: (425 / 1825) × 250 kWh = **58.2 kWh**
Consumo PC: (1400 / 1825) × 250 kWh = **191.8 kWh**

## Sistema de Auditoría
Cada vez que el motor procesa una factura, guarda un **Audit Log** en la tabla `equipment_usages`:
```json
{
  "audit_logs": [
    "Tanque asignado: 2 (Clima)",
    "HDD detectados: 10 días",
    "Multiplicador térmico: 1.4 (Casa C)",
    "Consumo estimado: 28 kWh"
  ]
}
```

### Dashboard de Auditoría
Ruta: `/admin/audit/dashboard`
- Lista facturas procesadas.
- Permite expandir cada factura para ver el log detallado de cada equipo.
- Útil para depurar discrepancias o explicar al usuario por qué un equipo consumió X kWh.

## Integración con Open-Meteo
El `ClimateService` consulta la API de Open-Meteo para obtener temperaturas históricas:
```php
$response = Http::get('https://archive-api.open-meteo.com/v1/archive', [
    'latitude' => $lat,
    'longitude' => $lon,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'daily' => 'temperature_2m_mean',
]);

foreach ($temps as $temp) {
    if ($temp > 24) $cdd += ($temp - 24);  // Refrigeración
    if ($temp < 18) $hdd += (18 - $temp);  // Calefacción
}
```

## Wizard Térmico
Permite al usuario calificar su hogar respondiendo preguntas sobre:
- Tipo de techo (chapa, losa, aislado)
- Tipo de ventanas (simple, doble vidrio)
- Orientación y exposición solar
- Detección de chifletes

El sistema asigna una **Etiqueta Energética** (A-E) y ajusta el multiplicador del Tanque 2.

## Tests Unitarios
- `ClimateServiceTest`: Valida cálculos matemáticos de HDD/CDD.
- `EnergyEngineTest`: Simula una factura de 500 kWh y verifica distribución en tanques.

## Próximos Pasos (Roadmap)
- Integración con medidores inteligentes (IoT) para datos en tiempo real.
- Machine Learning para ajustar `load_factor` dinámicamente.
- Alertas proactivas de consumo anómalo.
