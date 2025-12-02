<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestGridOptimization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:grid-optimization';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Grid Optimization Logic';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\GridOptimizerService $optimizer)
    {
        $this->info('Testing Grid Optimization Logic...');

        // 1. Setup Data
        // Ensure Tariff Scheme exists
        $scheme = \App\Models\TariffScheme::first();
        if (!$scheme) {
            $this->info('Seeding Tariffs...');
            $this->call('db:seed', ['--class' => 'TariffSeeder']);
            $scheme = \App\Models\TariffScheme::first();
        }

        // Create Dummy Usage
        // We need an equipment type that is shiftable
        $category = \App\Models\EquipmentCategory::firstOrCreate(['name' => 'Test Category']);
        
        $type = \App\Models\EquipmentType::firstOrCreate(
            ['name' => 'Test Lavarropas'],
            [
                'category_id' => $category->id,
                'is_shiftable' => true,
                'default_power_watts' => 2000,
                'default_avg_daily_use_hours' => 1
            ]
        );
        // Ensure it is shiftable (in case it existed but was false)
        $type->update(['is_shiftable' => true]);

        // Mock Usage Object (as expected by Service)
        $usage = (object) [
            'equipment' => (object) [
                'name' => 'Lavarropas Test',
                'type' => $type
            ],
            'kwh_reconciled' => 30, // 30 kWh/month (1 hour * 2000W * 15 days approx)
            'daily_kwh' => 1
        ];

        $usages = collect([$usage]);

        // 2. Run Optimizer
        $opportunities = $optimizer->calculateShiftSavings($usages, $scheme);

        // 3. Verify
        if (count($opportunities) > 0) {
            $opp = $opportunities[0];
            $this->info("✅ Success: Opportunity found for {$opp['equipment']}");
            $this->info("Current Cost: $" . $opp['current_cost']);
            $this->info("Optimized Cost: $" . $opp['optimized_cost']);
            $this->info("Savings: $" . $opp['potential_savings']);
            
            // Expected Savings: 30 kWh * (180 - 90) = 2700
            if ($opp['potential_savings'] == 2700) {
                $this->info("✅ Calculation Correct (2700)");
            } else {
                $this->error("❌ Calculation Incorrect. Expected 2700, got " . $opp['potential_savings']);
            }

            // Verify Plan B
            if (isset($opp['suggestion_secondary'])) {
                $this->info("✅ Plan B Suggested: " . $opp['suggestion_secondary']);
                // Expected Savings Plan B: 30 kWh * (180 - 100) = 2400
                if (str_contains($opp['suggestion_secondary'], '2.400')) {
                     $this->info("✅ Plan B Calculation Correct (2400)");
                } else {
                     $this->error("❌ Plan B Calculation Incorrect.");
                }
            } else {
                $this->error("❌ Plan B NOT Suggested (Should be available)");
            }
        } else {
            $this->error("❌ Failed: No opportunities found.");
        }
    }
}
