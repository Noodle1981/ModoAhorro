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
        // 1. CLASIFICACIÓN EN 3 NIVELES
        $baseLoad = $usages->filter(function ($u) {
            $type = $u->equipment->type->name ?? '';
            $fixedTypes = ['Heladera', 'Freezer', 'Router Wifi', 'Modem', 'Alarma', 'Cámaras', 'Camaras'];
            foreach ($fixedTypes as $fixed) {
                if (stripos($type, $fixed) !== false) {
                    return true;
                }
            }
            return false;
        });

        $ants = $usages->filter(function ($u) use ($baseLoad) {
            if ($baseLoad->contains('id', $u->id)) return false;
            $cat = $u->equipment->category->name ?? '';
            
            // Regla 1: Iluminación y Portátiles SIEMPRE son Hormigas (Indispensables/Bajo consumo)
            if ($cat === 'Iluminación' || $cat === 'Portátiles') {
                return true;
            }

            // Regla 2: ELIMINAMOS la regla de "< 100W" genérica.
            // Un Monitor de 50W NO es una hormiga, es parte de la PC.
            // Una TV de 85W NO es una hormiga, es entretenimiento.
            
            return false;
        });

        $whales = $usages->reject(function ($u) use ($baseLoad, $ants) {
            return $baseLoad->contains('id', $u->id) || $ants->contains('id', $u->id);
        });

        // 2. CALCULAR NECESIDADES TEÓRICAS
        $kwhBase = $baseLoad->sum('kwh_estimated');
        $kwhAnts = $ants->sum('kwh_estimated');
        $kwhWhales = $whales->sum('kwh_estimated');

        // 3. PROCESO DE LLENADO DE CUBETAS (WATERFALL)
        $remainingInvoice = $invoiceTotalKwh;

        // --- PASO A: Satisfacer Carga Base (Heladera/Router) ---
        if ($remainingInvoice >= $kwhBase) {
            // Hay suficiente para la heladera
            $baseLoad->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated, 'PROTECTED_BASE'));
            $remainingInvoice -= $kwhBase;
        } else {
            // ALERTA ROJA: La factura (ej. 50) es menor que la heladera (ej. 80).
            // Recortamos la heladera porque no queda otra (Medidor roto o casa vacía).
            $factor = ($kwhBase > 0) ? $remainingInvoice / $kwhBase : 0;
            $baseLoad->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated * $factor, 'CRITICAL_BASE_CUT'));
            
            // Las hormigas y ballenas mueren
            $ants->merge($whales)->each(fn($u) => $this->setReconciled($u, 0, 'ZERO_ALLOCATION'));
            
            return $usages; // FIN PREMATURO
        }

        // --- PASO B: Satisfacer Hormigas (Luces) ---
        if ($remainingInvoice >= $kwhAnts) {
            // Hay suficiente para las luces
            $ants->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated, 'PROTECTED_ANT'));
            $remainingInvoice -= $kwhAnts;
        } else {
            // Alcanzó para heladera, pero no para todas las luces.
            // Recortamos luces, matamos ballenas.
            $factor = ($kwhAnts > 0) ? $remainingInvoice / $kwhAnts : 0;
            $ants->each(fn($u) => $this->setReconciled($u, $u->kwh_estimated * $factor, 'PARTIAL_ANT_CUT'));
            $whales->each(fn($u) => $this->setReconciled($u, 0, 'ZERO_ALLOCATION'));
            
            return $usages; // FIN PREMATURO
        }

        // --- PASO C: Distribuir Sobrante a Ballenas (Ponderado) ---
        // Si llegamos aquí, Heladeras y Luces están al 100%. Repartimos lo que sobra.
        
        if ($kwhWhales <= 0) return $usages;

        // Calculamos score ponderado para dar prioridad al Aire sobre la PC
        $totalWeightedScore = $whales->sum(function ($u) {
            return $u->kwh_estimated * $this->getCategoryWeight($u->equipment->category->name ?? '');
        });

        $whales->each(function ($u) use ($remainingInvoice, $totalWeightedScore) {
            $weight = $this->getCategoryWeight($u->equipment->category->name ?? '');
            $score = $u->kwh_estimated * $weight;
            $share = ($totalWeightedScore > 0) ? ($score / $totalWeightedScore) : 0;
            
            $this->setReconciled($u, $remainingInvoice * $share, "WEIGHTED_ADJUSTMENT");
            $u->calibration_status = 'WEIGHTED_ADJUSTMENT'; // Set status explicitly for UI
        });

        return $usages;
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
