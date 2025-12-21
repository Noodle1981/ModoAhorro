<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Equipment;
use App\Models\EquipmentUsage;

class EquipmentUsageSeeder extends Seeder
{
    /**
     * Genera EquipmentUsages realistas para las 4 facturas.
     * Considera clima de San Juan, Argentina (períodos bimestrales)
     */
    public function run(): void
    {
        $this->command->info('🔧 Generando EquipmentUsages realistas para San Juan...');

        // Limpiar usages anteriores
        EquipmentUsage::truncate();

        $invoices = Invoice::orderBy('start_date')->get();

        foreach ($invoices as $invoice) {
            $this->command->info("\n📄 Factura #{$invoice->invoice_number}");
            $this->command->info("   Período: {$invoice->start_date} → {$invoice->end_date}");
            $this->command->info("   Consumo: {$invoice->total_energy_consumed_kwh} kWh");

            $equipment = Equipment::with(['type', 'category', 'room'])
                ->where('is_active', true)
                ->get();

            $season = $this->getSeason($invoice->start_date);
            $days = \Carbon\Carbon::parse($invoice->start_date)
                ->diffInDays(\Carbon\Carbon::parse($invoice->end_date));

            $this->command->info("   Estación: {$season}, Días: {$days}");

            foreach ($equipment as $equip) {
                $usage = $this->getUsagePattern($equip, $season, $days);

                if ($usage) {
                    EquipmentUsage::create([
                        'invoice_id' => $invoice->id,
                        'equipment_id' => $equip->id,
                        'usage_frequency' => $usage['frequency'],
                        'avg_daily_use_hours' => $usage['hours'],
                        'use_days_in_period' => $usage['days'],
                        'usage_count' => $usage['count'] ?? null,
                        'avg_use_duration' => $usage['duration'] ?? null,
                    ]);
                }
            }
        }

        $this->command->info("\n✅ EquipmentUsages generados: " . EquipmentUsage::count());
    }

    /**
     * Determina la estación según la fecha
     */
    private function getSeason($date): string
    {
        $month = \Carbon\Carbon::parse($date)->month;

        // Hemisferio Sur (San Juan, Argentina)
        if ($month >= 12 || $month <= 2)
            return 'verano';     // Dic-Feb (calor extremo)
        if ($month >= 3 && $month <= 5)
            return 'otoño';       // Mar-May (templado)
        if ($month >= 6 && $month <= 8)
            return 'invierno';    // Jun-Ago (frío)
        return 'primavera';                                    // Sep-Nov (templado)
    }

    /**
     * Patrones de uso realistas según equipo y estación
     * AJUSTADOS para coincidir con consumo real de San Juan
     */
    private function getUsagePattern($equipment, $season, $days): ?array
    {
        $name = strtolower($equipment->name);
        $category = $equipment->category->name ?? '';

        // --- BASE 24hs (siempre iguales) ---
        if (stripos($name, 'heladera') !== false) {
            return ['frequency' => 'diario', 'hours' => 24, 'days' => $days];
        }

        if (stripos($name, 'router') !== false || stripos($name, 'wifi') !== false) {
            return ['frequency' => 'diario', 'hours' => 24, 'days' => $days];
        }

        // --- CLIMATIZACIÓN (varía MUCHO por estación) ---
        if (stripos($name, 'aire') !== false) {
            if ($season === 'verano') {
                // San Juan: verano extremo, pero load_factor ya considera duty cycle
                return ['frequency' => 'diario', 'hours' => 8, 'days' => round($days * 0.7)]; // No todos los días
            } else {
                // Resto del año: no se usa
                return null; // No crear usage
            }
        }

        if (stripos($name, 'ventilador') !== false) {
            if ($season === 'verano') {
                return ['frequency' => 'diario', 'hours' => 5, 'days' => round($days * 0.8)];
            } elseif ($season === 'primavera' || $season === 'otoño') {
                return ['frequency' => 'diario', 'hours' => 2, 'days' => round($days * 0.3)];
            } else {
                return null;
            }
        }

        // --- ELECTRODOMÉSTICOS ---
        if (stripos($name, 'lavarropa') !== false || stripos($name, 'lavarropas') !== false) {
            // 2 veces por semana, 1.5hs por ciclo (load_factor ya reduce)
            return ['frequency' => 'semanal', 'hours' => 1.5, 'days' => round(($days / 7) * 2)];
        }

        if (stripos($name, 'microondas') !== false) {
            return ['frequency' => 'diario', 'hours' => 0.2, 'days' => $days]; // 12min/día
        }

        // --- ILUMINACIÓN ---
        if ($category === 'Iluminación') {
            // Más conservador: 3 horas/día promedio
            return ['frequency' => 'diario', 'hours' => 3, 'days' => $days];
        }

        // --- ENTRETENIMIENTO ---
        if (stripos($name, 'tv') !== false) {
            if (stripos($name, 'grande') !== false) {
                // TV principal: 4 horas/día
                return ['frequency' => 'diario', 'hours' => 4, 'days' => $days];
            } else {
                // TV chico: 2 horas/día
                return ['frequency' => 'diario', 'hours' => 2, 'days' => round($days * 0.7)]; // No todos los días
            }
        }

        // --- OFICINA ---
        if (stripos($name, 'pc') !== false || stripos($name, 'gamer') !== false) {
            // PC Gamer: solo fines de semana largo rato, días de semana poco
            return ['frequency' => 'diario', 'hours' => 4, 'days' => round($days * 0.6)];
        }

        if (stripos($name, 'monitor') !== false) {
            // Monitores usan cuando PC está encendida
            return ['frequency' => 'diario', 'hours' => 4, 'days' => round($days * 0.6)];
        }

        if (stripos($name, 'notebook') !== false) {
            return ['frequency' => 'diario', 'hours' => 3, 'days' => round($days * 0.5)];
        }

        // --- PORTÁTILES ---
        if ($category === 'Portátiles') {
            if (stripos($name, 'cargador') !== false) {
                return ['frequency' => 'diario', 'hours' => 1.5, 'days' => $days];
            }
        }

        // --- OTROS (poco uso) ---
        if (stripos($name, 'secador') !== false) {
            return ['frequency' => 'semanal', 'hours' => 0.15, 'days' => round(($days / 7) * 2)]; // 2 veces/sem, 10min
        }

        if (stripos($name, 'afeitar') !== false) {
            return ['frequency' => 'diario', 'hours' => 0.05, 'days' => round($days * 0.5)]; // 3min/día, día por medio
        }

        // --- DEFAULT: equipos sin patrón específico ---
        // No crear usage (será 0 en cálculo)
        return null;
    }
}
