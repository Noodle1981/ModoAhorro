# DATA_ANOMALY_LOGIC.md
# Especificación: Manejo de Anomalías y Datos No Representativos (Outliers)

## 1. Contexto del Problema
Cuando un usuario activa el "Modo Vacaciones" o tiene un periodo de consumo atípico (ej: casa vacía por 30 días), ese dato de consumo es **Real** para la facturación actual, pero **Tóxico** para las proyecciones futuras (Solar, ROI de equipos, etc.).

Incluir un mes de consumo casi nulo en el promedio anual baja artificialmente la media, llevando a recomendaciones erróneas (ej: sugerir un sistema solar demasiado pequeño).

---

## 2. Actualización de Base de Datos

Necesitamos "marcar" las facturas que no deben usarse para estadística.

**Tabla `invoices`:**
Agregar columnas de control:
```php
$table->boolean('is_representative')->default(true)->comment('Define si esta factura sirve para promedios históricos');
$table->string('anomaly_reason')->nullable()->comment('Ej: VACATION, METER_ERROR, HOUSE_RENOVATION');