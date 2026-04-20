<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\EquipmentUsage;
use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;

class TestStandbyPower extends Command
{
    protected $signature = 'test:standby';
    protected $description = 'Test Standby Power Calculation Logic';

    public function handle(ConsumptionAnalysisService $service)
    {
        $this->info('Testing Standby Power Calculation...');

        // 1. Setup Mock Data
        $type = EquipmentType::firstOrCreate(
            ['name' => 'Test Console'],
            [
                'category_id' => 1, // Asumiendo categoría 1 existe
                'default_power_watts' => 100,
                'default_standby_power_w' => 5.0 // 5 Watts standby
            ]
        );
        // Asegurar que tenga standby power
        $type->update(['default_standby_power_w' => 5.0]);

        $equipment = new Equipment([
            'name' => 'Test PS5',
            'nominal_power_w' => 100, // 100W active
            'is_standby' => true,
            'avg_daily_use_hours' => 2, // 2 hours active -> 22 hours standby
        ]);
        $equipment->type()->associate($type);

        $usage = new EquipmentUsage();
        $usage->equipment = $equipment;
        $usage->avg_daily_use_hours = 2;
        $usage->use_days_in_period = 30;
        $usage->usage_frequency = 'diario';

        $invoice = new Invoice(); // Dummy invoice

        // 2. Calculate Expected Values
        // Active: 100W * 2h * 30d = 6000 Wh = 6 kWh
        // Standby: 5W * 22h * 30d = 3300 Wh = 3.3 kWh
        // Total: 9.3 kWh

        $consumption = $service->calculateEquipmentConsumption($usage, $invoice);

        $this->info("Active Power: 100W");
        $this->info("Standby Power: 5W");
        $this->info("Daily Usage: 2h");
        $this->info("Period: 30 days");
        
        $this->info("Expected Active: 6.00 kWh");
        $this->info("Expected Standby: 3.30 kWh");
        $this->info("Expected Total: 9.30 kWh");
        
        $this->info("Calculated Total: {$consumption} kWh");

        if (abs($consumption - 9.30) < 0.1) {
            $this->info('✅ TEST PASSED: Standby calculation is correct.');
        } else {
            $this->error('❌ TEST FAILED: Calculation mismatch.');
        }

        // 3. Test without Standby
        $equipment->is_standby = false;
        $consumptionNoStandby = $service->calculateEquipmentConsumption($usage, $invoice);
        
        $this->info("\nTesting with is_standby = false...");
        $this->info("Expected Total: 6.00 kWh");
        $this->info("Calculated Total: {$consumptionNoStandby} kWh");

        if (abs($consumptionNoStandby - 6.00) < 0.1) {
            $this->info('✅ TEST PASSED: Non-standby calculation is correct.');
        } else {
            $this->error('❌ TEST FAILED: Non-standby calculation mismatch.');
        }
    }
}
