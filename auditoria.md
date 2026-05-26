# Auditoría Técnica Completa del Perfil Hogar
## Sistema de Gestión de Ahorro Energético (ModoAhorro)

Este documento presenta una auditoría técnica profunda del **Perfil Hogar** en el codebase de **ModoAhorro**. El objetivo de este análisis es identificar fallas de lógica, bugs activos, incongruencias físicas/termodinámicas en el motor de cálculo, código muerto o duplicado, e inconsistencias arquitectónicas antes de avanzar con la refactorización e implementación de los perfiles **Comercio** y **Oficina**, evitando así contaminar el flujo general de la aplicación.

---

## 1. Resumen Ejecutivo de Hallazgos

Hemos analizado el flujo completo del Perfil Hogar, desde la creación de la entidad en base de datos, la carga de equipos en sus ambientes, hasta la sintonía fina e interacción del Motor de Climatización y Tanques. Se detectaron **8 fallas significativas**:

| # | Severidad | Hallazgo | Componente / Archivos Afectados |
|---|---|---|---|
| **1** | [CRÍTICO] | [Mapeo Invertido y Rotura del Gate de Patrón Fijo en los Tanques](#1-mapeo-invertido-y-rotura-del-gate-de-patrón-fijo-en-los-tanques-crítico) | `Tank1BaseService.php`<br>`Tank2ClimateService.php`<br>`AnalysisController.php` |
| **2** | [ALTO] | [Mapeo de "Bomba de Agua" como Equipo de Agua Caliente (ACS) en Ajuste Climático](#2-mapeo-de-bomba-de-agua-como-equipo-de-agua-caliente-acs-en-ajuste-climático) | `ConsumptionAnalysisService.php` |
| **3** | [MEDIO] | [Redundancia Extrema y Cuello de Botella en la Carga de Datos Climáticos](#3-redundancia-extrema-y-cuello-de-botella-en-la-carga-de-datos-climáticos) | `ConsumptionAnalysisService.php` |
| **4** | [ALTO] | [Omisión del Filtro de Categorías Excluidas en Consumo Standby](#4-omisión-del-filtro-de-categorías-excluidas-en-consumo-standby) | `StandbyAnalysisService.php` |
| **5** | [ALTO] | [Inconsistencia Crítica en Creación de Ambientes por Defecto (System Rooms Mismatch)](#5-inconsistencia-crítica-en-creación-de-ambientes-por-defecto-system-rooms-mismatch) | `Entity.php` (Model boot)<br>`Room.php` (Model helpers)<br>`entity_types.php` (Config) |
| **6** | [MEDIO] | [Incongruencia y Duplicidad Silenciosa: `type` vs `usage_type`](#6-incongruencia-y-duplicidad-silenciosa-type-vs-usage_type) | `EntityController.php`<br>`Entity.php` |
| **7** | [ALTO] | [Duplicidad Absoluta de Servicios y Controladores de Recomendaciones (Código Muerto)](#7-duplicidad-absoluta-de-servicios-y-controladores-de-recomendaciones-código-muerto) | `app/Services/Recommendations/` |
| **8** | [MEDIO] | [Polución del Entorno de Desarrollo: Directorio `legacy/` en Repositorio Activo](#8-polución-del-entorno-de-desarrollo-directorio-legacy-en-repositorio-activo) | `/legacy` (Carpeta raíz) |

---

## 2. Detalles Técnicos de los Hallazgos y el "Por Qué"

### 1. Mapeo Invertido y Rotura del Gate de Patrón Fijo en los Tanques [CRÍTICO]

#### El Problema
La filosofía del nuevo motor establecida en [IMPLEMENTACION_MOTOR_TANQUES.md](file:///d:/ModoAhorro/IMPLEMENTACION_MOTOR_TANQUES.md) dicta de forma inflexible una **Regla de Oro**:
> *"El motor nuevo debe clasificar usando exclusivamente el flag `has_defined_pattern` que el usuario activa manualmente en la vista 'Sintonía Fina'. El catálogo solo sirve como sub-clasificador dentro de los equipos que el usuario ya marcó como fijos."*
>
> **Flujo deseado:**
> 1. **Tank Crítico** (Asignación 2): Patrón Fijo (`has_defined_pattern === true`) + (Refrigeración OR Conectividad/Seguridad OR >=23.5 horas).
> 2. **Tank Climático** (Asignación 3): Patrón Fijo (`has_defined_pattern === true`) + Categoría Climatización (`is_thermal_sensitive === true`).
> 3. **Tank Certeza** (Asignación 1): Patrón Fijo (`has_defined_pattern === true`) + Cualquier otra categoría.
> 4. **Tank Variable** (Asignación 4): Sin Patrón Fijo (Todo el resto).

Sin embargo, al auditar los archivos activos de los servicios de tanques, se detectó que esto **no se implementó** o se hizo al revés:
*   En [Tank1BaseService.php](file:///d:/ModoAhorro/app/Services/Tanks/Tank1BaseService.php#L56-L62) (Crítico), el método `isEligible` **ignora completamente** el flag `has_defined_pattern`. Permite el ingreso de heladeras o routers *aunque el usuario no los haya marcado como fijos*.
*   En [Tank2ClimateService.php](file:///d:/ModoAhorro/app/Services/Tanks/Tank2ClimateService.php#L163-L168) (Climático), la validación es **el opuesto exacto**:
    ```php
    public function isEligible(Equipment $eq): bool
    {
        // Si ya tiene un patrón definido por el usuario, ya fue procesado por el Tanque de Certeza (T1)
        if ($eq->has_defined_pattern) {
            return false;
        }
        ...
    }
    ```
    Excluye al equipo del Tanque Climático si el usuario *le definió* un patrón.
*   En [AnalysisController.php](file:///d:/ModoAhorro/app/Http/Controllers/AnalysisController.php#L652-L672) (Método `getEquipmentTier`), la UI clasifica basándose en una prioridad distinta, donde coloca a los críticos (`base_critica`) y climáticos (`climatizacion`) antes de evaluar el patrón definido (`certeza`).

#### El Impacto ("Por qué")
Si el usuario sintoniza un equipo de climatización (ej. *Aire Split*) y activa el flag "Patrón Fijo" en la UI, el sistema:
1. Lo excluye de `Tank0CertaintyService` (porque es térmicamente sensible).
2. Lo excluye de `Tank1BaseService` (porque no pertenece a Refrigeración o Conectividad).
3. **Lo excluye de `Tank2ClimateService`** porque el archivo verifica `if ($eq->has_defined_pattern) { return false; }`.
4. Termina cayendo en `Tank3ElasticityService` (Variable - Tanque 4), el cual está diseñado exclusivamente para equipos *sin patrón fijo*.

Esto rompe por completo la intención del usuario y la física de distribución del motor de tanques, haciendo que la calibración arroje discrepancias energéticas severas y asigne el consumo fijo de clima a una bolsa residual variable.

---

### 2. Mapeo de "Bomba de Agua" como Equipo de Agua Caliente (ACS) en Ajuste Climático [ALTO]

#### El Problema
En el archivo [ConsumptionAnalysisService.php](file:///d:/ModoAhorro/app/Services/ConsumptionAnalysisService.php#L220-L232), el método encargado de identificar equipos de Agua Caliente Sanitaria (ACS) para aplicarles correcciones termodinámicas estacionales realiza la siguiente comprobación:
```php
private function isWaterHeater(EquipmentUsage $usage): bool
{
    $name = strtolower($usage->equipment->name);
    $type = strtolower($usage->equipment->type->name ?? '');
    $keywords = ['termotanque', 'calefón', 'calefon', 'bomba de agua'];
    
    foreach ($keywords as $keyword) {
        if (str_contains($name, $keyword) || str_contains($type, $keyword)) {
            return true;
        }
    }
    return false;
}
```

#### El Impacto ("Por qué")
Una **bomba de agua** (centrífuga, periférica o presurizadora) no tiene un comportamiento termodinámico respecto a la climatología exterior. Su consumo energético depende estrictamente del volumen de líquido desplazado y la presión de trabajo.
Al incluir `"bomba de agua"` en esta lista, el motor ejecuta el método `getWaterHeaterClimateFactor`, aplicando un **multiplicador de 1.25 en invierno** (cuando la temperatura baja de 15 °C) y **0.85 en verano** (temperatura superior a 25 °C). Esto infla o subestima de manera absurda el consumo de bombeo eléctrico del hogar basándose puramente en el clima exterior, lo cual es físicamente incorrecto.

---

### 3. Redundancia Extrema y Cuello de Botella en la Carga de Datos Climáticos [MEDIO]

#### El Problema
En el método `calibrateUnifiedPeriod` de [ConsumptionAnalysisService.php](file:///d:/ModoAhorro/app/Services/ConsumptionAnalysisService.php#L431), el servicio carga correctamente y de forma optimizada toda la información climática del periodo unificado en una sola consulta mediante `climateService->loadDataForDateRange(...)`.
Sin embargo, al procesar los teóricos de cada equipo en la iteración:
```php
$simulatedEquipments = $usages->map(function($usage) use ($representativeInvoice, $startDate, $endDate) {
    ...
    $eq->_theo_kwh = $this->calculateEquipmentConsumption($usage, $tempInvoice);
    return $eq;
});
```
La función `calculateEquipmentConsumption` evalúa si el equipo es un termotanque (`isWaterHeater`) o una heladera (`isFridge`). De ser así, se ejecutan sub-métodos (`getWaterHeaterClimateFactor` y `calculateFridgeConsumption`) que invocan internamente de forma redundante:
```php
$this->climateService->loadDataForInvoice($invoice);
```

#### El Impacto ("Por qué")
Por cada heladera y termotanque registrado en el inventario del hogar, el sistema realiza una llamada reiterada e innecesaria a la base de datos (o peor, una petición de red HTTP bloqueante a la API meteorológica si los datos del día no estuvieran cacheados). Esto representa un cuello de botella de rendimiento severo para hogares con inventarios medianos/grandes y rompe el patrón de inyección de contexto limpio del motor.

---

### 4. Omisión del Filtro de Categorías Excluidas en Consumo Standby [ALTO]

#### El Problema
En [StandbyAnalysisService.php](file:///d:/ModoAhorro/app/Services/StandbyAnalysisService.php#L14-L24), la clase define explícitamente una constante de exclusión de categorías técnicas debido a que no son propensas a tener consumo standby real:
```php
const EXCLUDED_CATEGORY_NAMES = [
    'Iluminación',
    'Portátiles',
];
```
No obstante, dentro de la función principal `calculateStandbyAnalysis`, el bucle que recorre los equipos **omite por completo el uso de esta constante**. Procesa absolutamente todos los equipos activos vinculados a la entidad.

#### El Impacto ("Por qué")
Al no filtrar las categorías excluidas, cualquier lámpara, foco LED de techo o dispositivo portátil (como laptops) que el usuario agregue en su perfil del Hogar entra al cálculo de Consumo Fantasma. Dado que el software asume un fallback automático de **5W** de potencia standby si no está definido en el catálogo, tener 15 focos LED en el Hogar genera un consumo vampiro fantasma estimado de `5W * 24hs * 30 días * 15 unidades = 54 kWh/mes` puramente ficticios. Esto infla drásticamente los potenciales de ahorro que se le presentan al usuario final.

---

### 5. Inconsistencia Crítica en Creación de Ambientes por Defecto (System Rooms Mismatch) [ALTO]

#### El Problema
Existe una severa falta de sincronización e incongruencia ortográfica y estructural en la asignación de ambientes lógicos protegidos para el Perfil Hogar:
1.  En la configuración centralizada de tipos de entidad ([entity_types.php](file:///d:/ModoAhorro/config/entity_types.php#L34)), los ambientes por defecto para el hogar se definen como:
    `'default_rooms' => ['Portátiles', 'Temporales'],`
2.  En el modelo Eloquent principal ([Entity.php](file:///d:/ModoAhorro/app/Models/Entity.php#L24-L29)), el observador del evento `created` crea ambientes físicamente en la base de datos de manera hardcodeada:
    ```php
    } else {
        $entity->rooms()->createMany([
            ['name' => 'Portables'],
            ['name' => 'Eventos / Tareas Extras'],
        ]);
    }
    ```
3.  En el modelo [Room.php](file:///d:/ModoAhorro/app/Models/Room.php#L37-L40), las validaciones del sistema verifican si el ambiente es reservado (para prevenir edición o eliminación) con:
    ```php
    public function isSystemRoom(): bool
    {
        return in_array($this->name, ['Portátiles', 'Temporales']);
    }
    ```

#### El Impacto ("Por qué")
Cuando un usuario crea una nueva Vivienda (Hogar):
*   Se guardan en la base de datos las salas `"Portables"` y `"Eventos / Tareas Extras"`.
*   El método `$room->isSystemRoom()` responde **`false`** para ambos ambientes porque los strings no coinciden con `'Portátiles'` y `'Temporales'`.
*   Esto permite al usuario **eliminar o renombrar** salas del sistema que deberían estar protegidas.
*   Además, el método `$room->getSystemDescription()` devuelve `null`, ocultando las descripciones informativas de ayuda en el frontend.
*   Para rematar, se ignora completamente el archivo de configuración `entity_types.php`, impidiendo escalar la plataforma limpiamente cuando se agreguen oficinas u otros perfiles, ya que todo el resto de tipos caen en el `else` de `Entity.php` heredando nombres incorrectos.

---

### 6. Incongruencia y Duplicidad Silenciosa: `type` vs `usage_type` [MEDIO]

#### El Problema
La tabla de base de datos de la entidad tiene dos columnas concurrentes con nomenclatura y validación similar pero comportamiento divergente:
*   `type`: Especificado en la creación. Puede ser `hogar`, `comercio`, `oficina`. Es la clave primaria para determinar el comportamiento en `config/entity_types.php`.
*   `usage_type`: Validado en el formulario de edición de perfil ([EntityController.php](file:///d:/ModoAhorro/app/Http/Controllers/EntityController.php#L64)) bajo la regla `'usage_type' => 'required|string|in:residencial,comercial,oficina'`.

#### El Impacto ("Por qué")
El motor de cálculo (tanto en `EnergyEngineService` como en `ConsumptionAnalysisService`) evalúa estrictamente `$entity->type` para discernir la lógica de heladeras, turnos comerciales o factor de standby. El campo `usage_type` se almacena de forma pasiva en la base de datos pero **carece de toda funcionalidad lógica en el motor**.
Si un usuario edita su perfil de hogar y cambia el "Tipo de Uso" a *Comercial* en el formulario, el campo `usage_type` se actualiza a `comercial`, pero `type` permanece como `hogar`. Esto causa una desalineación de la integridad de los datos y presentará bugs severos cuando se integren flujos comerciales reales, ya que el usuario asumirá que su perfil es comercial pero el motor seguirá tratándolo como un hogar residencial estándar.

---

### 7. Duplicidad Absoluta de Servicios y Controladores de Recomendaciones (Código Muerto) [ALTO]

#### El Problema
Se identificó una severa duplicación de archivos que representan clases de servicios idénticas con namespaces cruzados y diferencias sutiles de estructura:
1.  **Doble archivo `ReplacementService`**:
    *   [app/Services/ReplacementService.php](file:///d:/ModoAhorro/app/Services/ReplacementService.php): **Activo**. Retorna llaves de respuesta de API como `id`, `name`, `suggestion`. Sus veredictos incluyen propiedades de estilo visual (`bg`, `text`).
    *   [app/Services/Recommendations/ReplacementService.php](file:///d:/ModoAhorro/app/Services/Recommendations/ReplacementService.php): **Código Muerto**. Retorna llaves diferentes como `equipment_id`, `equipment_name`, `replacement_suggestion` y carece de estilos de color extendidos en sus veredictos.
2.  **Servicios de Recomendación Vacíos**:
    *   `app/Services/Recommendations/HogarRecommendationService.php`
    *   `app/Services/Recommendations/ComercioRecommendationService.php`
    *   `app/Services/Recommendations/OficinaRecommendationService.php`
    No son instanciados, heredados ni llamados en ningún punto del controlador activo `RecommendationController.php`.

#### El Impacto ("Por qué")
Tener archivos fantasmas y duplicados (`ReplacementService` en particular) contamina el autocompletado y análisis estático de los editores IDE. Si un desarrollador edita por error el archivo dentro de `/Recommendations` para solucionar un problema en la lógica de cálculo de ROI de reemplazos, los cambios **no se reflejarán jamás en producción** porque el controlador (`RecommendationController`) está importando el archivo de la raíz de `/Services`.

---

### 8. Polución del Entorno de Desarrollo: Directorio `legacy/` en Repositorio Activo [MEDIO]

#### El Problema
El repositorio conserva en su raíz un directorio completo llamado `/legacy` que contiene una réplica espejo casi íntegra del framework (rutas, base de datos, seeders, assets, modelos y servicios). 

#### El Impacto ("Por qué")
A pesar de no ser interpretado por el servidor web Apache/Nginx o el comando `php artisan serve` en tiempo de ejecución, la presencia física de este directorio en el repositorio es altamente nociva:
*   Aumenta significativamente el peso del proyecto.
*   Interfiere y contamina las búsquedas globales de código (*grep searches*, *Ctrl+P* o herramientas de búsqueda difusa).
*   Provoca que los desarrolladores abran accidentalmente archivos del motor legacy en vez del motor activo, ocasionando pérdidas de tiempo y frustración técnica.

---

## 3. Plan de Acción Recomendado (Fixes y Prevención)

Para garantizar un código blindado y escalable de cara al desarrollo de los perfiles **Comercio** y **Oficina**, se sugieren las siguientes correcciones inmediatas en el **Perfil Hogar**:

### Acción A: Reconstrucción de la Lógica del Motor de Tanques (Alineación con la Filosofía)
*   **En `Tank1BaseService.php`**: Reemplazar `isEligible` para que requiera explícitamente `$eq->has_defined_pattern === true` antes de derivar a la base crítica.
*   **En `Tank2ClimateService.php`**: Invertir la validación de `has_defined_pattern`. Debe ser obligatoriamente `true` para entrar a la evaluación de climatización de sintonía fina, en lugar de ser un factor de exclusión (`return false`).
*   **En `AnalysisController.php` (`getEquipmentTier`)**: Adaptar la cascada de tiers para respetar exactamente las reglas de asignación del motor de base de datos, evitando que se dibuje visualmente una cosa en la UI y se compute otra en el motor.

### Acción B: Corrección Físico-Termodinámica
*   **En `ConsumptionAnalysisService.php` (`isWaterHeater`)**: Remover el keyword `'bomba de agua'`. La bomba de agua debe calcularse como un equipo puramente elástico sin multiplicador estacional de temperatura ambiental.
*   **En `ConsumptionAnalysisService.php`**: Refactorizar la carga de clima. En lugar de ejecutar `loadDataForInvoice` individualmente por cada heladera o termotanque, cachear el resultado del clima en una propiedad protegida del servicio durante el ciclo de vida del Request, o extraer el clima del motor directamente.

### Acción C: Unificación de Ambientes por Defecto (driven by Config)
*   **En `Entity.php` (`boot`)**: Eliminar el array hardcodeado de ambientes. Reemplazarlo por una lectura dinámica de la configuración unificada:
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
    *Nota: Corregir las referencias de `'Portables'` en base de datos a `'Portátiles'` y `'Eventos / Tareas Extras'` a `'Temporales'` para que exista coherencia total con los helpers del modelo `Room.php`.*

### Acción D: Depuración de Código Standby
*   **En `StandbyAnalysisService.php` (`calculateStandbyAnalysis`)**: Aplicar la constante `EXCLUDED_CATEGORY_NAMES` al filtrar la colección de equipos para evitar inflar artificialmente el consumo fantasma del hogar con lámparas o laptops:
    ```php
    $equipmentList = $entity->rooms
        ->flatMap(fn($room) => $room->equipment)
        ->filter(fn($eq) => $eq->is_active !== false)
        ->filter(fn($eq) => !in_array($eq->category?->name, self::EXCLUDED_CATEGORY_NAMES))
        ->values();
    ```

### Acción E: Eliminación de Basura y Redundancia
*   Eliminar el directorio `app/Services/Recommendations` de manera segura, habiendo verificado que ningún controlador importa clases de esa subcarpeta.
*   Mover el directorio `/legacy` fuera del espacio de trabajo del repositorio git local, o añadirlo a la regla del `.gitignore` principal para prevenir que los IDEs indexen sus archivos obsoletos.
