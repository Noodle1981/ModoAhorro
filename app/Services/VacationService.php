<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Equipment;

class VacationService
{
    /**
     * Generates a personalized vacation checklist based on trip duration and inventory.
     *
     * @param Entity $entity
     * @param int $days
     * @return array
     */
    public function generateChecklist(Entity $entity, int $days): array
    {
        $checklist = [];
        $totalSavings = 0;

        // 1. Security & Connectivity Rule (Router)
        $connectivity = $this->checkConnectivityRule($entity, $days);
        $checklist[] = $connectivity;
        $totalSavings += $connectivity['savings'] ?? 0;

        // 2. Refrigeration Rule (Fridge)
        $refrigeration = $this->checkRefrigerationRule($entity, $days);
        if ($refrigeration) {
            $checklist[] = $refrigeration;
            $totalSavings += $refrigeration['savings'] ?? 0;
        }

        // 3. Water Heater Rule
        $waterHeater = $this->checkWaterHeaterRule($entity, $days);
        if ($waterHeater) {
            $checklist[] = $waterHeater;
            $totalSavings += $waterHeater['savings'] ?? 0;
        }

        // 4. Vampire Power Rule (Standby)
        $vampires = $this->checkVampireRule($entity, $days);
        if ($vampires) {
            $checklist[] = $vampires;
            $totalSavings += $vampires['savings'] ?? 0;
        }
        
        // 5. Lighting Rule (Simulation)
        $lighting = $this->checkLightingRule($entity);
        $checklist[] = $lighting;

        return [
            'checklist' => $checklist,
            'total_savings' => $totalSavings
        ];
    }

    private function checkConnectivityRule(Entity $entity, int $days): array
    {
        // Check for security devices
        $hasSecurity = $entity->rooms->flatMap->equipment->contains(function ($eq) {
            $name = strtolower($eq->name);
            $type = strtolower($eq->type->name ?? '');
            return str_contains($name, 'cámara') || str_contains($name, 'camara') ||
                   str_contains($name, 'alarma') || str_contains($name, 'sensor') ||
                   str_contains($type, 'cámara') || str_contains($type, 'camara') ||
                   str_contains($type, 'alarma');
        });

        if ($hasSecurity) {
            return [
                'category' => 'security',
                'title' => 'Router Wi-Fi',
                'action' => 'NO TOCAR',
                'description' => 'Tus cámaras y sensores dependen del Wi-Fi. No lo desconectes.',
                'icon' => 'bi-router-fill',
                'color' => 'danger', // Critical to NOT touch
                'savings' => 0
            ];
        }

        // Calculate potential savings if turned off
        // Find router/modem power
        $router = $entity->rooms->flatMap->equipment->first(function ($eq) {
            $name = strtolower($eq->name);
            return str_contains($name, 'modem') || str_contains($name, 'router');
        });
        
        $powerW = $router ? ($router->nominal_power_w ?? 10) : 10; // Default 10W
        $savingsKwh = ($powerW * 24 * $days) / 1000;
        $savingsMoney = $savingsKwh * 150; // Approx tariff

        return [
            'category' => 'savings',
            'title' => 'Router Wi-Fi',
            'action' => 'DESCONECTAR',
            'description' => 'No tienes equipos de seguridad. Apágalo para ahorrar.',
            'icon' => 'bi-router',
            'color' => 'success',
            'savings' => $savingsMoney
        ];
    }

    private function checkRefrigerationRule(Entity $entity, int $days): ?array
    {
        $fridge = $entity->rooms->flatMap->equipment->first(function ($eq) {
            $name = strtolower($eq->name);
            return str_contains($name, 'heladera') || str_contains($name, 'freezer');
        });

        if (!$fridge) return null;

        if ($days < 20) {
            return [
                'category' => 'recommendation',
                'title' => 'Heladera / Freezer',
                'action' => 'MODO ECO (Mínimo)',
                'description' => 'No la desconectes. Sube la temperatura al mínimo (1) y vacía perecederos.',
                'icon' => 'bi-snow',
                'color' => 'warning',
                'savings' => 0
            ];
        }

        // Long trip > 20 days
        $powerW = $fridge->nominal_power_w ?? 150;
        $loadFactor = 0.35; // Compressor runs ~35% of time
        $savingsKwh = ($powerW * 24 * $loadFactor * $days) / 1000;
        $savingsMoney = $savingsKwh * 150;

        return [
            'category' => 'critical',
            'title' => 'Heladera / Freezer',
            'action' => 'DESCONECTAR Y ABRIR',
            'description' => 'Por más de 20 días, conviene vaciarla y desconectarla. ¡Deja la puerta abierta!',
            'icon' => 'bi-snow2',
            'color' => 'danger',
            'savings' => $savingsMoney
        ];
    }

    private function checkWaterHeaterRule(Entity $entity, int $days): ?array
    {
        $heater = $entity->rooms->flatMap->equipment->first(function ($eq) {
            $name = strtolower($eq->name);
            return str_contains($name, 'termotanque') || str_contains($name, 'calefón') || str_contains($name, 'calefon');
        });

        if (!$heater) return null;

        // Assume electric for savings calc if not specified (safe bet for high savings)
        // If we knew it was gas, we'd say "Pilot OFF"
        
        // Simple calc: 1500W * 4h/day standby loss equivalent? 
        // Or just assume a daily cost. Let's use a conservative estimate.
        // A standard electric water heater loses ~1-2 kWh/day just maintaining temp.
        $dailyLossKwh = 1.5; 
        $savingsKwh = $dailyLossKwh * $days;
        $savingsMoney = $savingsKwh * 150;

        return [
            'category' => 'critical',
            'title' => 'Termotanque',
            'action' => 'DESCONECTAR',
            'description' => 'Es un gasto innecesario mantener agua caliente que nadie usará.',
            'icon' => 'bi-droplet-half',
            'color' => 'danger',
            'savings' => $savingsMoney
        ];
    }

    private function checkVampireRule(Entity $entity, int $days): ?array
    {
        $vampires = $entity->rooms->flatMap->equipment->filter(function ($eq) {
            return ($eq->type->default_standby_power_w ?? 0) > 0 &&
                   !str_contains(strtolower($eq->name), 'modem') && 
                   !str_contains(strtolower($eq->name), 'router');
        });

        if ($vampires->isEmpty()) return null;

        $totalDailyStandbyKwh = 0;
        foreach ($vampires as $v) {
            $watts = $v->type->default_standby_power_w;
            $totalDailyStandbyKwh += ($watts * 24) / 1000;
        }

        $savingsKwh = $totalDailyStandbyKwh * $days;
        $savingsMoney = $savingsKwh * 150;

        return [
            'category' => 'critical',
            'title' => 'Vampiros (TV, PC, Consolas)',
            'action' => 'DESCONECTAR TODO',
            'description' => 'Desenchufa de la pared para ahorrar y proteger de tormentas.',
            'icon' => 'bi-plug-fill',
            'color' => 'danger',
            'savings' => $savingsMoney
        ];
    }
    
    private function checkLightingRule(Entity $entity): array
    {
        return [
            'category' => 'security',
            'title' => 'Iluminación',
            'action' => 'SIMULACIÓN',
            'description' => 'No dejes luces fijas 24h. Usa fotocélulas o timers para simular presencia.',
            'icon' => 'bi-lightbulb',
            'color' => 'info',
            'savings' => 0
        ];
    }
}
