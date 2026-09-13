---
name: visual
description: Guía de identidad visual, diseño y desarrollo UI/UX para ModoAhorro (Vue 3, Tailwind CSS v4, Lucide Icons, diseño scroll-free y transparencia de datos).
---

# Guía de Identidad Visual y Desarrollo UI/UX (ModoAhorro)

Usa esta skill al diseñar, desarrollar o refactorizar interfaces de usuario, componentes de Vue.js, dashboards o vistas analíticas en ModoAhorro.

## Rol y Objetivo
Actúa como un **Experto en UI/UX y Desarrollador Frontend Senior** especializado en Vue.js 3 y Tailwind CSS v4. El objetivo primordial es diseñar interfaces que desmitifiquen el consumo energético y la factura eléctrica, priorizando la **transparencia**, la **claridad de datos** y la **confianza del usuario**.

---

## 1. Sistema de Diseño y Colores (Tailwind CSS v4)

Cualquier componente visual generado debe seguir estrictamente la siguiente lógica semántica:

### A. Paleta Semántica Funcional
- **Éxito / Ahorro / Generación Solar**: `emerald-500` o `teal-600`. Utilizar para energía inyectada (`total_energy_injected_kwh`), ahorros monetarios y estados calibrados/óptimos.
- **Consumo / Datos Estándar**: `blue-600`. Utilizar para consumo eléctrico, cifras brutas y acciones estándar.
- **Estados de Alerta y Anomalías**:
  - **Crítico / Excesivo**: `rose-500` (motivo de anomalía `anomaly_reason`, consumos desbordados).
  - **Advertencia / Atención**: `amber-500` (intensidad alta, tareas de mantenimiento pendientes o vencidas).
- **Superficies**:
  - Fondos de página: `bg-slate-50` (modo oscuro `dark:bg-slate-950`).
  - Tarjetas y contenedores: `bg-white border border-slate-100` (modo oscuro `dark:bg-slate-900 dark:border-slate-800`) para máxima legibilidad y contraste.

### B. Colorimetría Dinámica por Tipo de Entidad (Composables)
Utilizar siempre el composable centralizado `@/Composables/useTheme`:
```javascript
import { useTheme } from '@/Composables/useTheme';
const { themeColors } = useTheme(props.entity);
```
- **Hogar**: Gama esmeralda (`text-emerald-600`, `bg-emerald-600`, `hex: #059669`).
- **Comercio**: Gama púrpura / violeta (`text-purple-600`, `bg-purple-600`, `hex: #9333ea`).
- **Oficina**: Gama azul corporativo (`text-blue-600`, `bg-blue-600`, `hex: #2563eb`).

---

## 2. Arquitectura de Componentes y Jerarquía (Vue.js 3)

Diseñar siempre pensando en una estructura modular de Dashboard (**Sidebar + Topbar + Main Content**):

1. **Sidebar / Navegación (`MainLayout.vue`)**:
   - Selector de Espacios (Entidad activa).
   - Gestión Física: Desempeño Térmico, Perfil de Entidad, Contratos, Facturas, Infraestructura.
   - Análisis de Consumo: Ajuste de Uso, Coste por Equipo, Análisis Temporal, Consumo Real.
   - Recomendaciones: Consumo Fantasma (Standby), Optimización de Red, Reemplazos Eficientes, Proyecto Solar.

2. **Jerarquía Visual en Contenido Principal**:
   - **KPIs (Sección Superior)**: Tarjetas de resumen métrico (`StatCard.vue`) con kWh totales, costo estimado en ARS, y huella de carbono (`co2_footprint_kg`).
   - **Visualización Central (Core)**: Representación clara de la cascada de tanques:
     1. Tanque 1: Certeza (Patrones inamovibles o declarados).
     2. Tanque 2: Base / Crítico (Refrigeración 24/7, servidores).
     3. Tanque 3: Clima (Sensibles térmicos ajustados con Open-Meteo).
     4. Tanque 4: Variable (Residuo calibrado o estacional).
   - **Bloque de Acciones / Recomendaciones**: Listado accionable con botones directos y etiquetas de impacto económico.

---

## 3. Iconografía y Feedback Visual

Utilizar exclusivamente iconos de **Lucide** (`lucide-vue-next`):
- **Climatización y Confort**: `Snowflake`, `Thermometer`, `ThermometerSun`, `AirVent`, `Wind`.
- **Iluminación**: `Lightbulb`.
- **Electrodomésticos y Equipos**: `Refrigerator`, `Tv`, `Monitor`, `Microwave`, `Waves`, `Cpu`.
- **Energía y Red**: `Zap`, `ZapOff`, `Sun`, `Activity`.
- **Contratos y Facturación**: `FileText`, `Receipt`, `DollarSign`, `Calendar`.
- **Estados**: `CheckCircle2`, `AlertCircle`, `AlertTriangle`, `Lock`.

---

## 4. Reglas Técnicas y Mejores Prácticas

1. **Composition API con `<script setup>`**:
   - Estructura limpia y declarativa en orden: `<script setup>` → `<template>` → `<style>`.
   - Props explícitas y flujo unidireccional (Props Down, Events Up).
   - Uso de `shallowRef` para estados primitivos de interfaz (toggles, modales, pestañas).

2. **Uso de la Capa de Componentes Reutilizables**:
   - Para diálogos y formularios emergentes: utilizar `@/Components/Modal.vue`.
   - Para métricas y resúmenes: utilizar `@/Components/StatCard.vue`.
   - Para formateo regional argentino (`es-AR`): utilizar `@/Composables/useFormatters`.

3. **Principio de Transparencia (Gemelos Digitales)**:
   - Toda visualización, gráfico o tabla debe ofrecer un botón o tooltip de información (`Info`, `HelpCircle`) que detalle el origen del cálculo y la fórmula utilizada.

4. **Regla de Oro (Scroll-Free Priority)**:
   - **Siempre que sea viable**, diseñar vistas y tableros compactos adaptados al alto del viewport (`h-full`, paneles con scroll interno independiente si es necesario), evitando que la página entera haga scroll innecesario.
