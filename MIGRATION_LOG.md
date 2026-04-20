# Bitácora de Migración: ModoAhorro (L12 -> L13 + Inertia)

Esta bitácora registra el progreso del transplante del proyecto desde la base legacy a una instalación limpia de Laravel 13 con Inertia.js.

## 🚀 Estado General: Fase 3 (Frontend y Navegación) en curso

| Fase | Descripción | Estado |
| :--- | :--- | :--- |
| **0** | **Aislamiento & Base** | ✅ Completado |
| **1** | **Infraestructura Inertia** | ✅ Completado |
| **2** | **Migración de ADN (Lógica)** | ✅ Completado |
| **3** | **Frontend (Vue / Tailwind 4)** | 🔄 En curso |
| **4** | **Pruebas y QA** | ⏳ Pendiente |

---

## ✅ Hitos Completados

### Fase 0: Aislamiento y Base
- [x] Movido proyecto original a `/legacy`.
- [x] Instalación fresca de **Laravel 13.5.0** en la raíz.
- [x] Configuración inicial de `.env` (heredado de legacy).

### Fase 1: Infraestructura Inertia + Vue 3
- [x] Adaptador `inertia-laravel` instalado.
- [x] Middleware `HandleInertiaRequests` registrado.
- [x] Dependencias de **Vue 3** y **Vite** instaladas.
- [x] Configurado `vite.config.js` y `app.js`.
- [x] Layout principal `app.blade.php` creado con `@inertia`.

### Fase 2: Migración de ADN (Lógica)
- [x] **25 Modelos** migrados al nuevo core.
- [x] **Migraciones** portadas y ejecutadas con éxito (`php artisan migrate`).
- [x] **Services** de cálculo térmico portados.
- [x] **Controladores** clave (`ThermalComfortController`) portados y refactorizados a Inertia.
- [x] **Rutas** esenciales (`web.php`) configuradas.

### Fase 3: Frontend y Navegación
- [x] Instalación de **Lucide Icons** y configuración de **Tailwind 4** (Colores Semánticos).
- [x] Creación de `MainLayout.vue` con estructura de Sidebar y navegación dinámica.
- [x] **Tríada de Gestión Física** completada (CRUDs Alta Fidelidad):
    - [x] **Contratos**: Gestión técnica de NIU, tarifas y potencias P1-3.
    - [x] **Facturas**: Historial de consumo, períodos y dashboard estadístico.
    - [x] **Infraestructura**: Mapeo de Ambientes y Equipos con carga por lote.
- [x] **Refactorización de Rutas**: Unificación bajo el prefijo `/gestion` para coherencia grupal.
- [x] **Seguridad & Autorización**: Implementación de `EntityPolicy` para prevenir IDOR en todos los módulos de gestión.

---

## 🔄 En Curso Actualmente
- [ ] Implementación de Módulos de Análisis (Consumo Real vs Teórico).
- [ ] Integración de Recomendaciones de Ahorro con el Gemelo Digital.

## 🛠️ Próximos pasos inmediatos
1. Desarrollar la vista de **Análisis de Consumo Real**.
2. Portar la lógica de **Ajuste de Uso** desde el legacy.
3. Implementar el motor de **Recomendaciones de Reemplazos**.

