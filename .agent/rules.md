# Reglas de Desarrollo Antigravity (ModoAhorro)

## 1. Estándares de Vue.js
- **Composition API**: Siempre usa `<script setup>`.
- **Naming**: Los componentes deben usar PascalCase (ej: `EquipmentCard.vue`).
- **Props**: Define siempre las props con `defineProps`.
- **Forms**: Usa exclusivamente `useForm` de `@inertiajs/vue3` para envíos a backend.

## 2. Estándares de Tailwind CSS v4
- **Estética Premium**: 
  - Usa gradientes sutiles: `bg-gradient-to-br from-slate-900 to-slate-800`.
  - Bordes suaves: `rounded-[24px]` o `rounded-[32px]`.
  - Glassmorphism: `backdrop-blur-md bg-white/10 border border-white/20`.
- **Colores Semánticos**:
  - `energy-solar`: #f59e0b (amber-500)
  - `energy-success`: #10b981 (emerald-500)
  - `energy-danger`: #ef4444 (rose-500)

## 3. Lógica de Negocio (Laravel)
- **Controllers Delgados**: La lógica compleja vive en `app/Services`.
- **Tipado**: Usa Type Hinting en métodos de servicio y controladores.
- **Modelos**: Mantén los `$fillable` y `$casts` actualizados para evitar errores de asignación masiva.

## 4. Interacción con el Agente (Yo)
- **Planificación**: Antes de cambios grandes, siempre lee `PDR.md` y `ARCHITECTURE.md`.
- **Validación**: Verifica siempre que los cambios no rompan la lógica de los "Tanques" de consumo.
- **Transparencia**: Al terminar una tarea, actualiza `PDR.md` si hubo cambios en la arquitectura.
