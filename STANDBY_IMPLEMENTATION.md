# STANDBY_IMPLEMENTATION.md
# Especificación Técnica: Módulo de Consumo Fantasma (Standby)

## 1. Objetivo
Implementar el cálculo de energía pasiva ("Vampire Power") para equipos que consumen electricidad mientras están apagados pero enchufados (TVs, Consolas, Microondas), excluyendo aquellos que físicamente no pueden tenerlo (Focos, Estufas).

---

## 2. Cambios en Base de Datos (Migrations)

Necesitamos almacenar la potencia de standby y una bandera para saber qué categorías lo permiten.

**Tabla `equipment_types`:**
Agregar columna para el valor por defecto.
```php
$table->float('default_standby_power_w')->default(0)->comment('Watts consumidos en modo espera');