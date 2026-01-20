# Credenciales de Acceso - ModoAhorro

Este documento contiene las credenciales predeterminadas generadas por los seeders para el entorno de desarrollo y pruebas.

> [!IMPORTANT]
> Estas credenciales son solo para uso en desarrollo local. Asegúrese de cambiarlas en entornos de producción.

## 👥 Usuarios de Prueba

| Perfil | Email | Contraseña | Notas |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@modoahorro.com` | `password` | Acceso total al sistema y gestión administrativa. |
| **Usuario Enterprise** | `test@modoahorro.com` | `password` | Posee el plan **Enterprise**. Tiene acceso a las 3 entidades de prueba (Hogar, Oficina y Comercio). |
| **Usuario Básico** | `demo@modoahorro.com` | `12345` | Usuario de prueba simple. |

---

## 🛠️ Cómo restablecer las credenciales
Si has modificado la base de datos y deseas volver a los valores predeterminados, puedes ejecutar el siguiente comando:

```bash
php artisan migrate:fresh --seed
```

Este comando:
1. Borra todas las tablas.
2. Ejecuta las migraciones desde cero.
3. Carga todos los datos maestros (Categorías, Equipos, Planes).
4. Crea los usuarios detallados arriba.
