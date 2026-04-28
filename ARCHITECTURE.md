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
- **Lógica de Tanques**: Divide el consumo en 4 capas (T1: Certeza Matemática, T2: Base Inmutable, T3: Sensibilidad Climática, T4: Hábitos y Elasticidad).
- **Proporcionalidad**: Si el total calculado no coincide con la factura, aplica factores de corrección ponderados.

### B. ClimateService
Gestiona la integración con APIs meteorológicas (Visual Crossing).
- **Grados Día**: Calcula HDD (Heating Degree Days) y CDD (Cooling Degree Days).
- **Correlación**: Traduce la severidad del clima en horas de uso adicionales para equipos térmicos.

### C. Sistema de Activos (New)
- **Selective Learning**: El sistema diferencia entre "Inventario" (datos físicos) y "Hábitos" (datos de uso).
- **Patrones Congelados**: Permite que el motor de sintonía fina ignore o proteja ciertos equipos del ruido estadístico.
- **Normalización de Activos**: Los tipos de equipo son genéricos. La variabilidad tecnológica (Inverter, Ineficiente) y la eficiencia (Energy Label) se manejan mediante atributos y factores de corrección externos (`energy_label_coefficients`).
- **Sintonía Fina Predictiva**: El motor deduce hábitos del Tanque 4 (Línea blanca, Pavas, Microondas) cruzando el remanente de la factura con el `people_count` y el `energy_per_cycle`, transformando la barra de "horas" en un asistente que propone ciclos y usos lógicos.
- **Categorías Universales**: 12 categorías visuales centradas en el usuario que actúan como contenedores organizativos, desacopladas de la lógica termodinámica del motor.

### D. Capa de Persistencia y Motor (Desacoplado)
- **saveContextOnly**: Endpoint liviano que persiste los ajustes del usuario (horas, ciclos, notas) sin ejecutar el motor. Protege la entrada de datos.
- **calibrateAndShowResults**: Orquestador que ejecuta la lógica de sintonía fina bimensual y genera el "Gemelo Digital" del periodo.
- **Testing Layer**: Suite de pruebas (Feature/Unit) que garantiza que la distribución de la bolsa de energía se mantenga dentro de los parámetros físicos esperados.

## 3. Flujo de Datos
1. **Entidad/Infraestructura**: Se define el espacio físico y el inventario técnico (Potencia, Inverter, Capacidad).
2. **Facturación**: Se cargan los kWh reales del medidor.
3. **Pre-Análisis**: El `ClimateService` inyecta los datos ambientales del periodo.
4. **Sintonía Fina**: El usuario ajusta desviaciones en una interfaz adaptativa (Horas/Ciclos).
5. **Persistencia**: Se guarda el contexto bimensual.
6. **Calibración**: El motor distribuye la bolsa y muestra los resultados en el dashboard de Tanques.

## 4. Metodología de Análisis (Tanques)
- **Tanque 1 (Certeza Matemática)**: Equipos con altísimo determinismo (Standby, Vampiros). Se restan primero de la bolsa.
- **Tanque 2 (Base/Crítica)**: Equipos esenciales (Heladeras, Bombas) con variabilidad por eficiencia interna y carga térmica base.
- **Tanque 3 (Climatización)**: Equipos sensibles al exterior (Aires, Calefacción). Consumo dinámico basado en Grados Día.
- **Tanque 4 (Elasticidad/Variable)**: Hábitos declarados (Lavarropas, Microondas, TV). Absorbe el remanente final y ajusta la sintonía fina predictiva.

## 5. Principios de Diseño
- **Estética**: Diseño oscuro, glassmorphism, micro-animaciones (Wow factor).
- **Escalabilidad**: Preparado para la integración de sensores IoT (Lectura de medidor en tiempo real).
- **Física-First**: El software no solo suma números, sino que simula el comportamiento de los electrones basado en la eficiencia térmica.
- **Confianza (Testing)**: Cada cambio en la lógica del motor debe ser validado por la suite de pruebas unitarias para evitar derivas en la distribución de energía.
