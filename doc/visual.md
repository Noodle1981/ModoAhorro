# Guía de Identidad Visual y Desarrollo UI/UX — ModoAhorro

**Proyecto:** ModoAhorro  
**Stack Visual:** Vue.js 3 (`<script setup>`) + Inertia.js + Tailwind CSS v4 + Lucide Icons / Heroicons  
**Última Actualización:** 2026-08-17  

---

## 1. Filosofía de Diseño Frontend
Diseñar interfaces que desmitifiquen la factura eléctrica, priorizando la **transparencia, claridad y confianza** del usuario:
- **Transparencia radical:** Cada vez que generes un gráfico o tabla de datos, incluye una pequeña sección de *"Auditoría"* o *"Info"* (tooltip/badge explicativo) que detalle de dónde proviene el cálculo físico o estadístico (filosofía de Gemelos Digitales).
- **Regla de oro de Scroll:** *Si se puede evitar que la vista haga scroll excesivo en desktop, darle prioridad.* Mantener la información densa pero legible y ordenada en grids limpios.

---

## 2. Sistema de Colores y Tokens Semánticos (Tailwind CSS v4)

Cualquier componente generado debe seguir estrictamente esta lógica semántica:

| Concepto / Estado | Color / Clase Tailwind | Propósito / Uso |
| :--- | :--- | :--- |
| **Éxito / Ahorro / Solar / Hogar** | `emerald-500` / `teal-600` | Inyección solar, métricas de ahorro, identidad residencial |
| **B2B / Comercio & Oficina** | `blue-600` / `indigo-600` | Entidades comerciales, horarios operativos, balances de potencia |
| **Heladerías / Procesos Frío** | `purple-600` / `cyan-600` | Sensibilidad térmica extrema, frío 24/7 |
| **Alerta Crítica / Anomalía** | `rose-500` / `red-600` | Detección de consumos vampiro desmedidos, desvíos graves |
| **Advertencia / Atención** | `amber-500` / `yellow-600` | Intensidad alta, mantenimiento preventivo vencido |
| **Superficies & Fondos** | `bg-slate-50` / `bg-white` | Fondos de página limpios y tarjetas contrastadas |
| **Bordes & Glassmorphism** | `border-slate-200/80` | Tarjetas redondeadas (`rounded-2xl` / `rounded-3xl`) con micro-sombras |

---

## 3. Arquitectura de Componentes (Vue.js + Inertia.js)

Estructura tipo Dashboard (Sidebar + Main Content):

- **Sidebar de Navegación:**
  - Selector de Espacios / Entidad Activa (`Mis Espacios`).
  - Facturas & Servicios (`Invoices`).
  - Inventario Físico (`Equipment / Rooms`).
  - Calibración de Tanques & Diagnóstico (`Ajuste de Uso`).
  - Log de Auditoría y Exportación.

- **Jerarquía de Datos (Main Content):**
  1. **KPIs Principales (Top):** Tarjetas con costo proyectado/real, kWh totales facturados y huella de CO₂.
  2. **Visualización Core:** Gráfico / Desglose granular de los **4 Tanques** (Certeza, Base Operativa, Climatización, Variable).
  3. **Acciones & Oportunidades:** Listado priorizado de recomendaciones de eficiencia basadas en benchmarks.

---

## 4. Iconografía y Feedback Visual

- Usar exclusivamente **Lucide Icons** o **Heroicons** (como componentes Vue).
- **Categorías clave:**
  - `Snowflake`: Climatización y Termodinámica.
  - `Lightbulb`: Iluminación y Equipamiento Eléctrico.
  - `Flame`: Multi-Vector / Gas Natural y Hornos.
  - `Sun`: Radiación solar y autogeneración.
  - `Thermometer`: Temperatura exterior (Open-Meteo).
  - `FileText`: Contratos y Facturas.
  - `Zap`: Potencia contratada, tarifas y picos.

---

## 5. Reglas Técnicas de Implementación

1. **Tailwind CSS 4:** Usar configuración CSS-first y utilidades directas.
2. **Vue.js 3:** Siempre usar `<script setup>` con `defineProps`, `defineEmits` y `useForm` de `@inertiajs/vue3`.
3. **Componentes Atómicos:** Separar tarjetas complejas en componentes modulares en `resources/js/Components/`.
