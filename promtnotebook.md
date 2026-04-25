# Vademécum de Eficiencia: Aires de Ventana

Los Aires de Ventana son una pieza clave para el catálogo porque representan el "puente ineficiente" entre la tecnología vieja y la nueva. En la física de Modo Ahorro, estos equipos se manejan con un perfil de pérdida estructural, similar al portátil pero con matices distintos.

### 1. Perfil Técnico (Catálogo Maestro)
El aire de ventana tiene una característica física crítica: el gabinete atraviesa la pared. Esto genera un puente térmico constante (incluso apagado, entra aire por las rejillas).

| Campo | Valor Sugerido | Razón Técnica |
|-------|----------------|---------------|
| Lógica de Tanque | CLIMATE_DEPENDENT | Depende totalmente de la temperatura exterior. |
| Thermal Penalty | 0.20 (20%) | Pierde eficiencia por filtraciones y falta de aislamiento en su chasis. |
| Determinism Score | 0.4 | Ciclo On/Off predecible pero sensible al clima. |
| Default Watts | 1200W - 1800W | Equipos de baja/media potencia comparados con Splits. |

### 2. Coeficientes de Eficiencia: Aire de Ventana
| Equipo | A+++ / A++ | A | B | C | D o inferior |
|--------|------------|---|---|---|--------------|
| **Aire de Ventana** | 0.75* | 1.00 | 1.15 | 1.30 | 1.65 |
*\*Nota: El valor 0.75 representa el mejor escenario posible con tecnología Inverter aplicada a este formato.*

### 3. El factor "Fuga de Dinero"
Si el Admin quiere convencer al usuario de saltar de un Aire de Ventana viejo a la "Mejor Opción", el Motor v3 aplica:
- **Por Etiqueta**: Pierde un 65% extra (Coef. 1.65 vs 1.0).
- **Por Formato**: Pierde un 20% extra por el puente térmico en la pared.
- **Total**: El usuario gasta casi el doble (**x1.85**) que con un Split moderno Inverter.

---

# Arquitectura de Administración y Motor v3 (Fuente de Verdad)

Se ha implementado una "Torre de Control" para el Super Admin que permite gestionar la inteligencia del sistema sin modificar código.

### 1. El Catálogo Maestro (Física Fundamental)
- **Ruta**: `/sistema/catalogo` (Tabla `equipment_types`).
- **Función**: Define los guardriales físicos (Min/Max Watts), la penalidad por ineficiencia (Penalty) y el grado de determinismo (T0 score). 

### 2. Matriz de Coeficientes de Eficiencia (Multiplicadores)
- **Ruta**: `/sistema/eficiencia` (Tabla `energy_label_coefficients`).
- **Lógica**: Transforma una etiqueta (A+++, B, D) en un factor multiplicador de consumo.
    - Clase A = 1.0 (Punto neutro).
    - Clase D = 1.6 (Consume un 60% más que la base).
    - Clase A+++ = 0.65 (Ahorra un 35% respecto a la base).

### 3. Benchmarks de Referencia (El "Standard Gold")
- **Ruta**: `/sistema/benchmarks` (Tabla `equipment_benchmarks`).
- **ROI**: El sistema compara el equipo actual del usuario contra el Benchmark del Admin. La diferencia en kWh es la "Fuga de Dinero" que se muestra en el informe.

### 4. Lógica de Tanques (Cascada de Consumo)
- **Tanque 0 (Certeza)**: Equipos constantes (Router, Cámaras). Se restan primero.
- **Tanque 1 (Base)**: Equipos de ciclo fijo (Heladeras).
- **Tanque 2 (Clima)**: Equipos dependientes de temperatura (Aires, Estufas).
- **Tanque 3 (Elasticidad)**: El resto del consumo (Iluminación, TV, Ocio). Absorbe el error de la factura.

---

## Log de Cambios Recientes
- **Compactación UI**: Reducción de márgenes y paddings en Navbar, Contratos e Infraestructura para mejorar el scroll.
- **Protección de Traducción**: Atributo `translate="no"` añadido a selectores críticos para evitar que "C" y "E" se traduzcan como "do" y "mi".
- **Tecnología Inverter**: Implementación de toggle y lógica de ahorro del 15% extra para equipos Inverter.