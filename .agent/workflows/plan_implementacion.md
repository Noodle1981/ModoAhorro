# Plan de Refactorización y Estabilización - Perfil Hogar y Motor de Energía

Este plan de implementación ha sido diseñado y aprobado para corregir las inconsistencias lógicas, físicas y estructurales detectadas en el Perfil Hogar y asegurar que los futuros desarrollos de los perfiles **Comercio** y **Oficina** no se contaminen.

---

## 1. Ajustes en Climatización y Tanques (`Tank2ClimateService.php`)

### Cambio de Lógica
El Tanque de Climatización debe procesar de forma unificada todos los aires acondicionados y calefactores (tanto fijos como variables) para aplicar la lógica meteorológica de grados-día. No obstante, debe excluir explícitamente a los ventiladores de techo y de pie, ya que estos no alteran termodinámicamente la temperatura, sino que únicamente desplazan el aire del ambiente a consumo eléctrico constante.

### Implementación en `app/Services/Tanks/Tank2ClimateService.php`
*   Modificar el método `isEligible(Equipment $eq)`:
    *   Excluir si el nombre del equipo o del tipo de equipo contiene el string `"ventilador"`.
    *   Permitir el paso de aires acondicionados (`"aire"`, `"split"`, `"portátil"`) y calefactores (`"estufa"`, `"calefactor"`) sin importar si tienen o no `has_defined_pattern === true`.
    *   Para otros dispositivos térmicamente sensibles que no sean aires ni calefactores directos, mantener la exclusión en caso de poseer patrón fijo (ya que serían capturados por Certeza).

---

## 2. Creación del Módulo para Bomba de Agua

### Cambio de Lógica
Las bombas de agua (centrífugas, presurizadoras, etc.) no deben calcularse bajo el factor termodinámico de agua caliente sanitaria (ACS). En su lugar, se crea un módulo propio en el motor de consumo con multiplicadores estacionales inversos:
*   **Verano**: Mayor uso por riego, llenado de piscinas y mayor higiene/duchas. Se aplica un **multiplicador de 1.30** si la temperatura promedio mensual supera los 25 °C.
*   **Invierno**: Menor uso por ausencia de riego y natación. Se aplica un **multiplicador de 0.70** si la temperatura promedio mensual baja de los 15 °C.

### Implementación en `app/Services/ConsumptionAnalysisService.php`
*   Eliminar `'bomba de agua'` del array de términos de `isWaterHeater`.
*   Añadir el método helper `isWaterPump(EquipmentUsage $usage)` para identificar bombas por palabras clave (`'bomba de agua'`, `'bomba de riego'`, `'presurizadora'`, etc.).
*   Crear la función `getWaterPumpClimateFactor(EquipmentUsage $usage, Invoice $invoice)` que determine y registre los factores climáticos `1.30` o `0.70` en los logs del equipo.
*   Integrar en `calculateEquipmentConsumption` un bloque dedicado a procesar bombas de agua con su factor climático inverso, aplicando también penalidades de mantenimiento y consumo standby en caso de que esté activo.

---

## 3. Ambientes Protegidos del Sistema Estables y Escalables

### Cambio de Lógica
El Perfil Hogar requiere las habitaciones `"Portátiles"` (para centralizar el consumo móvil itinerante como cargadores de celulares) y `"Temporales"` (para consumos ocasionales como obras, refacciones o fiestas). Estas salas deben crearse dinámicamente según la configuración de cada tipo de entidad y quedar completamente bloqueadas frente a intentos de borrado o renombrado por el usuario en el frontend, garantizando robustez y escalabilidad.

### Implementación
1.  **Configuración (`config/entity_types.php`)**:
    *   Asegurar que en la llave `hogar.default_rooms` se utilicen exactamente los nombres corregidos: `['Portátiles', 'Temporales']`.
2.  **Modelo de Entidad (`app/Models/Entity.php`)**:
    *   Reemplazar la inicialización hardcodeada en el evento `created` del modelo `Entity` por una lectura dinámica:
        ```php
        static::created(function ($entity) {
            $defaultRooms = config("entity_types.{$entity->type}.default_rooms", []);
            foreach ($defaultRooms as $roomName) {
                $entity->rooms()->create([
                    'name' => $roomName
                ]);
            }
        });
        ```
3.  **Modelo de Habitación (`app/Models/Room.php`)**:
    *   Actualizar `isSystemRoom()` para que sea completamente escalable:
        ```php
        public function isSystemRoom(): bool
        {
            $allSystemRooms = collect(config('entity_types'))->pluck('default_rooms')->flatten()->unique()->toArray();
            return in_array($this->name, $allSystemRooms);
        }
        ```
4.  **Controlador de Infraestructura (`app/Http/Controllers/InfrastructureController.php`)**:
    *   En `updateRoom`, validar si la habitación es de sistema y el nombre entrante difiere del actual, rechazándolo con un error:
        ```php
        if ($room->isSystemRoom() && $request->input('name') !== $room->name) {
            return redirect()->back()->with('error', 'No se puede renombrar un ambiente protegido del sistema.');
        }
        ```
    *   En `destroyRoom`, bloquear la eliminación de cualquier ambiente protegido:
        ```php
        if ($room->isSystemRoom()) {
            return redirect()->back()->with('error', 'No se puede eliminar un ambiente protegido del sistema.');
        }
        ```

---

## 4. Depuración de Consumo Standby (Consumo Fantasma)

### Cambio de Lógica
En el ambiente *"Portátiles"*, los cargadores y portátiles que permanezcan enchufados deben generar consumo standby real (phantom power). No obstante, los dispositivos de **Iluminación** (lámparas, focos LED) no deben figurar como posibles consumidores fantasma en el sistema, ya que al apagarse se interrumpe la corriente total en la pared.

### Implementación en `app/Services/StandbyAnalysisService.php`
*   Modificar la constante de exclusiones:
    ```php
    const EXCLUDED_CATEGORY_NAMES = [
        'Iluminación',
    ];
    ```
*   Aplicar el filtro en el método `calculateStandbyAnalysis` antes de procesar el cálculo de consumos:
    ```php
    $equipmentList = $entity->rooms
        ->flatMap(fn($room) => $room->equipment)
        ->filter(fn($eq) => $eq->is_active !== false)
        ->filter(fn($eq) => !in_array($eq->category?->name, self::EXCLUDED_CATEGORY_NAMES))
        ->values();
    ```

---

## 5. Cronograma de Tareas de Refactorización

*   **Paso 1**: Refactorizar y alinear `config/entity_types.php` con los nombres correctos de ambientes (`Portátiles`, `Temporales`).
*   **Paso 2**: Actualizar la creación dinámica de ambientes en `Entity.php` y blindar los métodos de actualización y borrado en `Room.php` e `InfrastructureController.php`.
*   **Paso 3**: Modificar `Tank2ClimateService.php` para integrar aires acondicionados (con o sin patrón) y excluir ventiladores.
*   **Paso 4**: Implementar el módulo propio de cálculo e identificación de bombas de agua en `ConsumptionAnalysisService.php` con multiplicadores climáticos estacionales inversos.
*   **Paso 5**: Corregir y aplicar la exclusión de la categoría de Iluminación en el servicio de consumos fantasma `StandbyAnalysisService.php`.
*   **Paso 6**: Ejecutar suites de testeo y realizar pruebas de integración manuales de los flujos de sintonía fina.
