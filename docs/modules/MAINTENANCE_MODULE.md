# MAINTENANCE_MODULE.md
# Especificación: Módulo de Gestión de Mantenimiento

## 1. Base de Datos

Necesitamos definir las tareas y llevar un registro de ejecución.

**Tabla:** `maintenance_tasks` (Catálogo de tareas)
* `equipment_type_id`: FK.
* `title`: "Limpieza de Filtros".
* `frequency_days`: 30 (mensual) o 90 (trimestral).
* `season_trigger`: 'SUMMER_START', 'WINTER_START', 'NONE'.
* `efficiency_impact`: 0.10 (10% de penalización si no se hace).

**Tabla:** `maintenance_logs` (Historial usuario)
* `device_id`: FK.
* `task_id`: FK.
* `completed_at`: Date.
* `notes`: Text.

## 2. Lógica de "Salud del Equipo"

El sistema debe calcular un **"Health Score"** o Eficiencia Real basada en el mantenimiento.

**Lógica en `ConsumptionCalculator` (Integración):**
Si una tarea crítica está vencida (ej. Filtros sucios hace 3 meses), aplicar una penalización al consumo calculado.

```php
// Ejemplo conceptual
if ($maintenanceOverdue) {
    // Si el filtro está sucio, asumimos que gasta un 10% más de lo normal
    $calculatedConsumption = $calculatedConsumption * 1.10;
    $alert = "Tu consumo es alto por falta de mantenimiento.";
}



actualizacion

# MAINTENANCE_MODULE_FINAL.md
# Especificación Técnica: Módulo de Gestión de Mantenimiento

## 1. Corrección de Estructura de Datos (Database)

**Tabla:** `maintenance_tasks` (Catálogo de tareas)
Define qué hay que hacerle a cada tipo de equipo.
* `id`: PK.
* `equipment_type_id`: FK (Relación con Aire, Heladera, etc).
* `title`: String ("Limpieza de Filtros").
* `frequency_days`: Integer (Ej: 30, 90, 180). Null si es estacional puro.
* `season_month`: Integer (Nullable). Mes de disparo estacional (Ej: 11 para Noviembre/Verano, 5 para Mayo/Invierno).
* `efficiency_impact`: Float (Ej: 0.10). Penalización de consumo si vence.

**Tabla:** `maintenance_logs` (Historial de ejecución)
Registro de cuándo el usuario hizo la tarea.
* `id`: PK.
* `equipment_id`: FK (**Corrección:** Vinculado al equipo real del usuario, no Device).
* `maintenance_task_id`: FK.
* `completed_at`: DateTime.
Devolver un objeto o array con:
* `health_score`: 100 (Sano) - (10 puntos por cada tarea crítica vencida).
* `pending_tasks`: Lista de tareas para mostrar en UI.
* `consumption_penalty_factor`: Sumatoria de `efficiency_impact` de las tareas vencidas (Ej: 1.15).

---

## 3. Integración con el Cálculo de Consumo

En `ConsumptionCalculator.php`, inyectar el factor de mantenimiento.

```php
// En calculateEquipmentConsumption...

// 1. Calcular Consumo Base (Físico + Standby)
$baseConsumption = ...;

// 2. Obtener Penalización por Mantenimiento (Cachear esto para no consultar DB siempre)
// El Service devuelve 1.0 si está sano, 1.10 si tiene filtros sucios, etc.
$penaltyFactor = $maintenanceService->getPenaltyFactor($usage->equipment);

// 3. Aplicar Penalización
$finalConsumption = $baseConsumption * $penaltyFactor;

return $finalConsumption;