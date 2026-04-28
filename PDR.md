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
- **`usage_unit`**: Define la interfaz y el motor (hours, cycles, people_proportional).
- **`energy_per_cycle`**: (NUEVO) Consumo fijo por cada uso de equipos de línea blanca (Lavarropas, etc.).
- **`social_coefficient`**: Factor para deducción automática basada en habitantes.
- **Simplificación de Catálogo**: Se eliminaron tipos duplicados por tecnología (ej: Aire Inverter). Ahora se utiliza un tipo genérico y el flag `is_inverter` en el equipo para ajustar la eficiencia física.

### Tabla `equipment_categories` (NUEVO: Categorías Universales)
- **Desacoplamiento Visual**: Se han implementado 12 categorías orientadas al usuario (Climatización y Ambiente, Refrigeración, Cocina y Preparación, etc.) para mejorar la UX.
- **Independencia del Motor**: El motor v3 ignora la categoría visual y se basa exclusivamente en la `consumption_logic` definida en `equipment_types`.

## 4. Lógica del Motor de Cálculo v3 (Evolución)
- **Metodología de 4 Tanques (Refactorización 1-Indexed)**: Se ha migrado la lógica de 0-3 a 1-4 para mejorar la legibilidad y corregir errores de agregación en reportes.
- **Tanque 1 (Certeza Matemática)**: Equipos con altísimo determinismo (Vampiros, Routers). Se restan primero de la bolsa.
- **Tanque 2 (Base/Crítica)**: Equipos esenciales (Heladeras) pero con variabilidad por eficiencia interna.
- **Tanque 3 (Climatización)**: Equipos sensibles al exterior (Aires, Estufas). Consumo dinámico.
- **Tanque 4 (Elasticidad/Sintonía Fina Predictiva)**: (EVOLUCIÓN) Ahora realiza deducciones lógicas antes de distribuir el residuo:
    1.  **Deducción Social**: Cruza `people_count` con `social_coefficient` (Microondas, Pava).
    2.  **Deducción por Ciclos**: Divide el remanente por `energy_per_cycle` para estimar usos de lavarropas/lavavajillas.
    3.  **Residuo Final**: Distribuye lo que sobra entre equipos elásticos (Iluminación, TV).
- **Aprendizaje Selectivo**: El motor solo "aprende" hábitos de equipos con patrón congelado (`has_defined_pattern`).
- **Eficiencia por Etiquetas**: Se integra la tabla `energy_label_coefficients` para ajustar el consumo teórico según la etiqueta energética (A+++, B, D, etc.).
- **Corrección de Agregación**: Se resolvió un bug en `GroupsInvoices` donde el Tanque 1 (Certeza) era omitido en los resúmenes bimensuales.

## 5. Desarrollo de Interfaz Adaptativa (v3.1)
- **Desacoplamiento de Flujos**: Se separó la persistencia de datos (`saveContextOnly`) de la ejecución del motor (`calibrateAndShowResults`), permitiendo al usuario ajustar sin recalculos pesados inmediatos.
- **Inputs Inteligentes**: La interfaz `UsageAdjustmentDetail.vue` ahora conmuta entre inputs de `horas`, `ciclos` o `proporcionalidad social` según la naturaleza física del equipo.
- **Sugerencias Predictivas**: Implementación de botones de "Aplicar sugerencia" basados en la frecuencia histórica declarada para facilitar el ajuste de ciclos.
- **Dashboard de Tanques (`EngineResults.vue`)**: Visualización de los 4 Tanques con sus "Top 5" equipos que más consumieron en cada categoría, facilitando la auditoría de la sintonía fina.

## 6. Aseguramiento de Calidad (Testing PHP)
- **Feature Tests**: Cobertura de las rutas de guardado, calibración y renderizado de resultados.
- **Unit Tests**: Validación de la lógica de cálculo por ciclos, proporcionalidad social y la estructura de retorno del servicio de análisis.
- **Bypass de Unidades**: Se corrigió un bug crítico donde los equipos de ciclos devolvían 0 si tenían una frecuencia diaria.

## 7. Próximos Pasos
- [x] Implementar las 12 Categorías Universales.
- [x] Cargar datos reales de calibración para "Casa 27" (36 equipos, 5 facturas).
- [x] Implementar la interfaz de usuario para la carga de "Ciclos" (Lavarropas).
- [ ] Implementar la curva de carga variable para equipos Inverter.
- [ ] Crear el "Analizador de Capacidad" (comparar frigorías vs metros cuadrados de la habitación).
- [ ] Refactorizar el "Analizador de Hábitos" para sugerir cambios basados en el Tanque 4.

---
**Estado del Proyecto**: Motor v3 e Interfaz Adaptativa completados y testeados. Estructura de tanques estandarizada del 1 al 4 para integración con NotebookLM.
