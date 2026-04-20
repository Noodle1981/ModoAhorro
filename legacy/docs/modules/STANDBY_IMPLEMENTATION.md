# STANDBY_IMPLEMENTATION.md
# Especificación Técnica: Módulo de Consumo Fantasma (Standby)

## 1. Objetivo
Implementar el cálculo de energía pasiva ("Vampire Power") para equipos que consumen electricidad mientras están apagados pero enchufados (TVs, Consolas, Microondas), excluyendo aquellos que físicamente no pueden tenerlo (Focos, Estufas).

---

## 2. Cambios en Base de Datos (Migrations)

### Tabla `equipment_types`
Necesitamos almacenar la potencia de standby por defecto para cada tipo de equipo.
*   **Nuevo Campo:** `default_standby_power_w` (float, default 0).
*   **Valores de Referencia (Seeder):**
    *   Televisor: 1.5 W
    *   Consola de Videojuegos: 5.0 W
    *   Microondas: 3.0 W
    *   Computadora de Escritorio: 5.0 W
    *   Impresora: 3.0 W
    *   Equipo de Audio: 4.0 W
    *   Decodificador/Modem: 5.0 W

### Tabla `equipment`
*   **Campo Existente:** `is_standby` (boolean).
*   **Uso:** Este campo ya existe en la base de datos. Se utilizará para marcar si un equipo específico del usuario está siempre enchufado.

---

## 3. Lógica de Cálculo

**Clase:** `ConsumptionService`

La fórmula de consumo diario se actualizará para incluir el consumo en espera:

```php
$dailyConsumption = 0;

// 1. Consumo Activo
$activeConsumption = ($nominalPower * $usageHours) / 1000; // kWh

// 2. Consumo Standby (Solo si el equipo tiene standby activo)
$standbyConsumption = 0;
if ($equipment->is_standby) {
    $standbyHours = 24 - $usageHours;
    // Usar potencia de standby del tipo de equipo
    $standbyPower = $equipment->type->default_standby_power_w ?? 0;
    $standbyConsumption = ($standbyPower * $standbyHours) / 1000; // kWh
}

$totalDailyConsumption = $activeConsumption + $standbyConsumption;
```