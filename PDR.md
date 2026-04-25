# PDR - Project Development Record: ModoAhorro Motor v3

## 1. Visión General
Este documento registra la evolución arquitectónica de **ModoAhorro** hacia un modelo de simulación física (Gemelo Digital). Se ha migrado de una gestión de inventario plana a una gestión de activos con comportamiento termodinámico y patrones de habitabilidad protegidos.

## 2. Problemas Resueltos (Pain Points)
- **Ruido en la Conciliación**: Los ajustes bimensuales sobrescribían los hábitos globales, causando que equipos constantes (ej: luces) perdieran su definición original.
- **Falta de Contexto Técnico**: No se diferenciaba entre tecnologías (Inverter vs. On/Off) ni tipos de transferencia de energía (Split vs. Portátil).

## 3. Arquitectura de Datos (Nuevas Estructuras)

### Tabla `equipment` (Activos Reales)
- **`has_defined_pattern`**: Flag de protección. Si es TRUE, el uso bimensual alimenta el aprendizaje global. Si es FALSE, se trata como uso variable y se inicializa en 0h cada ciclo.
- **Campos de Activo**: `brand`, `model`, `serial_number`, `energy_label`, `extra_attributes`.
- **Validadores**: `capacity` y `is_inverter` para cálculos de eficiencia.

### Tabla `equipment_types` (Inteligencia Física)
- **`consumption_logic`**: Clasificación por comportamiento (CLIMATE_DEPENDENT, CLIMATE_INEFFICIENT, CONSTANT_ELASTIC, BASE_LOAD).
- **`is_inverter_capable`**: Flag para recomendaciones de ahorro.
- **`is_thermal_sensitive`**: Identifica equipos cuya carga depende de `climate_data` (HDD/CDD).
- **`base_efficiency_ratio`**: Definición teórica de COP/EER para benchmarks.
- **`thermal_efficiency_penalty`**: Factor de corrección para equipos con diseño ineficiente (ej: Aires Portátiles).
- **`default_tank`**: Lógica de pre-asignación automática a los tanques de análisis (1: Base, 2: Clima, 3: Elasticidad).

## 4. Lógica del Motor de Cálculo v3 (Evolución)
- **Tanque 0 (Certeza)**: Implementado para equipos con `determinism_score >= 0.9`. Estos consumos se restan del total de la factura antes de la distribución de tanques.
- **Modelos Deterministas**: Se ha implementado la unidad de uso `people_proportional` que utiliza un `social_coefficient` basado en la población de la entidad.
- **Aprendizaje Selectivo**: El motor solo "aprende" hábitos de equipos con patrón congelado.
- **Diferenciación de Tanques**: 
  - Los equipos con `is_thermal_sensitive` se reconcilian contra la temperatura exterior (Tanque 2).
  - Los equipos con `default_tank = 1` se consideran inamovibles (Tanque 1).

## 5. Próximos Pasos
- [ ] Implementar la interfaz de usuario para la carga de "Ciclos" (Lavarropas).
- [ ] Implementar la curva de carga variable para equipos Inverter.
- [ ] Crear el "Analizador de Capacidad" (comparar frigorías vs metros cuadrados de la habitación).

---
**Estado del Proyecto**: Fase de Recolección de Datos Técnicos y Calibración de Activos.
