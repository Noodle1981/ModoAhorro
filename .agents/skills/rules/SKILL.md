---
name: rules
description: Reglas y estándares de desarrollo para ModoAhorro (Laravel, Vue 3, arquitectura de motor de tanques, roles y documentación en /doc).
---

# Reglas de Desarrollo y Estándares de Arquitectura (ModoAhorro)

Usa esta skill como el marco de referencia normativo para el desarrollo, arquitectura, interacción de roles y reglas de negocio en ModoAhorro.

---

## 1. Documentación Central del Proyecto (`/doc`)
Toda la documentación técnica, arquitectónica y registros del gemelo digital residen en el directorio `d:\ModoAhorro\doc/`:
- **[ARCHITECTURE.md](file:///d:/ModoAhorro/doc/ARCHITECTURE.md)**: Arquitectura del sistema, capas de servicios y orquestación del motor.
- **[PDR.md](file:///d:/ModoAhorro/doc/PDR.md)**: Project Development Record, historial de versiones, problemas resueltos y decisiones técnicas.
- **[notebook_sync.md](file:///d:/ModoAhorro/doc/notebook_sync.md)**: Protocolo de sincronización con NotebookLM y exportación a `tablas/`.
- **[context.md](file:///d:/ModoAhorro/doc/context.md)**: Contexto maestro del gemelo digital y visión del producto.

> [!IMPORTANT]
> - **Antes de cualquier cambio estructural o de motor**: Consulta siempre `doc/PDR.md` y `doc/ARCHITECTURE.md`.
> - **Al finalizar cualquier cambio relevante**: Registra la versión en `doc/PDR.md`, sincroniza tablas (`php artisan app:export-notebook`) y asegura la integridad técnica.

---

## 2. Roles y Protocolo de Trabajo
- **IA NotebookLM**: Director de Proyecto / Arquitecto de Alto Nivel.
- **IA Antigravity (Tú)**: Desarrollador Senior / Ejecutor e Ingeniero de Software.
- **Usuario**: Product Owner, Amigo y QA de Aceptación.

---

## 3. Estándares de Frontend (Vue.js 3 + Composition API)
- **Composition API**: Uso obligatorio de `<script setup>` con orden canónico: `<script setup>` → `<template>` → `<style scoped>`.
- **Nomenclatura**: Nombres de componentes siempre en PascalCase (ej: `StatCard.vue`, `Modal.vue`).
- **Reactividad Eficiente**:
  - `shallowRef()` para valores primitivos (`boolean`, `string`, `number`), modales y filtros de vista, minimizando la sobrecarga de proxies reactivos profundos.
  - `ref()` para colecciones u objetos reasignables en bloque.
  - `computed()` puro para valores derivados (sin efectos secundarios).
  - `watch()` / `watchEffect()` exclusivamente para efectos secundarios (navegación, llamadas asíncronas).
- **Flujo Unidireccional de Datos**:
  - Props tratadas estrictamente como de solo lectura (`defineProps`). Prohibida la mutación directa en componentes hijos.
  - Comunicación ascendente mediante eventos explícitos (`defineEmits`).
- **Capa de Composables y Componentes Reutilizables**:
  - Toda lógica compartida debe abstraerse en `resources/js/Composables/` (ej: `useTheme.js`, `useFormatters.js`).
  - Todo elemento visual repetido debe modularizarse en `resources/js/Components/` (ej: `Modal.vue`, `StatCard.vue`).
  - Mantener las vistas de página (Inertia) como superficies delgadas de composición y orquestación.
- **Formularios e Integración**:
  - Uso exclusivo de `useForm` de `@inertiajs/vue3` para envíos y validaciones con backend.
- **Seguridad en Plantillas**:
  - Cero uso de `v-html` con datos dinámicos.

> [!NOTE]
> **Identidad Visual y Diseño UI/UX**:
> Para la guía de diseño visual, paleta semántica funcional, Tailwind CSS v4, iconografía Lucide y principio *Scroll-Free*, consulta y aplica la skill dedicada **`/visual`** ([`.agents/skills/visual/SKILL.md`](file:///d:/ModoAhorro/.agents/skills/visual/SKILL.md)).

---

## 4. Estándares de Backend (Laravel 11 + PHP 8.2+)
- **Controllers Delgados**: Los controladores únicamente validan entradas, delegan a servicios y retornan respuestas o vistas Inertia.
- **Capa de Servicios**: La lógica de negocio, cálculos termodinámicos y orquestación residen en `app/Services/`.
- **Tipado Fuerte**: Type-hinting estricto en argumentos y retornos de métodos.
- **Modelos Eloquent**: Propiedades `$fillable` y `$casts` siempre actualizados.
- **Seguridad**: Mantener dependencias auditadas (`composer audit` con 0 vulnerabilidades) y cabeceras de protección activas (`SecurityHeaders`).
- **Pruebas Automatizadas**: Todo cambio debe verificar la suite completa con `php artisan test` en verde.

---

## 5. Modelo de Negocio: Motor de Energía por Tanques
### Filosofía Central
El usuario NO conoce ni interactúa con la estructura interna de tanques. Solo conoce sus equipos y cómo los usa. El motor traduce esa información en balances físicos y matemáticos verificables.

### Invariantes del Motor (Reglas Intocables)
1. **Orden Fijo de la Cascada**:
   - `Standby` (Consumo fantasma).
   - **Tanque 2 (Crítico / Base)**: Equipos de uso continuo 24/7 (Heladera, Router, Cámaras de seguridad).
   - **Tanque 3 (Climático)**: Equipos sensibles térmicos ajustados por condiciones meteorológicas (Aires acondicionados, climatización en días de calor/frío).
   - **Tanque 1 (Certeza)**: Equipos con patrón fijo predecible declarado (`has_defined_pattern = true`).
   - **Tanque 4 (Variable / Elástico)**: Consumo discrecional, hábitos variables y residuo calibrado.
2. **Filtrado por Asignación Única**:
   - Cada servicio de tanque procesa únicamente equipos con `tank_assignment === null` para evitar duplicidad de asignación.
3. **Pre-cálculo Teórico Puro**:
   - `_theo_kwh` se calcula en `ConsumptionAnalysisService` **antes** de ejecutar la cascada de tanques.
4. **Persistencia del Patrón**:
   - `has_defined_pattern` vive en el modelo `Equipment` (ficha técnica del activo), no en `EquipmentUsage` temporal.

---

## 6. Soporte Comercial (B2B)
- Las entidades comerciales (`comercio`, `oficina`) operan bajo lógicas de proceso y turnos operativos:
  - `TURNS_BASED`: Consumo = Potencia × Turnos × Horas por turno × Días.
  - `SERVICE_HOURS`: Consumo = Potencia × (Cierre - Apertura) × Días.
  - `CONTINUOUS_COMMERCIAL`: Consumo 24h con factores de carga industriales.
- Mapeo de personal y afluencia mediante `staff_count` y `visitors_count`.
