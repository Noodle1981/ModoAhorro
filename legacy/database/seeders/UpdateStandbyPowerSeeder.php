<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentType;

class UpdateStandbyPowerSeeder extends Seeder
{
    public function run()
    {
        $standbyValues = [
            'Televisor LED' => 1.5, // Matches "Televisor LED 32"" and "Televisor LED 50" 4K" via LIKE
            'Consola de Videojuegos' => 5.0,
            'Microondas' => 3.0,
            'PC de Escritorio' => 5.0,
            'Impresora' => 3.0,
            'Equipo de Audio' => 4.0,
            'Decodificador TV' => 5.0,
            'Modem / Router WiFi' => 5.0,
            'Cargador de Celular' => 0.5,
            'Notebook' => 2.0,
        ];

        foreach ($standbyValues as $typeName => $watts) {
            // Buscamos por nombre aproximado o exacto
            $type = EquipmentType::where('name', 'LIKE', "%$typeName%")->first();
            
            if ($type) {
                $type->update(['default_standby_power_w' => $watts]);
                $this->command->info("Updated standby power for: $typeName -> {$watts}W");
            } else {
                $this->command->warn("Equipment Type not found: $typeName");
            }
        }
    }
}
