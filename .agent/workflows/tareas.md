---
description: Roadmaps de testing
---

¡Excelente iniciativa! Como ya decidimos enfocarnos primero en **Hogar**, vamos a aplicar una estrategia de "Shift-Left Testing", probando desde el diseño antes de escribir código complejo.

Aquí tienes un plan integral de pruebas ("Master Test Plan") diseñado específicamente para la arquitectura de **Modo Ahorro**, cruzando la teoría general con tu PRD y tu Base de Datos.

### 1. El Corazón: Testing del Motor de Cálculo (Caja Blanca)
El PRD establece que el motor de 3 Tanques es el núcleo de valor. Aquí no probamos si "se ve bonito", probamos si la matemática es perfecta.

*   **Pruebas de Unidad (Unit Testing) con PHPUnit:**
    *   **Fórmula Base:** Validar que `potencia * 24h * load_factor` nunca exceda el total de horas del mes.
    *   **Lógica de Tanques:** Verificar que la suma de `Tanque 1 + Tanque 2 + Tanque 3` sea **exactamente igual** al `total_energy_consumed_kwh` de la tabla `invoices`.
    *   **Idempotencia:** Si ejecuto el cálculo 10 veces sobre la misma factura, debe dar exactamente el mismo resultado (crucial para no confundir al usuario).

*   **Pruebas de Valores Límite (Boundary Value Analysis):**
    *   *Caso:* Un usuario dice que usa el aire acondicionado 25 horas al día. El sistema debe rechazarlo o toparlo a 24.
    *   *Caso:* Factura con consumo `0`. El motor no debe dividir por cero.
    *   *Caso:* `load_factor` en la tabla `equipment_types` mayor a 1.0 (imposible físico).

### 2. Los Datos: Testing de Integridad y Base de Datos
Tu base de datos es relacional y compleja. Si los datos están sucios, el motor fallará.

*   **Data Constrain Testing:**
    *   **Clima Duplicado:** Intentar insertar dos registros en `climate_data` con la misma `date`, `latitude` y `longitude`. La base de datos debe rechazarlo (constraint unique).
    *   **Fechas Incoherentes:** Verificar que en la tabla `contracts`, la `end_date` no sea anterior a la `start_date`.

*   **Pruebas de Integridad Referencial:**
    *   Si borras un usuario de la tabla `users`, verificar qué pasa con sus registros en `equipment_usages`. ¿Se borran en cascada o quedan huérfanos? (Deberían gestionarse con cuidado para mantener históricos).

*   **Validación de API Externa (Mocking):**
    *   Simular que Open-Meteo se cae o devuelve `null`. El PRD exige una "Estrategia de Fallback" (usar promedios estacionales). Debes testear que el sistema cambie a ese modo sin romperse.

### 3. La Validación: "Casos de Oro" y UAT (Caja Negra)
Aquí es donde validas con la realidad ("Friends & Family").

*   **Etapa 3: Casos de Oro (Gold Standard):**
    *   Toma 10 facturas reales de tus amigos.
    *   Haz el cálculo manual en Excel.
    *   Corre el Motor v3.
    *   **Prueba de Aceptación:** Si la diferencia es >10%, es un *Bug Crítico* de lógica. Revisa los `efficiency_benchmarks`.

*   **Pruebas de Usabilidad (UAT):**
    *   Dale el reporte a tu "tía" (usuario no técnico). Pregúntale: *"¿Entiendes por qué gastaste más en calefacción?"*. Si no entiende el gráfico, el test falló, aunque la matemática sea correcta.

### 4. El Futuro: Pruebas de Estrés y Escalabilidad
Pensando en cuando escales a Pymes y Fincas.

*   **Pruebas de Estrés (Stress Testing):**
    *   Simula que 100 usuarios suben su PDF al mismo tiempo. ¿El servidor aguanta el procesamiento OCR y el cálculo matemático simultáneo?
*   **Performance Testing:**
    *   Mide cuánto tarda en responder la consulta SQL que une `equipment_usages` con `climate_data` cuando tengas 1 millón de registros de clima. Si tarda más de 2 segundos, necesitas optimizar índices.

### 5. Escenarios de "Mantenimiento y Anomalías"
Para validar tu idea de detectar "goteo" o fallas.

*   **Testing de Lógica de Negocio:**
    *   *Escenario:* En la tabla `maintenance_logs`, no hay registro de limpieza de filtros de aire acondicionado en 180 días.
    *   *Resultado Esperado:* El motor debe aplicar el `efficiency_impact` (penalización) definido en `maintenance_tasks` y aumentar el consumo estimado en el Tanque 3.

### Herramienta Sugerida: Matriz de Trazabilidad
Para no perderte, crea un Excel simple:
| ID | Requerimiento (PRD) | Prueba (Test Case) | Resultado Esperado | Estado |
|---|---|---|---|---|
| 01 | Motor debe usar 3 Tanques | Verificar JSON en `audit_logs` | Ver keys Tanque 1, 2, 3 | Pendiente |
| 02 | Fallback de Clima | Desconectar internet y correr cálculo | Usar promedio histórico | Pendiente |

¿Te gustaría que usemos NotebookLM para generar **automáticamente** una lista de 20 casos de prueba (Test Cases) listos para copiar y pegar en tu gestor de tareas, basándonos en las reglas de tu base de datos?