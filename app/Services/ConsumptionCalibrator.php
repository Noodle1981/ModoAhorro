<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\EquipmentUsage;

class ConsumptionCalibrator
{
    /**
     * Calibra los consumos teóricos utilizando la lógica de MOTOR INTEGRAL.
     * 
     * Guía:
     * 1. Base Crítica (Heladeras, Routers, Alarmas): Intocables. Se llenan primero.
     * 2. Base Pesada (Termotanques, Bombas): Confort básico. Se llenan segundo.
     * 3. Hormigas (Iluminación, Cargadores): Infraestructura. Se llenan tercero.
     * 4. Ballenas (Aires, Estufas, PC Gamer, TV): Ocio y Clima. Absorben variabilidad.
     *
     * @param Collection $usages Colección de EquipmentUsage con propiedad 'kwh_estimated'
     * @param float $invoiceTotalKwh Total de kWh facturados
     * @return Collection Usages con propiedad 'kwh_reconciled' y 'calibration_status'
     */
    public function calibrate(Collection $usages, float $invoiceTotalKwh): Collection
    {
        // --- A. CLASIFICACIÓN (Sincronizada con getEquipmentTier del controlador) ---
        $groups = [
            'base_critica' => collect(),
            'base_pesada'  => collect(),
            'hormigas'     => collect(),
            'ballenas'     => collect(),
            'otros'        => collect(),
        ];

        foreach ($usages as $u) {
            $tier = $this->getEquipmentTier($u->equipment);
            $groups[$tier]->push($u);
        }

        $remaining = $invoiceTotalKwh;

        // --- B. DISTRIBUCIÓN POR JERARQUÍA ---

        // 1. Base Crítica: SE ASIGNA EL 100% SIEMPRE (Protegido)
        $reqCritical = $groups['base_critica']->sum('kwh_estimated');
        if ($remaining >= $reqCritical) {
            $this->fullAlloc($groups['base_critica'], 'TIER_CRITICAL_FIXED');
            $remaining -= $reqCritical;
        } else {
            $this->partialAlloc($groups['base_critica'], $remaining, $reqCritical, 'TIER_CRITICAL_INSUFFICIENT');
            $remaining = 0;
        }

        // 2. Hormigas: SE ASIGNA EL 100% (Protegido - Son consumos fijos de infraestructura)
        $reqAnts = $groups['hormigas']->sum('kwh_estimated');
        if ($remaining >= $reqAnts) {
            $this->fullAlloc($groups['hormigas'], 'TIER_ANTS_FIXED');
            $remaining -= $reqAnts;
        } else {
            $this->partialAlloc($groups['hormigas'], $remaining, $reqAnts, 'TIER_ANTS_INSUFFICIENT');
            $remaining = 0;
        }

        // 3. Base Pesada: SE ASIGNA LO ESTIMADO SIEMPRE QUE HAYA MARGEN
        $reqHeavy = $groups['base_pesada']->sum('kwh_estimated');
        if ($remaining >= $reqHeavy) {
            $this->fullAlloc($groups['base_pesada'], 'TIER_HEAVY_BASE');
            $remaining -= $reqHeavy;
        } else {
            $this->partialAlloc($groups['base_pesada'], $remaining, $reqHeavy, 'TIER_HEAVY_INSUFFICIENT');
            $remaining = 0;
        }

        // 4. Ballenas y Otros: ABSORBEN EL RESTO (Aires, TV, etc.)
        // Son los que más varían y donde el ajuste es más elástico.
        $variableGroup = $groups['ballenas']->concat($groups['otros']);
        $reqVariable = $variableGroup->sum('kwh_estimated');

        if ($remaining > 0) {
            if ($reqVariable > 0) {
                // Distribuimos el remanente proporcionalmente entre las ballenas
                $this->partialAlloc($variableGroup, $remaining, $reqVariable, 'TIER_VARIABLE_ADJUSTED');
                $remaining = 0;
            } else {
                // Si no hay ballenas declaradas, repartimos el remanente en lo que haya (probablemente base pesada)
                // para que la suma total sea exacta a la factura
                $this->redistributeOverfill($usages, $remaining);
            }
        } else {
            // Si el remaining llegó a 0 antes, las ballenas quedan en 0 (ajuste forzado por falta de energía)
            $this->fullAlloc($variableGroup, 'TIER_VARIABLE_ZERO');
        }

        return $usages;
    }

    private function getEquipmentTier($equipment)
    {
        $name = strtolower($equipment->name ?? '');
        $typeName = strtolower($equipment->type->name ?? '');
        $combined = $name . ' ' . $typeName;

        if (str_contains($combined, 'heladera') || str_contains($combined, 'freezer') || str_contains($combined, 'router') || str_contains($combined, 'modem') || str_contains($combined, 'alarma')) {
            return 'base_critica';
        }

        if (str_contains($combined, 'termotanque') || str_contains($combined, 'calefón') || str_contains($combined, 'bomba de agua')) {
            return 'base_pesada';
        }

        if (str_contains($combined, 'lámpara') || str_contains($combined, 'lampara') || str_contains($combined, 'led') || str_contains($combined, 'tubo fluorescente') || str_contains($combined, 'cargador')) {
            return 'hormigas';
        }

        if (str_contains($combined, 'aire') || str_contains($combined, 'estufa') || str_contains($combined, 'caloventor') || str_contains($combined, 'radiador') || str_contains($combined, 'televisor') || str_contains($combined, 'tv') || str_contains($combined, 'gamer') || str_contains($combined, 'consola')) {
            return 'ballenas';
        }

        return 'otros';
    }

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

    private function redistributeOverfill($usages, $overfill)
    {
        $totalAllocated = $usages->sum('kwh_reconciled');
        if ($totalAllocated == 0) return;

        $usages->each(function ($u) use ($overfill, $totalAllocated) {
            $share = $u->kwh_reconciled / $totalAllocated;
            $u->kwh_reconciled += ($overfill * $share);
        });
    }

    private function setReconciled($usage, $val, $note)
    {
        $usage->kwh_reconciled = round($val, 2);
        $usage->calibration_note = $note;
        $usage->calibration_status = $note;
    }
}
