<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entity;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Room;
use App\Services\VacationService;

class TestVacationModule extends Command
{
    protected $signature = 'test:vacation';
    protected $description = 'Test Vacation Module Logic';

    public function handle(VacationService $service)
    {
        $this->info('Testing Vacation Module Logic...');

        // 1. Setup Mock Data
        // Create a temporary entity structure in memory or use existing if possible.
        // For reliability, let's use the first existing entity or create a dummy one.
        
        $entity = Entity::first();
        if (!$entity) {
            $this->error('No entity found to test. Please create one first.');
            return;
        }
        
        // Ensure we have specific equipment for testing rules
        // We'll mock the check by temporarily adding equipment if needed, 
        // but since we can't easily mock relationships on real models without DB interaction,
        // we will rely on what's there or add temp records.
        
        $this->info("Using Entity: {$entity->name}");

        // --- TEST CASE 1: Short Trip (5 days) ---
        $this->info("\n--- Test Case 1: Short Trip (5 days) ---");
        $result = $service->generateChecklist($entity, 5);
        
        // Check Fridge Rule
        $fridgeItem = collect($result['checklist'])->firstWhere('title', 'Heladera / Freezer');
        if ($fridgeItem) {
            $this->info("Fridge Action: {$fridgeItem['action']}");
            if ($fridgeItem['action'] === 'MODO ECO (Mínimo)') {
                $this->info('✅ Fridge Rule (Short Trip): Passed');
            } else {
                $this->error('❌ Fridge Rule (Short Trip): Failed');
            }
        } else {
            $this->warn('⚠️ No Fridge found in inventory.');
        }

        // --- TEST CASE 2: Long Trip (25 days) ---
        $this->info("\n--- Test Case 2: Long Trip (25 days) ---");
        $result = $service->generateChecklist($entity, 25);
        
        // Check Fridge Rule
        $fridgeItem = collect($result['checklist'])->firstWhere('title', 'Heladera / Freezer');
        if ($fridgeItem) {
            $this->info("Fridge Action: {$fridgeItem['action']}");
            if ($fridgeItem['action'] === 'DESCONECTAR Y ABRIR') {
                $this->info('✅ Fridge Rule (Long Trip): Passed');
            } else {
                $this->error('❌ Fridge Rule (Long Trip): Failed');
            }
        }

        // Check Router Rule (Security)
        // We need to know if the entity has cameras to verify the expected output
        $hasSecurity = $entity->rooms->flatMap->equipment->contains(function ($eq) {
             return str_contains(strtolower($eq->name), 'cámara') || str_contains(strtolower($eq->name), 'alarma');
        });
        
        $routerItem = collect($result['checklist'])->firstWhere('title', 'Router Wi-Fi');
        if ($routerItem) {
            $this->info("Router Action: {$routerItem['action']}");
            if ($hasSecurity) {
                if ($routerItem['action'] === 'NO TOCAR') {
                    $this->info('✅ Router Rule (Security Present): Passed');
                } else {
                    $this->error('❌ Router Rule (Security Present): Failed');
                }
            } else {
                if ($routerItem['action'] === 'DESCONECTAR') {
                    $this->info('✅ Router Rule (No Security): Passed');
                } else {
                    $this->error('❌ Router Rule (No Security): Failed');
                }
            }
        }

        // --- TEST CASE 3: Water Heater Logic ---
        $this->info("\n--- Test Case 3: Water Heater Logic ---");
        
        // Temporarily add a Water Heater to the entity's first room if not present
        $waterHeaterType = EquipmentType::firstOrCreate(
            ['name' => 'Termotanque Eléctrico'],
            ['default_power_watts' => 1500, 'category_id' => 1] // Ensure category exists or use valid ID
        );
        
        $room = $entity->rooms->first();
        $heater = new Equipment([
            'name' => 'Termotanque Eléctrico Test',
            'nominal_power_w' => 1500,
            'is_standby' => false,
            'avg_daily_use_hours' => 4
        ]);
        $heater->type()->associate($waterHeaterType);
        $heater->room()->associate($room);
        $heater->save();
        
        $this->info("Added Temporary Water Heater: {$heater->name}");
        
        // Re-run generation
        $result = $service->generateChecklist($entity, 15);
        
        $heaterItem = collect($result['checklist'])->firstWhere('title', 'Termotanque');
        if ($heaterItem) {
            $this->info("Water Heater Action: {$heaterItem['action']}");
            if ($heaterItem['action'] === 'DESCONECTAR') {
                $this->info('✅ Water Heater Rule: Passed');
            } else {
                $this->error('❌ Water Heater Rule: Failed');
            }
        } else {
            $this->error('❌ Water Heater Rule: Failed (Item not found)');
        }
        
        // Cleanup
        $heater->delete();
        $this->info("Removed Temporary Water Heater");

        $this->info("\nTotal Potential Savings (25 days): $" . number_format($result['total_savings'], 2));
    }
}
