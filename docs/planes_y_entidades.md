# Documentación: Gestión de Planes y Acceso a Entidades

## Estructura de Planes
- Los planes se definen en la tabla `plans`.
- Cada plan tiene un campo `max_entities` que indica la cantidad máxima de entidades que puede gestionar un usuario con ese plan.
- Ejemplo de estructura:

| id | name      | features | price | max_entities |
|----|-----------|----------|-------|--------------|
| 1  | Gratuito  | ...      | 0.00  | 1            |
| 2  | Premium   | ...      | 9.99  | 5            |
| 3  | Empresa   | ...      | 29.99 | 20           |

## Asignación de Planes a Usuarios
- La relación entre usuario, entidad y plan se gestiona en la tabla pivote `entity_user`.
- Un usuario puede tener varias entidades, cada una asociada a un plan.
- El plan gratuito solo permite una entidad por usuario.

## Middleware Dinámico
- El middleware `CheckPlanEntities` verifica el límite de entidades según el plan del usuario.
- Si el usuario supera el límite, se le bloquea el acceso y se muestra un mensaje.
- El middleware se aplica en las rutas protegidas:

```php
Route::middleware(['auth', \App\Http\Middleware\CheckPlanEntities::class])->group(function () {
    // Rutas protegidas
});
```

## Agregar o Modificar Planes
- Para agregar un nuevo plan, crea un registro en la tabla `plans` con el límite deseado en `max_entities`.
- Para modificar un plan, actualiza el campo `max_entities` según el nuevo límite.
- No es necesario modificar el código del middleware para nuevos planes.

## Ejemplo de Seeder para Planes
```php
\App\Models\Plan::create([
    'name' => 'Premium',
    'features' => 'Acceso a 5 entidades',
    'price' => 9.99,
    'max_entities' => 5,
]);
```

## Futuro
- Puedes agregar más planes y cambiar los límites en cualquier momento.
- El sistema se adapta automáticamente según la configuración de la base de datos.
