

# Documentación ModoAhorro


## Estructura actual implementada

- Proyecto Laravel con Blade y base de datos SQLite.
- Modelos y migraciones para:
	- Usuarios, entidades, habitaciones, contratos, proveedores y facturas.
	- Provincias, localidades y planes.
	- Equipos, categorías y tipos de equipos, historial y uso por periodo.
- Relaciones Eloquent robustas y centralizadas.
- Validación de campos obligatorios y filtrado dinámico en formularios.
- Formulario de facturas adaptado para usuarios generales.
- Gestión de equipos exclusivamente por habitación (cada equipo se asocia a una room).
- Nueva lógica de carga múltiple: campo "Cantidad" en el formulario de equipos, que permite crear varios equipos individuales en un solo paso.
- Visualización de cantidad de equipos por habitación en la vista principal.
- Eliminación de rutas y vistas generales de equipos para evitar errores y duplicidad.
- Migraciones y modelos depurados, listos para escalar.

## Siguiente pasos recomendados

**Etapa de equipamientos (actual):**
1. CRUD de equipos por habitación, con campo cantidad y validaciones.
2. Visualización y edición de equipos por ambiente.
3. Documentar endpoints y flujos nuevos en README y ETAPAS_DESARROLLO.md.
4. Validar migraciones y seeders para catálogo de equipos y categorías.
5. Preparar lógica para historial, bajas y reemplazos.

**Checklist para commit y push:**
- Validar que todos los cambios estén guardados y probados.
- Ejecutar en terminal:
	1. git add .
	2. git commit -m "Actualización: equipos por habitación, carga múltiple y UX."
	3. git push

## Notas
- El sistema ahora permite una gestión energética mucho más precisa y flexible.
- La lógica de equipos por habitación y carga múltiple facilita el inventario y los cálculos.
- El flujo UX está alineado con la gestión real de ambientes y equipos.

---

Para dudas, ideas o nuevas funcionalidades, escribir en `ETAPAS_DESARROLLO.md` y seguir el flujo de trabajo.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
