<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\EquipmentUsage;

class ConsumptionCalibrator
{
    /**
     * Calibra los consumos teóricos utilizando una lógica mejorada.
     * MEJORA: Protege consumo mínimo para equipos declarados con uso > 0
     * 
     * Prioriza llenar las "cubetas" de consumo en orden: Base -> Hormigas -> Elefantes
     * Pero si no alcanza, distribuye proporcionalmente en lugar de poner a 0.
     *
     * @param Collection $usages Colección de EquipmentUsage con propiedad 'kwh_estimated'
     * @param float $invoiceTotalKwh Total de kWh facturados
     * @return Collection Usages con propiedad 'kwh_reconciled' y 'calibration_status'
     */
    public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
    {
        // --- A. CLASIFICACIÓN ---

        // 1. Base Crítica (Intocables) - 24hs
        $baseCritical = $usages->filter(function ($u) {
            $type = $u->equipment->type->name ?? '';
            $fixedTypes = ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma', 'Cámaras', 'Camaras'];
            foreach ($fixedTypes as $fixed) {
                if (stripos($type, $fixed) !== false)
                    return true;
            }
            return false;
        });

        // 2. Base Pesada (Higiene/Confort Básico)
        $baseHeavy = $usages->filter(function ($u) use ($baseCritical) {
            if ($baseCritical->contains('id', $u->id))
                return false;
            $type = $u->equipment->type->name ?? '';
            $heavyTypes = ['Termotanque Eléctrico', 'Calefón Eléctrico', 'Bomba de Agua'];
            foreach ($heavyTypes as $heavy) {
                if (stripos($type, $heavy) !== false)
                    return true;
            }
            return false;
        });

        // 3. Hormigas (Luces y Portátiles)
        $ants = $usages->filter(function ($u) use ($baseCritical, $baseHeavy) {
            if ($baseCritical->contains('id', $u->id) || $baseHeavy->contains('id', $u->id))
                return false;
            $cat = $u->equipment->category->name ?? '';
            return ($cat === 'Iluminación' || $cat === 'Portátiles');
        });

        // 4. Elefantes (Todo lo demás: PC, Aire, TV, Estufa)
        $elephants = $usages->reject(
            fn($u) =>
            $baseCritical->contains('id', $u->id) ||
            $baseHeavy->contains('id', $u->id) ||
            $ants->contains('id', $u->id)
        );

        // --- B. CÁLCULO DE CONSUMOS TEÓRICOS ---
        $reqCritical = $baseCritical->sum('kwh_estimated');
        $reqHeavy = $baseHeavy->sum('kwh_estimated');
        $reqAnts = $ants->sum('kwh_estimated');
        $reqElephants = $elephants->sum('kwh_estimated');

        $remaining = $invoiceTotalKwh;

        // --- C. DISTRIBUCIÓN MEJORADA ---

        // Paso 1: Base Crítica (Heladera) - PROTEGIDA
        if ($remaining >= $reqCritical) {
            $this->fullAlloc($baseCritical, 'BASE_CRITICAL');
            $remaining -= $reqCritical;
        } else {
            // Caso extremo: Factura < Heladera
            // Distribuir proporcionalmente lo que hay
            $this->partialAlloc($baseCritical, $remaining, $reqCritical, 'CRITICAL_PARTIAL');
            $this->partialAlloc($baseHeavy, 0, 1, 'ZERO_REMAINING');
            $this->partialAlloc($ants, 0, 1, 'ZERO_REMAINING');
            $this->partialAlloc($whales, 0, 1, 'ZERO_REMAINING');
            return $usages;
        }

        // Paso 2: Base Pesada
        if ($remaining >= $reqHeavy) {
            $this->fullAlloc($baseHeavy, 'BASE_HEAVY');
            $remaining -= $reqHeavy;
        } else {
            // Partial alloc a BASE_HEAVY con lo que queda
            $this->partialAlloc($baseHeavy, $remaining, $reqHeavy, 'HEAVY_PARTIAL');
            $this->partialAlloc($ants, 0, 1, 'ZERO_REMAINING');
            $this->partialAlloc($whales, 0, 1, 'ZERO_REMAINING');
            return $usages;
        }

        // Paso 3 y 4: HORMIGAS + ELEFANTES (Distribución Proporcional)
        // MEJORA: Si no alcanza para ambas, distribuir proporcionalmente

        $reqVariable = $reqAnts + $reqElephants;

        if ($remaining >= $reqVariable) {
            // Alcanza para ambas: asignación completa
            $this->fullAlloc($ants, 'PROTECTED_ANT');
            // Para ELEFANTES, usar distribución ponderada con el remaining restante
            $remainingElephants = $remaining - $reqAnts;
            $this->distributePonderada($elephants, $remainingElephants, 'WEIGHTED_ADJUSTMENT');
        } else if ($reqVariable > 0) {
            // NO alcanza: DISTRIBUCIÓN PROPORCIONAL entre ANTS y ELEFANTES

            // Calcular qué % del total variable representa cada grupo
            $antsProportion = $reqAnts / $reqVariable;
            $elephantsProportion = $reqElephants / $reqVariable;

            $remainingAnts = $remaining * $antsProportion;
            $remainingElephants = $remaining * $elephantsProportion;

            // Distribuir proporcionalmente dentro de cada grupo
            $this->partialAlloc($ants, $remainingAnts, $reqAnts, 'ANT_PROPORTIONAL');

            // Para ELEFANTES, aplicar pesos por categoría
            $this->distributePonderada($elephants, $remainingElephants, 'ELEPHANT_PROPORTIONAL');
        } else {
            // No hay ANTS ni WHALES (caso raro)
            // El remaining sobra, podría distribuirse hacia arriba pero lo dejamos
        }

        return $usages;
    }

    /**
     * Distribución ponderada para ELEFANTES con pesos por categoría
     */
    private function distributePonderada($elephants, $available, $note)
    {
        $totalScore = $elephants->sum(function ($u) {
            return $u->kwh_estimated * $this->getCategoryWeight($u->equipment->category->name ?? '');
        });

        $elephants->each(function ($u) use ($available, $totalScore, $note) {
            $weight = $this->getCategoryWeight($u->equipment->category->name ?? '');
            $score = $u->kwh_estimated * $weight;
            $share = ($totalScore > 0) ? ($score / $totalScore) : 0;

            $this->setReconciled($u, $available * $share, $note);
        });
    }

    // --- Helpers ---

    private function fullAlloc($collection, $note)
    {
        $collection->each(function ($u) use ($note) {
            $this->setReconciled($u, $u->kwh_estimated, $note);
        });
    }

    private function partialAlloc($collection, $available, $required, $note)
    {
        $factor = ($required > 0) ? $available / $required : 0;
        $collection->each(function ($u) use ($factor, $note) {
            $this->setReconciled($u, $u->kwh_estimated * $factor, $note);
        });
    }

    private function zeroAlloc($collection)
    {
        $collection->each(function ($u) {
            $this->setReconciled($u, 0, 'ZERO_ALLOCATION');
        });
    }

    // Helper para asignar y guardar estado
    private function setReconciled($usage, $val, $note)
    {
        $usage->kwh_reconciled = $val;
        $usage->calibration_note = $note; // Usamos calibration_note para consistencia con UI existente
        $usage->calibration_status = $note; // Usamos el note como status code
    }

    // Pesos de Voracidad
    private function getCategoryWeight($cat)
    {
        return match ($cat) {
            'Climatización' => 3.0,
            'Cocina' => 1.5,
            'Oficina' => 0.6,
            'Entretenimiento' => 0.6,
            default => 1.0
        };
    }
}
