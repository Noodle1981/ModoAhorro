# Arquitectura del Software: ModoAhorro

## 1. Stack Tecnológico
- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue.js 3 con Inertia.js (Monolito Moderno)
- **Estilos**: Tailwind CSS v4 (Aesthetics Premium)
- **Base de Datos**: Relacional (SQLite en desarrollo / MariaDB en prod)
- **Comunicación**: Protocolo Inalámbrico de Estado (Inertia) para evitar APIs REST tradicionales.

## 2. Componentes Nucleares (The Engine)

### A. ConsumptionAnalysisService
Es el cerebro del sistema. Realiza la distribución de kWh facturados entre los equipos registrados.
- **Lógica de Tanques**: Divide el consumo en 3 capas (T1: Base, T2: Clima, T3: Variable).
- **Proporcionalidad**: Si el total calculado no coincide con la factura, aplica factores de corrección ponderados.

### B. ClimateService
Gestiona la integración con APIs meteorológicas (Visual Crossing).
- **Grados Día**: Calcula HDD (Heating Degree Days) y CDD (Cooling Degree Days).
- **Correlación**: Traduce la severidad del clima en horas de uso adicionales para equipos térmicos.

### C. Sistema de Activos (New)
- **Selective Learning**: El sistema diferencia entre "Inventario" (datos físicos) y "Hábitos" (datos de uso).
- **Patrones Congelados**: Permite que el motor de sintonía fina ignore o proteja ciertos equipos del ruido estadístico.

## 3. Flujo de Datos
1. **Entidad/Infraestructura**: Se define el espacio físico y el inventario técnico (Potencia, Inverter, Capacidad).
2. **Facturación**: Se cargan los kWh reales del medidor.
3. **Pre-Análisis**: El `ClimateService` inyecta los datos ambientales del periodo.
4. **Sintonía Fina**: El usuario ajusta las desviaciones. Aquí se decide si un cambio de hábito se vuelve permanente (`has_defined_pattern`).

## 4. Metodología de Análisis (Tanques)
- **Tanque 1 (Base/Crítica)**: Equipos 24hs o esenciales. Consumo inelástico.
- **Tanque 2 (Climatización)**: Equipos sensibles al exterior. Consumo dinámico.
- **Tanque 3 (Elasticidad/Variable)**: Equipos de uso manual o discrecional. Aquí reside el mayor potencial de ahorro conductual.

## 5. Principios de Diseño
- **Estética**: Diseño oscuro, glassmorphism, micro-animaciones (Wow factor).
- **Escalabilidad**: Preparado para la integración de sensores IoT (Lectura de medidor en tiempo real).
- **Física-First**: El software no solo suma números, sino que simula el comportamiento de los electrones basado en la eficiencia térmica.
