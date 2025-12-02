<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entity;
use App\Models\Invoice;
use App\Services\VacationService;
use Carbon\Carbon;

class TestAnomalyLogic extends Command
{
    protected $signature = 'test:anomaly';
    protected $description = 'Test Data Anomaly Logic';

    public function handle(VacationService $service)
    {
        $this->info('Testing Data Anomaly Logic...');

        // 1. Setup Data
        $entity = Entity::first();
        if (!$entity) {
            $this->error('No entity found.');
            return;
        }
        
        $user = $entity->users()->first(); // Assuming relation exists or we attach one
        if (!$user) {
             // Fallback: try to find a user and attach
             $user = \App\Models\User::first();
             if ($user) {
                 $entity->users()->syncWithoutDetaching([$user->id]);
             } else {
                 $this->error('No user found.');
                 return;
             }
        }
        
        $this->info("Using User: {$user->name} (ID: {$user->id})");

        // Create a dummy invoice for "Current Month" that overlaps with "Vacation"
        // Vacation starts "now" for 15 days.
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Ensure we have a contract linked to this entity
        $contract = \App\Models\Contract::where('entity_id', $entity->id)->first();
        if (!$contract) {
             $utility = \App\Models\UtilityCompany::firstOrCreate(['name' => 'Test Utility']);
             
             $this->info("Utility ID: " . $utility->id);
             $provId = \App\Models\Proveedor::first()->id ?? 1;
             $this->info("Proveedor ID: " . $provId);
             
             try {
                 $contract = \App\Models\Contract::create([
                     'entity_id' => $entity->id,
                     'proveedor_id' => $provId,
                     'utility_company_id' => $utility->id,
                     'contract_number' => 'TEST-CONTRACT-' . rand(1000,9999),
                     'supply_number' => 'SUPPLY-' . rand(1000,9999),
                     'meter_number' => 'METER-' . rand(1000,9999),
                     'client_number' => 'CLIENT-' . rand(1000,9999),
                     'tariff_type' => 'RESIDENTIAL',
                     'rate_name' => 'T1-R1',
                     'start_date' => now()->subYear(),
                     'end_date' => now()->addYear(),
                     'is_active' => true,
                     // Add other required fields if any. Assuming minimal for now.
                 ]);
             } catch (\Exception $e) {
                 $this->error("Contract Creation Failed: " . $e->getMessage());
                 return;
             }
        }

        try {
            $invoice = Invoice::create([
                'contract_id' => $contract->id,
                'invoice_number' => 'TEST-ANOMALY-' . rand(1000,9999),
                'issue_date' => now(),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_amount' => 5000,
                'total_energy_consumed_kwh' => 100, // Low consumption
                'is_representative' => true,
            ]);
        } catch (\Exception $e) {
            $this->error("Invoice Creation Failed: " . $e->getMessage());
            return;
        }
        
        $this->info("Created Invoice: {$invoice->invoice_number} (Representative: TRUE)");

        // 2. Trigger Vacation Logic
        $days = 15;
        $this->info("Simulating Vacation: $days days (Threshold is 7)");
        
        $markedCount = $service->markAnomalousInvoices($entity, $days);
        
        // 3. Verify
        $invoice->refresh();
        
        if ($markedCount > 0 && $invoice->is_representative == false && $invoice->anomaly_reason === 'VACATION_MODE') {
            $this->info("✅ Success: Invoice marked as anomalous.");
            $this->info("Reason: {$invoice->anomaly_reason}");
        } else {
            $this->error("❌ Failed: Invoice not marked.");
            $this->info("Marked Count: $markedCount");
            $this->info("Is Representative: " . ($invoice->is_representative ? 'TRUE' : 'FALSE'));
        }
        
        // Cleanup
        $invoice->delete();
    }
}
