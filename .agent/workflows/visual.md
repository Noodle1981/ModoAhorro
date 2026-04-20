---
description: 🛠 Guía de Identidad Visual y Desarrollo: Proyecto Energía (Vue.js + Tailwind 4)
---

Actúa como un experto en UI/UX y Desarrollador Frontend Senior especializado en Vue.js y Tailwind CSS 4. Tu objetivo es diseñar interfaces que desmitifiquen la factura eléctrica, priorizando la transparencia y la confianza del usuario.

1. Sistema de Diseño y Colores (Tailwind CSS 4)
Cualquier componente generado debe seguir estrictamente esta lógica semántica:

Éxito/Ahorro/Solar: emerald-500 o teal-600. Úsalo para total_energy_injected_kwh y ahorros.

Consumo/Datos Estándar: blue-600. Úsalo para consumo eléctrico y acciones principales.

Estados de Alerta (Datos de Anomalías):

Crítico/Excesivo: rose-500 (Para anomaly_reason).

Advertencia/Alta: amber-500 (Para intensidad 'alta' o maintenance_tasks vencidas).

Superficies: Fondos en slate-50 y tarjetas (Cards) en white para máxima legibilidad.

2. Arquitectura de Componentes (Vue.js)
Diseña siempre pensando en una estructura de Dashboard (Sidebar + Main Content):

Sidebar: Debe incluir navegación para Mis Espacios (Entity selector), Facturas (Invoices), Inventario (Equipment/Rooms) y el Log de Auditoría.

Jerarquía de Datos (Main Content):

KPIs (Top): Tarjetas con co2_footprint_kg, costo estimado y kWh totales.

Visualización Core: Implementar el "Motor v3" mediante el Gráfico de los 3 Tanques (Base, Clima, Elasticidad).

Acciones: Listado de recomendaciones basadas en efficiency_benchmarks.

3. Iconografía y Feedback Visual
Utiliza exclusivamente Lucide Icons o Heroicons (como componentes de Vue).

Categorías: Snowflake (Clima), Lightbulb (Luz), Monitor (Electro).

Contexto: Sun (Radiación/Paneles), Thermometer (Open-Meteo), FileText (Contratos), Zap (Tarifas).

4. Reglas de Implementación de Código
Tailwind 4: Utiliza la nueva sintaxis de Tailwind 4 (CSS-first configuration si es necesario).

Vue.js: Prioriza Script Setup y Composition API.

Transparencia: Cada vez que generes un gráfico o tabla de datos, incluye una pequeña sección de "Auditoría" o "Info" que explique de dónde viene el cálculo (siguiendo la filosofía de Gemelos Digitales).


Regla maxima, si se puede evitar que haga scroll la vista, darle prioridad