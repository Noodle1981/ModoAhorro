# Product Requirements Document (PRD) - Modo Ahorro

## 1. Visión del Producto
**Modo Ahorro** es una plataforma de inteligencia energética que permite a los usuarios residenciales y comerciales entender el desglose real de su factura eléctrica. Mediante el uso de algoritmos de calibración, datos climáticos en tiempo real y perfiles térmicos, el sistema genera una auditoría digital precisa que identifica oportunidades de ahorro y justifica el consumo basado en la física del hogar.

## 2. El Motor de Cálculo v3 (Core)

El Motor de Cálculo es el componente central que transforma una factura agregada en un desglose detallado por equipo. Su objetivo es la **reconciliación inteligente** entre lo que el usuario declara y lo que la factura registra.

### 2.1 Lógica de "3 Tanques Virtuales"
El motor (`EnergyEngineService`) distribuye el consumo total siguiendo una jerarquía de rigidez física:

1.  **Tanque 1 - Base (Inmutable)**:
    *   **Equipos**: Heladeras (parte base), routers, alarmas, standby.
    *   **Lógica**: Consumo constante 24/7. Se calcula como `Potencia × 24h × Factor de Carga`.
    *   **Propiedad**: Es el consumo mínimo que la entidad tiene, independientemente del comportamiento variable.

2.  **Tanque 2 - Clima (Dinámico)**:
    *   **Equipos**: Aires acondicionados, estufas, termotanques.
    *   **Lógica**: Integra datos de **Open-Meteo** (Grados-Día HDD/CDD) y el **Perfil Térmico** de la vivienda (Etiqueta A-E).
    *   **Ajuste**: El consumo se escala según la severidad del clima y la ineficiencia térmica de la construcción (aislación, aberturas).

3.  **Tanque 3 - Elasticidad (Ajuste Final)**:
    *   **Equipos**: Iluminación, TV, pequeños electrodomésticos.
    *   **Lógica**: Distribuye el remanente del consumo basándose en la intensidad de uso (Bajo, Medio, Alto) y la potencia de los equipos restantes.

### 2.2 El Algoritmo de Recomendación
A diferencia de sistemas que fuerzan el consumo a coincidir exactamente con la factura (pudiendo validar declaraciones erróneas), Modo Ahorro utiliza un **Cálculo Recomendado**:

*   **Teórico vs. Real**: El motor calcula lo que el usuario *debería* consumir según su declaración física (`Theoretical_Total`).
*   **Límite de Tolerancia**: Si el total declarado supera la factura, el sistema permite un desvío de hasta el 30% (`Invoice × 1.30`) antes de realizar ajustes drásticos.
*   **Consumo Recomendado**: Es el valor objetivo (`recommended_kwh`) que se guarda en la factura como el perfil ideal para ese periodo, sirviendo de base para futuras comparativas.

## 3. Modelado de Consumo Avanzado

Para maximizar la precisión, el sistema (`ConsumptionAnalysisService`) implementa modelos físicos específicos:

### 3.1 Modelo de Heladeras (Impacto Social y Climático)
El consumo de refrigeración no es lineal. Se desglosa en:
*   **Carga Base**: 25% de potencia nominal para mantenimiento de frío (noche).
*   **Carga por Actividad**: Incremento del 1.5% por cada habitante de la casa (aperturas de puerta).
*   **Corrección Climática**: +/- 2% por cada grado de desviación respecto a los 20°C de temperatura ambiente promedio.

### 3.2 Eficiencia del Medidor (Input Power)
El sistema calcula el consumo basado en la **potencia de entrada** (lo que el medidor registra). La eficiencia energética de un equipo no reduce el costo de la energía entrante, sino que determina cuánta de esa energía se convierte en trabajo útil; por lo tanto, las auditorías reflejan el costo real pagado.

### 3.3 Penalización por Mantenimiento y Standby
*   **Salud del Equipo**: Los equipos con tareas de mantenimiento vencidas sufren una penalización automática en su consumo estimado (ej. filtros de aire sucios), reflejando la pérdida de eficiencia real.
*   **Consumo Vampiro**: Detección y cálculo automático del consumo en modo Standby para equipos que permanecen enchufados 24/7.

## 4. Estrategia de Eficiencia Térmica

La "Envolvente" de la vivienda es fundamental para el Tanque 2. El sistema utiliza un **Scoring Térmico (A-E)** basado en:
*   Tipo de techo y aislación térmica.
*   Calidad de aberturas (DVH vs Vidrio simple) y materiales de marcos.
*   Infiltraciones de aire detectadas.
*   Asolamiento y exposición solar.

Este score genera un multiplicador de ineficiencia que oscila entre **1.0 (Etiqueta A)** y **1.8 (Etiqueta E)**, impactando directamente en la estimación de climatización.

## 5. Trazabilidad y Auditoría
Cada proceso de calibración genera un flujo de auditoría detallado almacenado en formato JSON dentro de cada uso de equipo (`audit_logs`). Esto permite al administrador y al sistema explicar:
*   Por qué un equipo fue asignado a un tanque específico.
*   Qué factores climáticos (HDD/CDD) se detectaron para el periodo.
*   Cuál fue el porcentaje de ajuste aplicado para lograr la recomendación.

## 6. Estrategia de Validación (QA)

El ciclo de calidad se divide en:
1.  **Testing de Integración Climática**: Verificación de la conectividad con Open-Meteo y la correcta transformación de grados-día en factores de carga.
2.  **Validación de "Casos de Oro"**: Comparativa del motor contra facturas reales auditadas manualmente. El criterio de éxito es un desvío menor al 10% en el desglosado por categorías.
3.  **Stress Test de Calibración**: Simulación de declaraciones extremas para validar la robustez del "Cálculo Recomendado".

## 7. Evolución y Roadmap: Gemelos Digitales

### 7.1 Concepto de Réplica Dinámica
La evolución natural de Modo Ahorro es transformar el inventario estático en un **Gemelo Digital**. Un modelo físico que simula el comportamiento de la casa minuto a minuto.

### 7.2 Hitos Sugeridos
*   **Fase 1 (En curso)**: Consolidación del Motor v3 y modelos de consumo avanzado.
*   **Fase 2 (B2B Expansion)**: Implementación de perfiles operativos (horarios de apertura y cierre) para entidades comerciales y oficinas.
*   **Fase 3 (Simulación Predictiva)**: Herramientas "What-If" para proyectar ahorros exactos antes de realizar inversiones en eficiencia energética.
