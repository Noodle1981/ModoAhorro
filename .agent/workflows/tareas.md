---
description: Prompt: Reestructuración de Vista de Auditoría y Motor de Clasificación
---

1. Contexto del Flujo de Datos
Estamos rediseñando el flujo de carga. El sistema ahora se divide en dos fases:
Fase 1 (Inventario): Se cargan los equipos en las habitaciones. Solo se guarda: nombre, cantidad, potencia_nominal_w y el flag is_validated (si el usuario ingresó el dato real o dejó el genérico del seeder).
Fase 2 (Auditoría/Uso): En la vista usage-adjustments.edit, se vinculan los equipos del inventario con una Factura específica. Aquí es donde el usuario define las Horas de Uso y la Periodicidad.
2. El Problema a Resolver
La vista de ajustes actual muestra "0 equipos" porque no está realizando el mapeo correcto entre el Inventario (equipment) y el Uso del periodo (equipment_usages). Necesito que la vista cargue todos los equipos de la entidad y los clasifique automáticamente en los 3 Tanques del motor.
3. Reglas de Clasificación para la Vista (Lógica de Tanques)
Debes programar la lógica en el Controlador/Vista para que los equipos se agrupen así:
Tanque 1 (Base Crítica): Equipos que en el inventario NO son de climatización y que el usuario marque con 24 horas y frecuencia Diariamente (Inmutables).
Tanque 2 (Clima): Todos los equipos que tengan el flag is_climatization = true en su equipment_type.
Tanque 3 (Elasticidad/Rutina): Todos los demás equipos (TV, PC, Lavarropas, etc.) que no entran en los tanques anteriores.
4. Tareas Específicas en la Vista edit de Usage-Adjustments:
Listar Equipos: Por cada equipo del inventario de la casa, crear una fila de ajuste.
Inputs de Ajuste: Cada equipo debe tener un input para avg_daily_use_hours y un select para periodicidad (opciones: Diariamente, Casi frecuentemente, Frecuentemente, Ocasionalmente, Raramente, Nunca).
Indicador de Validación: Mostrar visualmente si la potencia del equipo es "Real" (is_validated = true) o "Promedio Sugerido" (is_validated = false).
Carga de Valores por Defecto: Si no existe un registro previo en equipment_usages para ese equipo y esa factura, debe tomar como sugerencia los valores default_avg_daily_use_hours del equipment_type.
5. Requerimiento Técnico de Guardado:
Al presionar "Guardar Ajustes", el sistema debe realizar un updateOrCreate en la tabla equipment_usages, vinculando el equipment_id con el invoice_id, guardando las horas y periodicidad definidas por el usuario.
