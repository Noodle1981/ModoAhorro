Desvincular Ajustes de Uso del Inventario Físico
Actualmente, cuando se realiza un ajuste de uso para una factura específica, se sobrescriben los valores predeterminados del equipo en la "Gestión de Infraestructura". Esto genera ruido y obliga al usuario a re-ajustar equipos variables todos los meses.

El objetivo es separar el "Uso Base" (fijo) del "Uso Variable" (específico por periodo).

Cambios Propuestos
Base de Datos
[NEW] 
2026_04_24_000000_add_has_defined_pattern_to_equipment.php
Añadir columna has_defined_pattern (boolean, default false) a la tabla equipment.
Backend (Laravel)
[MODIFY] 
Equipment.php
Añadir has_defined_pattern a $fillable y $casts.
[MODIFY] 
AnalysisController.php
usageAdjustmentDetail:
Pasar el campo has_defined_pattern a la vista.
Cambiar la lógica de inicialización de uso: si el equipo no tiene un Patrón Definido y no tiene un registro previo para esa factura, inicializar las horas en 0.
saveUsageAdjustment:
Actualizar el flag has_defined_pattern en el modelo Equipment.
Solo actualizar las horas base del equipo si has_defined_pattern es true.
Frontend (Vue)
[MODIFY] 
UsageAdjustmentDetail.vue
Añadir un toggle/checkbox de "Patrón Definido" en cada fila de equipo.
Incluir este valor en el formulario que se envía al servidor.
Visualmente destacar los equipos que están en "modo variable" (0h) para que el usuario sepa que debe ajustarlos.
Plan de Verificación
Pruebas Manuales
Persistencia: Ajustar un equipo con "Patrón Definido" activado y verificar que se actualiza en la sección de Infraestructura.
Aislamiento: Ajustar un equipo SIN "Patrón Definido" y verificar que en Infraestructura mantiene sus valores anteriores.
Reset: Crear un ajuste para un nuevo periodo y verificar que los equipos variables aparecen con 0 horas, mientras que los constantes mantienen su patrón.