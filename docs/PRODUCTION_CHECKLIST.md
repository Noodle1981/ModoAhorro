# Checklist de Preparación para Producción

Basado en el análisis del Roadmap actual y el estado del código base, estos son los elementos faltantes para considerar el proyecto "Listo para Producción", además de la integración con la API de Mercado Libre.

## 1. Módulos y Funcionalidades Faltantes

### 💰 Monetización y Afiliados (Fase 5)
- [ ] **Configuración de ID de Afiliado**: No se detectó la variable `MELI_AFFILIATE_ID` en el archivo de entorno. Es necesario para generar ingresos.
- [ ] **Links Dinámicos**: Verificar que los botones de "Comprar" generen la URL correcta con el tag de afiliado.

### 🛡️ Panel de Administración (Fase 7)
- [ ] **Instalación de FilamentPHP**: No se encuentra en `composer.json`. Es crucial para que el administrador pueda:
    - Cargar/Editar los "Benchmarks de Eficiencia" (Aires, Heladeras, etc.) sin tocar la base de datos manualmente.
    - Gestionar Usuarios y Planes.
- [ ] **Roles y Permisos**: Definir claramente quién es Admin y quién es Usuario final.

### 🏗️ Lógica de Expansión (Fase 6 - Opcional para MVP, pero en Roadmap)
- [ ] **Tipos de Ambiente "Taller/Depósito"**: Lógica específica para máquinas de alto consumo.
- [ ] **Equipos Portátiles**: Gestión de baterías y herramientas que se mueven entre habitaciones.

## 2. Configuración de Sistema y Despliegue

### ⏰ Tareas Programadas (Scheduler)
- [ ] **Actualización de Precios**: El comando `prices:update` (que usará la API de ML) debe configurarse en el `cron` del servidor para correr semanalmente.
- [ ] **Alertas de Mantenimiento**: Asegurar que los jobs de notificación de mantenimiento venzan y notifiquen correctamente.

### 🔒 Seguridad y Entorno
- [ ] **Cambio de Base de Datos**: Migrar de `SQLite` (local) a `MySQL/MariaDB` (producción) para soportar concurrencia.
- [ ] **Variables de Entorno**:
    - `APP_ENV=production`
    - `APP_DEBUG=false`
- [ ] **HTTPS**: Certificado SSL obligatorio para procesar datos de facturación o integración con APIs seguras.

### 🧪 Verificación Final (QA)
- [ ] **Tests de Integración**: Correr `php artisan test` para asegurar que la lógica de "Reemplazos" y "Confort Térmico" funciona con datos reales.
- [ ] **Carga de Datos Maestros**: Asegurar que la tabla `efficiency_benchmarks` tenga datos reales (no solo seeders de prueba) antes de salir en vivo.
