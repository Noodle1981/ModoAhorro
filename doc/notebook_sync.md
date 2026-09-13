# Skill: Sincronización NotebookLM

## Descripción
Esta habilidad asegura que el "cerebro externo" (NotebookLM) tenga siempre la versión más reciente de la arquitectura, los datos y la visión del proyecto para evitar desincronizaciones y "alucinaciones" de la IA.

## Cuándo usarla
- Después de realizar cambios en la estructura de la base de datos (migraciones).
- Después de actualizar el Motor de Cálculo o la lógica de los Tanques.
- Cuando el usuario quiera realizar consultas estratégicas de alto nivel.

## Pasos de Ejecución (Automáticos)

### 1. Actualizar Datos Crudos (CSVs)
Ejecutar el script de exportación para regenerar los archivos en `tablas/`:
- `entities.csv`
- `rooms.csv`
- `equipment.csv`
- `equipment_categories.csv`
- `equipment_types.csv` (con la nueva lógica de consumo)
- `invoices.csv`

### 2. Sincronizar Arquitectura (`ARCHITECTURE.md`)
Revisar si los cambios técnicos afectan el stack o el flujo de datos y reflejarlos en el documento.

### 3. Sincronizar Visión y Progreso (`PDR.md`)
Actualizar el estado del proyecto, los problemas resueltos y los próximos pasos.

### 4. Notificar al Usuario
Confirmar que el paquete de sincronización está listo para ser subido a NotebookLM.

---
*Regla de Oro: "Datos frescos, decisiones sabias".*
