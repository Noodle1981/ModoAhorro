# Product Requirements Document (PRD) - Modo Ahorro

## 1. Visión del Producto
**Modo Ahorro** es una plataforma integral de eficiencia energética diseñada para usuarios residenciales y comerciales. Su objetivo principal es "desmitificar" la factura eléctrica, permitiendo a los usuarios entender, desglosar y optimizar su consumo de energía mediante auditorías digitales, recomendaciones personalizadas y proyecciones de ahorro.

## 2. El Motor de Cálculo: El Corazón del Sistema

### 2.1 Importancia Crítica
El **Motor de Cálculo** no es solo una funcionalidad más; es el **núcleo de valor** de Modo Ahorro. La credibilidad del producto descansa enteramente sobre la precisión de este motor.

*   **Confianza del Usuario**: Si el sistema estima un consumo que difiere drásticamente de la realidad sin justificación, el usuario perderá la confianza instantáneamente.
*   **Viabilidad de Inversiones**: Las recomendaciones de ROI (ej. "Instalar un termotanque solar te ahorrará $X/mes") dependen de cálculos precisos. Un error aquí puede llevar a decisiones financieras erróneas por parte del cliente.
*   **Diferenciador de Mercado**: La capacidad de "calibrar" consumos teóricos contra facturas reales es lo que distingue a Modo Ahorro de una simple calculadora de Excel.

### 2.2 Complejidad Técnica
El motor de cálculo actual (`ConsumptionCalibrator`, `ConsumptionAnalysisService`) posee una alta complejidad debido a las siguientes capas de lógica:

1.  **Dependencia Climática Dinámica**:
    *   Integra APIs externas (Open-Meteo) para ajustar consumos de climatización basados en temperaturas históricas reales (Grados-Día).
    *   *Desafío*: Manejar fallos de API, latencia y falta de datos históricos para ciertas ubicaciones.

2.  **Lógica de Calibración Jerárquica ("Smart Calibration")**:
    *   No todos los consumos son iguales. El motor debe distinguir entre:
        *   **Base Crítica (Inelástica)**: Heladeras, alarmas (siempre ON).
        *   **Consumos Hormiga**: Iluminación, cargadores (predecibles pero menores).
        *   **Ballenas (Elástica)**: Aires acondicionados, calefacción (altamente variables).
    *   *Desafío*: El algoritmo debe decidir inteligentemente qué categorías "absorben" la diferencia entre el consumo declarado por el usuario y la factura real.

3.  **Factores de Corrección Multifactoriales**:
    *   Ajustes por estacionalidad (ej. temperatura de agua en red en invierno vs verano).
    *   Penalizaciones por falta de mantenimiento en equipos.
    *   Cálculo de "Consumo Vampiro" (Standby) automatizado.

4.  **Reconciliación Input vs Realidad**:
    *   El usuario ingresa datos subjetivos ("Creo que uso el aire 4 horas").
    *   La factura presenta datos objetivos ("Se consumieron 450 kWh").
    *   *Complejidad*: El motor debe "negociar" estas dos verdades para ofrecer un desglose coherente sin romper la lógica física.

## 3. Requisitos para Salida a Producción (Readiness)

Para considerar el motor listo para producción, se deben cumplir los siguientes requisitos de robustez:

*   **Validación Preventiva de Inputs**: El sistema debe rechazar o advertir sobre entradas físicamente imposibles (ej. usar un equipo 25 horas al día, o consumos que exceden la potencia contratada) antes de intentar calibrar.
*   **Estrategias de Fallback**: Si la API de clima falla, el sistema debe degradarse elegantemente a promedios estacionales pre-calculados, sin detener el servicio.
*   **Trazabilidad (Audit Logs)**: Cada cálculo debe generar un log detallado explicando *por qué* se tomó una decisión de calibración (ej. "Se redujo horas de Aire Acondicionado para coincidir con la factura").
*   **Manejo de Outliers**: Detección automática de facturas anómalas (ej. medidores rotos o errores de lectura) para no contaminar el perfil de consumo del usuario.

## 4. Estrategia de Testing (Etapas de Validación)

Para garantizar un servicio correcto, el ciclo de QA debe ser riguroso y estar dividido en etapas claras:

### Etapa 1: Testing Unitario (Unit Testing)
*   **Objetivo**: Validar la corrección matemática de fórmulas aisladas.
*   **Alcance**:
    *   Fórmulas de potencia: `kW * h * días`.
    *   Lógica de standby.
    *   Conversión de unidades y monedas.
*   **Herramientas**: PHPUnit.

### Etapa 2: Testing de Integración con Datos Sintéticos
*   **Objetivo**: Verificar que los servicios cooperan correctamente (Servicio de Clima + Servicio de Consumo + Base de Datos).
*   **Pruebas Clave**:
    *   Simular una respuesta de API de clima y verificar que el factor de corrección se aplica en la BD.
    *   Verificar que la jerarquía de calibración respeta el orden (Críticos > Ballenas).

### Etapa 3: Validación de "Casos de Oro" (Gold Standard Calibration)
*   **Objetivo**: Validar la "inteligencia" del motor comparado con resultados conocidos.
*   **Metodología**:
    *   Crear un set de 10-20 "Personas" (perfiles de usuario) con facturas reales y patrones de uso conocidos.
    *   Ejecutar el motor y comparar el resultado automático contra el análisis manual de un experto en energía.
    *   *Criterio de éxito*: El desvío entre el cálculo automático y el análisis experto debe ser menor al 10%.

### Etapa 4: User Acceptance Testing (UAT) - "Alpha Cerrada"
*   **Objetivo**: Validar la usabilidad y la percepción de valor.
*   **Acción**: Lanzamiento controlado a un grupo reducido (Friends & Family o 50 usuarios beta).
*   **Foco**: ¿El usuario entiende por qué el sistema ajustó sus horas? ¿Le parece creíble el reporte?

### Etapa 5: Monitoreo en Producción (Post-Deployment)
*   **Objetivo**: Detección temprana de anomalías.
*   **Métricas**:
    *   % de calibraciones fallidas (donde el motor no pudo reconciliar).
    *   Tiempos de respuesta de integraciones externas.
    *   Feedback directo de usuarios ("Esto no es lo que consumo").

## 5. Roadmap Sugerido hacia Producción

1.  **Fase de Estabilización**: Refactorización del código de clasificación (String matching) y Hardcoded values (ver Auditoría).
2.  **Fase de Implementación de Tests**: Creación de la suite de tests unitarios y de integración.
3.  **Fase de Calibración**: Ajuste fino de constantes (factores climáticos) usando el set de "Casos de Oro".
4.  **Soft Launch**: Despliegue en producción para segmento limitado.
