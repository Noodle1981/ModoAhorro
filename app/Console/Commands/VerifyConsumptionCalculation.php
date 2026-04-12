<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;
use App\Services\Climate\UsageSuggestionService;
use App\Services\ClimateService;

class VerifyConsumptionCalculation extends Command
{
    protected $signature = 'verify:consumption {invoice_id=2}';
    protected $description = 'Verifica el cálculo de consumo energético para una factura';

    public function handle(ConsumptionAnalysisService $service)
    {
        $invoiceId = $this->argument('invoice_id');
        
        $invoice = Invoice::with('equipmentUsages.equipment.type')->find($invoiceId);
        
        if (!$invoice) {
            $this->error("Factura #{$invoiceId} no encontrada");
            return 1;
        }
        
        $this->info("🔍 Verificando cálculo de consumo - Factura #{$invoiceId}");
        $this->info("Periodo: {$invoice->start_date} - {$invoice->end_date}");
        $this->newLine();
        
        // service is now injected and correctly configured
        
        $total = 0;
        $details = [];
        
        foreach ($invoice->equipmentUsages as $usage) {
            $kwh = $service->calculateEquipmentConsumption($usage, $invoice);
            $loadFactor = $usage->equipment->type->load_factor ?? 1.0;
            $hours = $usage->avg_daily_use_hours ?? 0;
            $days = $usage->use_days_in_period ?? 0;
            
            $details[] = [
                'Equipo' => $usage->equipment->name,
                'Potencia (W)' => $usage->equipment->nominal_power_w,
                'Horas/día' => $hours,
                'Días' => $days,
                'Load Factor' => $loadFactor,
                'Consumo (kWh)' => round($kwh, 2),
            ];
            
            $total += $kwh;
        }
        
        $this->table(
            ['Equipo', 'Potencia (W)', 'Horas/día', 'Días', 'Load Factor', 'Consumo (kWh)'],
            $details
        );
        
        $this->newLine();
        $this->info("📊 RESUMEN:");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  Total Calculado: " . round($total, 2) . " kWh");
        $this->info("  Total Facturado: " . $invoice->total_energy_consumed_kwh . " kWh");
        
        $diff = $total - $invoice->total_energy_consumed_kwh;
        $percentage = ($total / $invoice->total_energy_consumed_kwh) * 100;
        
        $diffColor = abs($percentage - 100) <= 10 ? 'green' : (abs($percentage - 100) <= 30 ? 'yellow' : 'red');
        
        $this->info("  Diferencia: " . round($diff, 2) . " kWh");
        $this->line("  Precisión: <fg={$diffColor}>" . round($percentage, 1) . "%</>");
        
        if ($percentage >= 90 && $percentage <= 110) {
            $this->line("  <fg=green>✅ Excelente precisión (90-110%)</>");
        } elseif ($percentage >= 70 && $percentage <= 130) {
            $this->line("  <fg=yellow>⚠️  Diferencia aceptable (70-130%)</>");
        } else {
            $this->line("  <fg=red>❌ Revisar ajustes (fuera de rango)</>");
        }
        
        return 0;
    }
}
