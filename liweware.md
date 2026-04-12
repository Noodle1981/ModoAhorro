# Plan de Traspaso a Livewire

## Etapa 1: Wizards (Bajo Vuelo, Alto Impacto)
*   **Thermal Comfort Wizard:** Es el candidato #1. Actualmente tiene 3 rutas. Livewire lo hará un solo componente reactivo.
*   **Solar & Vacation:** Calculadoras que necesitan feedback instantáneo.

## Etapa 2: Listados y Filtros (UX)
*   **Facturas y Contratos:** Migrar a componentes `Full Page` con búsqueda y filtrado en tiempo real (sin recarga de página).
*   **Gestión de Habitaciones:** Hacer que agregar un equipo a una habitación sea un modal de Livewire o un componente inline.

## Etapa 3: "El Gran Reemplazo" (CRUDs)
*   Reemplazar gradualmente todos los controladores en `app/Http/Controllers/Physical` y `app/Http/Controllers/Entity`.
*   Convertir las rutas `Route::resource` en llamadas a componentes.

## Etapa 4: Dashboard Pro
*   El panel de consumo actual es estático. Livewire permitirá que las gráficas y tarjetas se actualicen al cambiar de entidad sin parpadeos.

---
**Progreso:**
- [x] Rama `livewire` creada.
- [ ] Etapa 1: Iniciada.
