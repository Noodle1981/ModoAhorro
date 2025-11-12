

# Documentación ModoAhorro

## Estructura actual implementada

- Proyecto Laravel con Blade y base de datos SQLite.
- Modelos y migraciones para:
	- Usuarios, entidades, habitaciones, contratos, proveedores y facturas.
	- Provincias, localidades y planes.
- Relaciones Eloquent entre entidades, contratos, proveedores y facturas centralizadas en la gestión de contratos.
- Validación de campos obligatorios en la carga y edición de facturas.
- Formulario de facturas adaptado para usuarios generales:
	- Campos visibles: N° de factura, fecha de emisión, fecha inicio/fin, consumos P1/P2/P3, consumo total, costo energía, costo potencia, impuestos, otros cargos, importe total.
	- Campos ocultos: Energía inyectada, compensación excedente, huella CO2, archivo adjunto (solo se usarán en otros contextos).
- Vistas y controladores alineados para evitar confusiones y mejorar la experiencia de usuario.
- Migraciones y modelos depurados, eliminando duplicidad y tablas innecesarias.

## Siguiente pasos recomendados

**Etapa de equipamientos (próxima sesión):**
1. Definir migraciones y modelos para equipos (devices), categorías y tipos.
2. Crear seeders para poblar catálogo de equipos y categorías.
3. Implementar CRUD de equipos y su asociación con entidades y habitaciones.
4. Adaptar vistas para la gestión y visualización de equipamientos.
5. Validar relaciones y flujos de carga/edición de equipos.
6. Documentar endpoints y flujos nuevos en README y ETAPAS_DESARROLLO.md.

**Checklist para commit y push:**
- Validar que todos los cambios estén guardados y probados.
- Ejecutar en terminal:
  1. git add .
  2. git commit -m "Actualización: flujo de facturas, validaciones y campos ocultos. Preparado para etapa de equipamientos."
  3. git push

1. Crear seeders para poblar provincias, localidades, categorías y tipos de equipos.
2. Implementar controladores y rutas para flujos de carga de datos (usuarios, entidades, habitaciones, equipos, facturas, etc.).
3. Crear carpeta `app/Services` y servicios para cálculos energéticos, recomendaciones y análisis de ROI.
4. Preparar vistas Blade para cada flujo principal.
5. Implementar roles y permisos para usuario admin y panel de monitoreo.
6. Documentar endpoints y flujos en README y ETAPAS_DESARROLLO.md.
7. Testear migraciones y relaciones con datos reales.
8. Preparar el sistema para futuras extensiones (paneles solares, medidores inteligentes, integración IoT).

## Notas
- La estructura está lista para escalar y agregar nuevas funcionalidades sin romper el sistema.
- Se recomienda avanzar por etapas, probando cada flujo antes de agregar nuevas lógicas complejas.
- El sistema está preparado para análisis de consumo, recomendaciones, historial y gestión eficiente de equipos.

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
