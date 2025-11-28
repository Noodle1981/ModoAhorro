<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\EquipmentUsage;

class ConsumptionCalibrator
{
    /**
     * Calibra los consumos teóricos utilizando una lógica de "Supervivencia Jerárquica" (Waterfall).
     * Prioriza llenar las "cubetas" de consumo en orden: Base -> Hormigas -> Ballenas.
     *
     * @param Collection $usages Colección de EquipmentUsage con propiedad 'kwh_estimated'
     * @param float $invoiceTotalKwh Total de kWh facturados
     * @return Collection Usages con propiedad 'kwh_reconciled' y 'calibration_status'
     */
    public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
    {
        // --- A. CLASIFICACIÓN ---
        
        // 1. Base Crítica (Intocables)
        $baseCritical = $usages->filter(function ($u) {
            $type = $u->equipment->type->name ?? '';
            $fixedTypes = ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma', 'Cámaras', 'Camaras'];
            foreach ($fixedTypes as $fixed) {
                if (stripos($type, $fixed) !== false) return true;
            }
            return false;
        });

        // 2. Base Pesada (Higiene/Confort Básico)
        $baseHeavy = $usages->filter(function ($u) use ($baseCritical) {
            if ($baseCritical->contains('id', $u->id)) return false;
            $type = $u->equipment->type->name ?? '';
            $heavyTypes = ['Termotanque Eléctrico', 'Calefón Eléctrico', 'Bomba de Agua'];
            foreach ($heavyTypes as $heavy) {
                if (stripos($type, $heavy) !== false) return true;
            }
            return false;
        });

        // 3. Hormigas (Luces y Portátiles)
        $ants = $usages->filter(function ($u) use ($baseCritical, $baseHeavy) {
            if ($baseCritical->contains('id', $u->id) || $baseHeavy->contains('id', $u->id)) return false;
            $cat = $u->equipment->category->name ?? '';
            // Solo Iluminación y Portátiles
            return ($cat === 'Iluminación' || $cat === 'Portátiles');
        });

        // 4. Ballenas (Todo lo demás: PC, Aire, TV, Estufa)
        $whales = $usages->reject(fn($u) => 
            $baseCritical->contains('id', $u->id) || 
            $baseHeavy->contains('id', $u->id) || 
            $ants->contains('id', $u->id)
        );

        // --- B. CÁLCULO DE CONSUMOS TEÓRICOS ---
        $reqCritical = $baseCritical->sum('kwh_estimated');
        $reqHeavy    = $baseHeavy->sum('kwh_estimated');
        $reqAnts     = $ants->sum('kwh_estimated');
        $reqWhales   = $whales->sum('kwh_estimated');

        $remaining = $invoiceTotalKwh;

        // --- C. DISTRIBUCIÓN EN CASCADA (WATERFALL) ---

        // Paso 1: Base Crítica (Heladera)
        if ($remaining >= $reqCritical) {
            $this->fullAlloc($baseCritical, 'BASE_CRITICAL');
            $remaining -= $reqCritical;
        } else {
            // Catástrofe: Factura < Heladera. Recorte total.
            $this->partialAlloc($baseCritical, $remaining, $reqCritical, 'CRITICAL_CUT');
            $this->zeroAlloc($baseHeavy->merge($ants)->merge($whales));
            return $usages;
        }

        // Paso 2: Base Pesada (Termotanque)
        if ($remaining >= $reqHeavy) {
            $this->fullAlloc($baseHeavy, 'BASE_HEAVY');
            $remaining -= $reqHeavy;
        } else {
            // Alcanzó para Heladera, pero no para Termotanque completo.
            $this->partialAlloc($baseHeavy, $remaining, $reqHeavy, 'HEAVY_CUT');
            $this->zeroAlloc($ants->merge($whales));
            return $usages;
        }

        // Paso 3: Hormigas (Luces)
        if ($remaining >= $reqAnts) {
            $this->fullAlloc($ants, 'PROTECTED_ANT');
            $remaining -= $reqAnts;
        } else {
            // Recorte de luces
            $this->partialAlloc($ants, $remaining, $reqAnts, 'ANT_CUT');
            $this->zeroAlloc($whales);
            return $usages;
        }

        // Paso 4: Ballenas (PC, Aire) - Distribución Ponderada
        if ($reqWhales > 0) {
            // Aquí aplicamos los PESOS (Aire x3, PC x0.6)
            $totalScore = $whales->sum(function ($u) {
                return $u->kwh_estimated * $this->getCategoryWeight($u->equipment->category->name ?? '');
            });

            $whales->each(function ($u) use ($remaining, $totalScore) {
                $weight = $this->getCategoryWeight($u->equipment->category->name ?? '');
                $score = $u->kwh_estimated * $weight;
                $share = ($totalScore > 0) ? ($score / $totalScore) : 0;
                
                $this->setReconciled($u, $remaining * $share, "WEIGHTED_ADJUSTMENT");
                $u->calibration_status = 'WEIGHTED_ADJUSTMENT';
            });
        } else {
             // Si no hay ballenas, el remanente se pierde (o se podría repartir hacia arriba, pero por ahora lo dejamos)
             // En un sistema perfecto, esto no debería pasar si la factura es mayor que la suma de los anteriores.
        }

        return $usages;
    }

    // --- Helpers ---

    private function fullAlloc($collection, $note) {
        $collection->each(function($u) use ($note) {
            $this->setReconciled($u, $u->kwh_estimated, $note);
        });
    }

    private function partialAlloc($collection, $available, $required, $note) {
        $factor = ($required > 0) ? $available / $required : 0;
        $collection->each(function($u) use ($factor, $note) {
            $this->setReconciled($u, $u->kwh_estimated * $factor, $note);
        });
    }

    private function zeroAlloc($collection) {
        $collection->each(function($u) {
            $this->setReconciled($u, 0, 'ZERO_ALLOCATION');
        });
    }

    // Helper para asignar y guardar estado
    private function setReconciled($usage, $val, $note) {
        $usage->kwh_reconciled = $val;
        $usage->calibration_note = $note; // Usamos calibration_note para consistencia con UI existente
        $usage->calibration_status = $note; // Usamos el note como status code
    }

    // Pesos de Voracidad
    private function getCategoryWeight($cat) {
        return match($cat) {
            'Climatización' => 3.0,
            'Cocina' => 1.5,
            'Oficina' => 0.6,
            'Entretenimiento' => 0.6,
            default => 1.0
        };
    }
}
