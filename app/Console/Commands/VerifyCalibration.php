<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;
use App\Services\Climate\UsageSuggestionService;
use App\Services\Climate\ClimateDataService;
use App\Services\ConsumptionCalibrator;

class VerifyCalibration extends Command
{
    protected $signature = 'verify:calibration {invoice_id=2}';
    protected $description = 'Verifica la calibración inteligente de consumo';

    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        $invoice = Invoice::with('equipmentUsages.equipment.type')->find($invoiceId);
        
        if (!$invoice) {
            $this->error("Factura #{$invoiceId} no encontrada");
            return 1;
        }
        
        $this->info("🔍 Verificando Calibración - Factura #{$invoiceId}");
        $this->info("Total Facturado: {$invoice->total_energy_consumed_kwh} kWh");
        $this->newLine();
        
        // Setup Services
        $climateService = new ClimateDataService();
        $usageSuggestionService = new UsageSuggestionService($climateService);
        $calibrator = new ConsumptionCalibrator();
        $service = new ConsumptionAnalysisService($usageSuggestionService, $climateService, $calibrator);
        
        // Execute Calibration
        $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
        
        $tableData = [];
        $totalTheoretical = 0;
        $totalCalibrated = 0;
        
        foreach ($calibratedUsages as $usage) {
            $theoretical = $usage->kwh_estimated;
            $calibrated = $usage->kwh_reconciled;
            $status = $usage->calibration_status;
            
            $totalTheoretical += $theoretical;
            $totalCalibrated += $calibrated;
            
            $diff = $theoretical - $calibrated;
            $percentChange = ($theoretical > 0) ? round(($diff / $theoretical) * 100, 1) : 0;
            
            $color = 'white';
            if ($status === 'TIER_1_FIXED') $color = 'green';
            if ($status === 'TIER_2_LOW_IMPACT') $color = 'cyan';
            if ($status === 'TIER_3_ADJUSTED') $color = 'yellow';
            
            $tableData[] = [
                'Equipo' => $usage->equipment->name,
                'Tier' => $status,
                'Teórico (kWh)' => round($theoretical, 2),
                'Calibrado (kWh)' => "<fg=$color>" . round($calibrated, 2) . "</>",
                'Ajuste' => $percentChange > 0 ? "-{$percentChange}%" : '0%',
            ];
        }
        
        $this->table(
            ['Equipo', 'Tier', 'Teórico', 'Calibrado', 'Ajuste'],
            $tableData
        );
        
        $this->newLine();
        $this->info("📊 RESUMEN FINAL:");
        $this->info("  Suma Teórica: " . round($totalTheoretical, 2) . " kWh");
        $this->info("  Suma Calibrada: " . round($totalCalibrated, 2) . " kWh");
        $this->info("  Factura Real: " . $invoice->total_energy_consumed_kwh . " kWh");
        
        $diff = abs($totalCalibrated - $invoice->total_energy_consumed_kwh);
        
        if ($diff < 0.1) {
            $this->info("\n✅ CALIBRACIÓN EXITOSA: La suma coincide con la factura.");
        } else {
            $this->error("\n❌ ERROR DE CALIBRACIÓN: Diferencia de {$diff} kWh.");
        }
        
        return 0;
    }
}
