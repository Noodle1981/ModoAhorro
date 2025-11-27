# Refactorización del Algoritmo de Cálculo Energético ("Modo Ahorro")

## 1. Contexto y Objetivo
Actualmente, el sistema está sobreestimando masivamente el consumo energético (755 kWh calculados vs 123 kWh facturados).

**Causas detectadas:**
1.  **Error Matemático:** Se está dividiendo por la `efficiency` en la fórmula de costo. Esto es incorrecto para facturación, ya que el medidor cobra la potencia de entrada (Input Power), no la potencia útil.
2.  **Error de Datos (Load Factors):** Los factores de carga (`load_factor`) actuales no contemplan el "Ciclo de Trabajo" (Duty Cycle) de equipos cíclicos como heladeras o aires acondicionados, asumiendo que el motor funciona el 100% del tiempo que están enchufados.

**Objetivo:** Ajustar el cálculo para reflejar el comportamiento real del medidor y actualizar los factores de carga en la base de datos.

---

## 2. Cambios en el Modelo de Negocio (Lógica)

**Archivo objetivo:** `App\Services\ConsumptionCalculator.php` (o donde resida el método `calculateEquipmentConsumption`).

**Instrucción:**
Refactorizar el método `calculateEquipmentConsumption`. Debemos eliminar la división por `$efficiency` y asegurar que `$loadFactor` se aplique como un multiplicador directo que represente el "Uso Real" (Potencia x Ciclo de Trabajo).

```php
public function calculateEquipmentConsumption(EquipmentUsage $usage, Invoice $invoice): float
{
    // 1. Potencia Nominal (Convertida a kW)
    // Asumimos que nominal_power_w es la potencia de ETIQUETA (Input Power)
    $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
    
    // 2. Factor de Uso Real
    // Combina Load Factor (Potencia real vs Nominal) + Duty Cycle (Tiempo encendido vs apagado)
    // Si no está definido, usamos 1.0 (peor escenario)
    $equipmentType = $usage->equipment->type;
    $realUsageFactor = $equipmentType->load_factor ?? 1.0; 

    // CRITICAL FIX: Eliminamos $efficiency de la ecuación de costo/facturación.
    // El medidor cobra la energía entrante, la ineficiencia ya está incluida en el consumo.
    
    // 3. Cálculo para Frecuencia Diaria/Semanal
    if (in_array($usage->usage_frequency, ['diario', 'semanal'])) {
        $hoursPerDay = $usage->avg_daily_use_hours ?? 0;
        $daysInPeriod = $usage->use_days_in_period ?? 0;
        
        // Fórmula: Potencia (kW) * Horas * Días * Factor de Uso Real
        $consumption = $powerKw * $hoursPerDay * $daysInPeriod * $realUsageFactor;
        
        return round($consumption, 2);
    }
    
    // 4. Cálculo para uso Puntual
    $usageCount = $usage->usage_count ?? 0;
    $avgUseDuration = $usage->avg_use_duration ?? 0; // en horas
    
    $consumption = $powerKw * $avgUseDuration * $usageCount * $realUsageFactor;

    return round($consumption, 2);
}
3. Actualización de Datos (Base de Datos)
Necesitamos actualizar los valores de load_factor en la tabla equipment_types para que reflejen el ciclo de trabajo real (especialmente en motores y electrónica).

Instrucción: Crear un Seeder llamado FixLoadFactorsSeeder para actualizar masivamente estos valores.

Archivo: database/seeders/FixLoadFactorsSeeder.php

PHP

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentType;

class FixLoadFactorsSeeder extends Seeder
{
    /**
     * Actualiza los factores de carga para incluir ciclos de trabajo (Duty Cycle).
     * Esto corrige la sobreestimación en heladeras, aires y PCs.
     */
    public function run()
    {
        $adjustments = [
            // --- GRUPO MOTOR (Cíclicos) ---
            // Heladeras: El motor solo funciona ~35-40% del tiempo.
            'Heladeras' => 0.35, 
            // Aires: Inverter o Termostato cortan el compresor.
            'Aires acondicionados' => 0.50,
            // Lavarropas: Solo centrifuga a alta potencia brevemente.
            'Lavarropas (agua fría)' => 0.30,
            
            // --- GRUPO MOTOR (Continuos) ---
            // Ventiladores: Si está ON, consume constante.
            'Ventiladores' => 1.00,

            // --- GRUPO RESISTENCIA (Con Termostato) ---
            // Plancha: La luz prende y apaga para mantener temperatura.
            'Planchas' => 0.60,

            // --- GRUPO ELECTRÓNICO (Carga Variable) ---
            // PCs: Fuentes de 600W rara vez pasan de 350W reales.
            'Computadoras' => 0.60, 
            'Notebooks' => 0.40,
            'Televisores' => 0.90,

            // --- GRUPO MAGNETRÓN ---
            // Microondas: Si se usa, es al 100%.
            'Microondas' => 1.00,
        ];

        foreach ($adjustments as $name => $factor) {
            // Usamos LIKE para abarcar variaciones de nombres
            EquipmentType::where('name', 'LIKE', "%$name%")
                ->update(['load_factor' => $factor]);
        }
        
        $this->command->info('✅ Factores de carga actualizados correctamente.');
    }
}
4. Verificación
Una vez aplicados los cambios:

Ejecutar el seeder: php artisan db:seed --class=FixLoadFactorsSeeder

Recalcular el consumo de la factura #2.

Resultado esperado:

La Heladera debería bajar de ~200 kWh a ~80 kWh.

La PC Gamer debería bajar de ~268 kWh a ~160 kWh.

El consumo total calculado debería acercarse al rango de 400-500 kWh (reduciendo la brecha con el medidor).