# ModoAhorro - Contexto Maestro del Proyecto

## 1. Visión y Propósito
**ModoAhorro** es un sistema de gestión energética de vanguardia (SaaS) que evoluciona de una simple auditoría a un modelo de **Gemelo Digital**. Su objetivo es permitir que usuarios finales y empresas comprendan su consumo eléctrico mediante leyes físicas, no solo estadísticas.

### El Motor v3 (Cerebro del Sistema)
El corazón de la aplicación es el motor de calibración basado en la **Metodología de los 4 Tanques**:
1. **Tanque 1 (Certeza)**: Consumo con error <5% (Vampiros, Routers, Deterministas).
2. **Tanque 2 (Base/Crítica)**: Consumo constante e inelástico (Heladeras, Seguridad).
3. **Tanque 3 (Climatización)**: Consumo sensible a la termodinámica exterior.
4. **Tanque 4 (Elasticidad/Variable)**: Consumo discrecional y conductual.

## 2. Stack Tecnológico Actual (Actualizado)
- **Backend**: Laravel 11 (PHP 8.2+).
- **Arquitectura**: Clean Architecture con Capa de Servicios (`ClimateService`, `ConsumptionAnalysisService`).
- **Frontend**: Vue.js 3 (Composition API) + Inertia.js (Monolito Moderno).
- **Estilos**: Tailwind CSS v4 con enfoque en **Estética Premium** (Dark mode, Glassmorphism).
- **Base de Datos**: SQLite para desarrollo, MariaDB para producción.

## 3. Reglas de Negocio Críticas
- **Protección de Patrones (Antinoise)**: El sistema permite "congelar" el uso de equipos constantes (`has_defined_pattern`). El motor de ajuste bimensual debe respetar estos valores para evitar "ruido" en los hábitos globales.
- **Inteligencia de Activos**: Los equipos no son solo nombres; tienen atributos de física térmica (`is_thermal_sensitive`, `base_efficiency_ratio`, `is_inverter`).
- **Validación por Capacidad**: Comparación de potencia/frigorías vs metros cuadrados de la habitación.

## 4. Estándares de Código y UI
- **Vue**: Siempre usar `<script setup>`.
- **UI/UX**: Prioridad absoluta a la visualización clara de datos. Si una vista puede evitar el scroll, se prefiere. Uso de Lucide Icons.
- **Inertia**: Las actualizaciones de datos deben ser fluidas, usando `router.reload` o `useForm` de forma eficiente.
- **Auditoría**: Cada cálculo presentado al usuario debe ser explicable. "No más cajas negras".

## 5. Directorios Clave
- `app/Services`: Lógica central del motor.
- `resources/js/Pages`: Vistas Vue (Inertia).
- `tablas/`: Directorio de exportación CSV para análisis externo (NotebookLM).
