<?php

namespace Database\Seeders;

use App\Models\EquipmentType;
use Illuminate\Database\Seeder;

class StandbyPowerSeeder extends Seeder
{
    public function run(): void
    {
        // Valores estimados en Watts (W)
        $standbyValues = [
            'Aire Acondicionado (2200 frigorías)' => 3.0,
            'Aire Acondicionado (3500 frigorías)' => 3.0,
            'Aire Acondicionado Grande' => 3.5,
            'Aire Acondicionado Mediano' => 3.0,
            'Aire Acondicionado Portátil' => 2.5,
            'Microondas' => 3.0,
            'TV LED 50"' => 0.5,
            'TV Grande (Smart 55+)' => 0.6,
            'TV Chico (32/Monitor)' => 0.4,
            'PC Gamer' => 4.0,
            'Monitor PC' => 0.5,
            'Notebook' => 1.5, // Cargador conectado
            'Router WiFi' => 6.0,
            'Cargador de Celular' => 0.3,
            'Lavarropas Automático' => 1.0,
            'Termotanque Eléctrico' => 2.0, // Panel digital
            'Secador de Pelo' => 0.0, // Interruptor mecánico
            'Máquina de Afeitar' => 0.5, // Base de carga
            'Ventilador de techo' => 0.0,
            'Caloventor' => 0.0,
            'Estufa Halógena' => 0.0,
            'Cortadora de Fiambre' => 0.0,
        ];

        foreach ($standbyValues as $name => $watts) {
            $type = EquipmentType::where('name', $name)->first();
            if ($type) {
                $type->update(['default_standby_power_w' => $watts]);
                $this->command->info("Updated {$name}: {$watts}W");
            }
        }
    }
}
