Guía de Implementación: Modelo Demográfico de Ocupación1. Nuevo Concepto: El "Household Profile" (Perfil del Hogar)Necesitamos agregar campos al usuario o a la propiedad para calcular el Factor de Ocupación.Datos requeridos:Metros Cuadrados: (Ej. 450 m² → Casa grande, mayor dispersión de luz, pero no necesariamente más uso de TV).Habitantes: (Ej. 4 personas).Perfil de Ocupación:HOME_OFFICE_RETIRED: Están 100% en casa.MIXED_FAMILY: Hijos a la escuela (mañana), Padres trabajan (tarde).FULL_WORKERS: Casa vacía de 08:00 a 18:00.WEEKENDERS: Solo fines de semana.2. Lógica de "Tiempo Muerto" (Sanitización por Ausencia)El sistema calculará las Horas Disponibles de Consumo (HDC).El día tiene 24 horas.Dormir: -8 horas (promedio).Trabajo/Escuela/Salidas: -X horas (según perfil).Fórmula:$$HDC = 24 - (HorasSueño + HorasAusencia)$$Si el usuario declara que usa el Aire Acondicionado 10 horas, pero su perfil dice que trabaja 9 horas y duerme 8 (total 17h ocupado), solo le quedan 7 horas libres.Acción del Sistema: Recortar automáticamente el input de 10h a 7h (o menos).3. Clasificación de Equipos según Dependencia HumanaNo todos los equipos se afectan por las salidas. Debemos clasificarlos en el código (EquipmentType):Clasificación¿Se reduce si salen?EjemplosHUMAN_DEPENDENTSÍ (Drásticamente)TV, Consolas, Luces Interiores, Microondas, Aire (Manual).PASSIVE_ALWAYS_ONNO (Intocable)Heladera, Freezer, Alarma, Router, Cámaras.TASK_BASEDNO (Depende de cant. gente)Lavarropas, Lavavajillas, Termotanque (si es eléctrico).ENVIRONMENT_DEPENDENTPARCIALMENTELuces Exteriores (Fotocélula), Filtro Piscina.4. Guía para el Archivo .md (Antigravity Implementation)Copia y pega esto en tu archivo ENERGY_CALC_FIX.md. Reemplaza o agrega a la sección de Sanitización.Markdown# Estrategia de Reducción Demográfica (Household Logic)

## Objetivo
Implementar un "Factor de Ocupación" que reduzca automáticamente las horas de uso declaradas basándose en el estilo de vida de la familia (Escuela, Trabajo, Jubilados) y el tamaño de la propiedad.

## A. Actualización del Modelo de Datos

Agregar a la tabla `properties` o `user_profiles`:
- `square_meters` (int)
- `inhabitants_count` (int)
- `occupancy_type` (enum: '100_percent', 'worker_student', 'mostly_out')

## B. Servicio: OccupancyCalculator

Crear `App\Services\Calculators\OccupancyCalculator.php`.

### Lógica de Negocio

1. **Determinar "Ventana de Ocupación" (Active Hours):**
   - Si `100_percent` (Jubilados/Home Office): 
     - Ventana activa: 16 horas (24h - 8h sueño).
   - Si `worker_student` (Familia Tipo): 
     - Ventana activa: 8 horas (Desayuno + Noche). 
     - *Descuento automático de 8 a 10 horas por trabajo/escuela.*
   - Si `mostly_out` (Soltero trabajador): 
     - Ventana activa: 4-5 horas.

2. **Calcular "Factor de Fricción" por Metros Cuadrados:**
   - En una casa de 450m² (grande), es común dejar luces prendidas en habitaciones vacías por error.
   - En un depto de 50m², si no estás en la pieza, la luz está apagada.
   - **Regla:** Si `category == Iluminación` y `sq_meters > 200`, permitir un 15% extra de "desperdicio" sobre el cálculo base. Si es pequeña, ser estricto.

3. **Calcular Frecuencia de Lavado (Task Based):**
   - El usuario puede decir "Uso el lavarropas todos los días".
   - **Regla Demográfica:** - 1 persona = 1.5 lavados/semana.
     - 4 personas = 4-5 lavados/semana.
     - Si el input del usuario supera `habitantes * 1.5`, aplicar tope (Cap).

## C. Implementación en el Sanitizer

Dentro de `BehaviorSanitizer::sanitize()`:

```php
public function applyOccupancyRules(EquipmentUsage $usage, Property $property)
{
    // 1. Identificar Tipo de Dependencia
    $dependency = $usage->equipment->type->human_dependency; // 'HIGH', 'NONE', 'TASK'

    // CASO 1: Equipos que dependen de que haya gente (TV, Aire, Luces)
    if ($dependency === 'HIGH') {
        $maxPossibleHours = $this->getMaxActiveHours($property->occupancy_type);
        
        // Si el usuario puso más horas de las que está físicamente en casa:
        if ($usage->avg_daily_use_hours > $maxPossibleHours) {
            // Recortamos a la realidad
            $usage->avg_daily_use_hours = $maxPossibleHours;
        }
        
        // Ajuste por "Salidas de Paseo" (Fin de semana)
        // Si es fin de semana, asumimos 4 horas fuera por ocio
        // Se aplica un factor de reducción del 0.85 global al periodo
        $usage->avg_daily_use_hours *= 0.85;
    }

    // CASO 2: Tareas del Hogar (Lavarropas)
    if ($dependency === 'TASK') {
        // Regla: Máximo 1.2 ciclos diarios por cada 4 habitantes
        $maxCyclesPerWeek = $property->inhabitants_count * 1.5; // Ej: 4 pax = 6 lavados/semana
        $dailyCap = $maxCyclesPerWeek / 7;
        
        if ($usage->usage_frequency === 'diario' && $usage->avg_daily_use_hours > $dailyCap) {
             // Ajustar frecuencia o cantidad
             // Convertimos a un promedio ponderado
             $usage->avg_daily_use_hours = $dailyCap; 
        }
    }
    
    // CASO 3: Siempre encendido (Heladera) -> NO TOCAR.
}

private function getMaxActiveHours($type) {
    return match($type) {
        '100_percent' => 16,    // Están todo el día
        'worker_student' => 7,  // Salen 9h, Duermen 8h
        'mostly_out' => 4,      // Solo cenan y duermen
        default => 8
    };
}

---

### ¿Cómo mejora esto tu cálculo?

Tomemos tu ejemplo actual con esta nueva lógica:

1.  **Datos:** Familia de 4, Casa 450m², "Escolaridad/Trabajo" (`worker_student`).
2.  **Input Usuario:** "Aire Acondicionado 10 horas" (Es lo que él cree).
3.  **Procesamiento:**
    * El sistema ve `worker_student`.
    * Sabe que de 8 AM a 5 PM no hay nadie (o solo 1 persona).
    * Calcula `MaxActiveHours` = 7 horas (aprox 18:00 a 01:00).
    * **Recorte:** Baja el aire de 10h a 7h.
4.  **Ajuste Extra:** Como es una casa de 450m², es probable que haya luces "olvidadas". El sistema permite un poco más de horas en `Iluminación` que en `Climatización`.

Al aplicar esto, tu consumo calculado bajará "mágicamente" alineándose con la factura, porque estás modelando la **ausencia**, que es el factor que más ahorra energía.