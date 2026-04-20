<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\EquipmentUsage;
use App\Models\Invoice;
use App\Models\EquipmentType;
use App\Models\MaintenanceLog;
use App\Services\ConsumptionAnalysisService;
use App\Services\MaintenanceService;
use Carbon\Carbon;

class TestMaintenanceLogic extends Command
{
    protected $signature = 'test:maintenance';
    protected $description = 'Test maintenance penalty logic';

    public function handle(ConsumptionAnalysisService $consumptionService, MaintenanceService $maintenanceService)
    {
        $this->info('Testing Maintenance Logic...');

        // 1. Setup Data
        $acType = EquipmentType::where('name', 'like', '%Aire Acondicionado%')->first();
        if (!$acType) {
            $this->error('No AC type found. Run seeders first.');
            return;
        }

        // Create dummy equipment
        $equipment = Equipment::create([
            'name' => 'Test AC Unit',
            'room_id' => 1, // Assuming room 1 exists
            'category_id' => $acType->category_id,
            'type_id' => $acType->id,
            'nominal_power_w' => 1000,
            'is_active' => true
        ]);

        // Create dummy invoice
        $invoice = Invoice::first(); // Use existing or create new
        if (!$invoice) {
             $invoice = Invoice::create([
                 'contract_id' => 1,
                 'start_date' => Carbon::now()->subDays(30),
                 'end_date' => Carbon::now(),
                 'total_energy_consumed_kwh' => 0,
                 'total_cost' => 0
             ]);
        }

        // Create usage
        $usage = EquipmentUsage::create([
            'invoice_id' => $invoice->id,
            'equipment_id' => $equipment->id,
            'avg_daily_use_hours' => 5,
            'use_days_in_period' => 30,
            'usage_frequency' => 'diario'
        ]);

        // 2. Test Case 1: Overdue (No logs)
        $this->info("\n--- Case 1: No Maintenance Logs (Overdue) ---");
        $status = $maintenanceService->checkStatus($equipment);
        $this->info("Health Score: " . $status['health_score']);
        $this->info("Penalty Factor: " . $status['penalty_factor']);
        
        $consumptionWithPenalty = $consumptionService->calculateEquipmentConsumption($usage, $invoice);
        
        // Calculate expected base (ignoring penalty for a moment to check factor)
        // We can't easily get the internal load_factor here without reflection or query, 
        // so we will compare against the "After Fix" value later.
        
        $this->info("Calculated Consumption (With Penalty): {$consumptionWithPenalty} kWh");

        // 3. Test Case 2: Maintenance Completed
        $this->info("\n--- Case 2: Maintenance Completed Today ---");
        
        // Log all pending tasks
        foreach ($status['pending_tasks'] as $pending) {
            $task = $acType->maintenanceTasks()->where('title', $pending['task'])->first();
            MaintenanceLog::create([
                'equipment_id' => $equipment->id,
                'maintenance_task_id' => $task->id,
                'completed_at' => Carbon::now(),
                'notes' => 'Test maintenance'
            ]);
        }

        $status = $maintenanceService->checkStatus($equipment);
        $this->info("Health Score: " . $status['health_score']);
        $this->info("Penalty Factor: " . $status['penalty_factor']);

        $consumptionClean = $consumptionService->calculateEquipmentConsumption($usage, $invoice);
        $this->info("Calculated Consumption (Clean): {$consumptionClean} kWh");

        if ($consumptionWithPenalty > $consumptionClean) {
            $this->info("✅ Penalty Applied Successfully ({$consumptionWithPenalty} > {$consumptionClean})");
            $ratio = $consumptionWithPenalty / $consumptionClean;
            $this->info("Effective Penalty Ratio: " . round($ratio, 2));
        } else {
            $this->error("❌ Penalty NOT Applied correctly");
        }

        // Cleanup
        $equipment->delete();
        $usage->delete();
        // Don't delete invoice if it was existing
    }
}
