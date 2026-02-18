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

- **Auto-completado de Dirección**: Integración con API GeoRef.
- **Nomenclatura de Áreas**: Renombrar "Habitaciones" a "Áreas" para mayor flexibilidad (Jardín, Garage, etc.).
- **Dashboard de Estadísticas Globales**: Conexión de métricas agregadas de múltiples entidades.
- **Integración Mercado Libre API** *(requiere producción)*: Usar el campo `meli_search_term` de cada `EfficiencyBenchmark` para consultar la API de MeLi y traer precios reales, fotos y links de compra en tiempo real. Reemplaza los precios estáticos del seeder por datos actualizados automáticamente. Ver `docs/modules/REPLACEMENT_MODULE.md`.

---

## Gemelo Digital Energético (Digital Twin)

> Un gemelo digital es una réplica virtual de una entidad física (hogar, edificio, comercio) que permite simular su comportamiento energético, predecir consumos y evaluar escenarios de mejora sin intervenir el mundo real.

### ¿Por qué ModoAhorro ya tiene la base?

La arquitectura actual cubre la mayoría de los componentes necesarios:

| Componente | Estado | Modelo/Servicio |
|---|---|---|
| Inventario físico (equipos por ambiente) | ✅ Implementado | `Equipment`, `Room`, `Entity` |
| Consumo real histórico | ✅ Implementado | `Invoice`, `EquipmentUsage` |
| Perfil climático de la zona | ✅ Implementado | `ClimateDataService` |
| Perfil térmico del edificio | ✅ Implementado | `thermal_profile` en `Entity` |
| Motor de análisis energético | ✅ Implementado | `EnergyEngineService` |
| Base de alternativas eficientes | ✅ Implementado | `EfficiencyBenchmark` |
| Simulación de escenarios "¿qué pasaría si...?" | 🔲 Pendiente | — |
| Predicción de consumo futuro | 🔲 Pendiente | — |
| Visualización interactiva del gemelo | 🔲 Pendiente | — |

### Arquitectura propuesta

```
[Entidad Física]
    └── Ambientes (Rooms)
         └── Equipos (Equipment) ──► EfficiencyBenchmark
                                          │
                                          ▼
                              [Motor de Simulación]
                                ├── Escenario actual (baseline)
                                ├── Escenario A: reemplazar equipo X
                                ├── Escenario B: agregar paneles solares
                                └── Escenario C: cambiar hábitos de uso
                                          │
                                          ▼
                              [Resultado Comparativo]
                                ├── Consumo estimado (kWh/mes)
                                ├── Costo estimado (ARS/mes)
                                ├── Ahorro vs. baseline
                                └── ROI y tiempo de recupero
```

### Roadmap de implementación

#### Fase 1 — Simulador de Escenarios (corto plazo)
- [ ] `ScenarioSimulatorService`: recibe una entidad + lista de cambios propuestos y devuelve el consumo proyectado.
- [ ] UI de "Simulador": el usuario puede activar/desactivar equipos, cambiarlos por alternativas eficientes y ver el impacto en tiempo real.
- [ ] Integrar con `EfficiencyBenchmark` para sugerir automáticamente los mejores reemplazos.

#### Fase 2 — Predicción de Consumo (mediano plazo)
- [ ] Modelo de predicción basado en histórico de facturas + perfil climático.
- [ ] Alertas proactivas: "Este mes vas a consumir más de lo habitual por el calor".
- [ ] Comparativa con entidades similares (benchmarking entre pares).

#### Fase 3 — Gemelo Visual (largo plazo)
- [ ] Visualización interactiva del plano de la entidad con consumo por ambiente.
- [ ] Dashboard en tiempo real si se integra con medidores inteligentes (smart meters).
- [ ] API pública para que terceros (municipios, distribuidoras) consulten datos agregados.

### Valor diferencial

El gemelo digital convierte ModoAhorro de una herramienta de **análisis histórico** a una plataforma de **toma de decisiones energéticas**, permitiendo:
- A usuarios residenciales: saber exactamente qué cambiar primero para maximizar el ahorro.
- A municipios/organismos: simular el impacto de políticas de eficiencia energética a escala.
- A instaladores/proveedores: ofrecer presupuestos basados en datos reales del cliente.