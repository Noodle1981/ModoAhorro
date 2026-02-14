# Product Requirements Document (PRD) - Modo Ahorro

## 1. Visión del Producto
**Modo Ahorro** es una plataforma integral de eficiencia energética diseñada para usuarios residenciales y comerciales. Su objetivo principal es "desmitificar" la factura eléctrica, permitiendo a los usuarios entender, desglosar y optimizar su consumo de energía mediante auditorías digitales, recomendaciones personalizadas y proyecciones de ahorro.

## 2. El Motor de Cálculo: El Corazón del Sistema

### 2.1 Importancia Crítica
El **Motor de Cálculo** no es solo una funcionalidad más; es el **núcleo de valor** de Modo Ahorro. La credibilidad del producto descansa enteramente sobre la precisión de este motor.

*   **Confianza del Usuario**: Si el sistema estima un consumo que difiere drásticamente de la realidad sin justificación, el usuario perderá la confianza instantáneamente.
*   **Viabilidad de Inversiones**: Las recomendaciones de ROI (ej. "Instalar un termotanque solar te ahorrará $X/mes") dependen de cálculos precisos. Un error aquí puede llevar a decisiones financieras erróneas por parte del cliente.
*   **Diferenciador de Mercado**: La capacidad de distribuir consumos de forma transparente y auditable es lo que distingue a Modo Ahorro de una simple calculadora de Excel.

### 2.2 Arquitectura del Motor v3 (Febrero 2026)
El motor actual (`EnergyEngineService`) implementa un sistema de **3 Tanques Virtuales** que distribuye el consumo total de una factura de forma jerárquica y transparente:


1.  **Tanque 1 - Base (24/7)**:
    *   Equipos de consumo constante (heladeras, routers).
    *   Cálculo directo: `potencia × 24h × load_factor × días`.
    *   *Ventaja*: Consumo predecible y confiable.

2.  **Tanque 2 - Clima (Integración con Open-Meteo)**:
    *   Equipos de climatización (aires, calefacción).
    *   Integra datos climáticos reales (Grados-Día HDD/CDD) vía API Open-Meteo.
    *   Ajusta consumo según eficiencia térmica del hogar (Etiqueta A-E del Wizard Térmico).
    *   *Desafío*: Manejar fallos de API, latencia y falta de datos históricos para ciertas ubicaciones.

3.  **Tanque 3 - Elasticidad (Distribución Proporcional)**:
    *   Resto de equipos (TV, PC, luces, microondas).
    *   Distribuye el remanente de la factura (Total - Tanque1 - Tanque2) proporcionalmente según intensidad.
    *   *Ventaja*: Garantiza que la suma coincida exactamente con la factura real.

### 2.3 Sistema de Auditoría (Transparencia)
*   Cada cálculo genera un **Audit Log** en `equipment_usages.audit_logs` (JSON).
*   Dashboard de Auditoría (`/admin/audit/dashboard`) permite inspeccionar la lógica paso a paso.
*   *Ejemplo de log*: `["Tanque asignado: 2 (Clima)", "HDD detectados: 10 días", "Multiplicador térmico: 1.4 (Casa C)"]`

### 2.4 Complejidad Técnica Resuelta

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

## 6. Evolución Futura: Estrategia de Gemelos Digitales (Digital Twins)

### 6.1 Concepto
Transformar el modelo de datos estático en una **réplica virtual dinámica** de la vivienda. En lugar de calcular consumos históricos, el Gemelo Digital simulará el comportamiento físico de la casa minuto a minuto.

### 6.2 Objetivos y Beneficios
1.  **Simulaciones "What-If"**: Permitir al usuario proyectar ahorros reales antes de invertir.
    *   *Ejemplo*: "¿Cuánto ahorro si cambio el aire acondicionado por un Inverter o si aíslo el techo?"
2.  **Detección de Anomalías (Mantenimiento Predictivo)**: Comparar el consumo teórico del gemelo con el consumo real del medidor.
    *   *Alerta*: "Tu heladera consume 40% más de lo que la física dicta. Revisa los burletes."
3.  **Gamificación**: Competir contra el "Gemelo Perfecto" (la versión más eficiente de tu propia casa).

### 6.3 Hoja de Ruta de Implementación
*   **Fase 1 (Actual)**: Estabilización del Motor v3 y recolección de datos históricos (1-3 meses) para "entrenar" el modelo base.
*   **Fase 2 (Modelado Físico)**: Enriquecer el modelo de datos con propiedades térmicas (aislación, inercia) y curvas de eficiencia de equipos.
*   **Fase 3 (Simulación)**: Implementar motor de simulación física diaria para comparativa Real vs. Teórico.
