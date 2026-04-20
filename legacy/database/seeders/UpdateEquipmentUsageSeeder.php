<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;

class UpdateEquipmentUsageSeeder extends Seeder
{
    public function run()
    {
        $equipments = Equipment::where('name', 'LIKE', '%PC%')
            ->orWhere('name', 'LIKE', '%Notebook%')
            ->get();

        foreach ($equipments as $eq) {
            $eq->update(['avg_daily_use_hours' => 4]);
            $this->command->info("Updated usage for {$eq->name} to 4 hours.");
        }
    }
}
