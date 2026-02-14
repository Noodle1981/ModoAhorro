# Implementaciones Futuras & Roadmap al MVP

## Roadmap Crítico (Corto Plazo - MVP Readiness)

### 1. Robustez del Motor de Cálculo (Stress Test)
- [ ] **Prueba de Volumen Real**: Carga completa de una vivienda (50+ equipos) y simulación de un año completo de facturas.
- [ ] **Manejo de Outliers**: Detección automática de consumos imposibles para evitar reportes erróneos.
- [ ] **Validación de Fallback**: Asegurar que el sistema no colapse si falla la API de clima.

### 2. Experiencia de Usuario: Cierre de Mes
- [ ] **Reporte Mensual**: Generación automática de un resumen visual post-ajuste ("Cerrar Mes").
- [ ] **Comparativas Claras**: Gráfico simple de "Meta vs. Real" en el dashboard principal.

### 3. Optimización del Onboarding
- [ ] **Asistente de Carga Rápida**: Un wizard simplificado para cargar equipos comunes por lotes.
- [ ] **Presets Inteligentes**: Valores por defecto más precisos basados en la categoría del equipo.

### 4. Pulido de Interfaz (Look & Feel)
- [ ] **Micro-interacciones**: Feedback visual inmediato al guardar/editar (spinners, toasts mejorados).
- [ ] **Visualización Dinámica**: Gráficos interactivos en tiempo real al ajustar parámetros.

---

## Implementaciones Post-MVP (Mediano/Largo Plazo)

- **Gemelos Digitales (Digital Twins)**: Simulación física dinámica de la vivienda (Ver `PRD.MD` Sección 6).
- **Auto-completado de Dirección**: Integración con API GeoRef.
- **Nomenclatura de Áreas**: Renombrar "Habitaciones" a "Áreas" para mayor flexibilidad (Jardín, Garage, etc.).
- **Dashboard de Estadísticas Globales**: Conexión de métricas agregadas de múltiples entidades.