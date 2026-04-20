<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\ClimateService;

class LoadClimateData extends Command
{
    protected $signature = 'climate:load {invoice_id?}';
    protected $description = 'Carga datos climáticos para una factura específica o todas las facturas';

    public function handle(ClimateService $climateService)
    {
        $invoiceId = $this->argument('invoice_id');
        
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            
            if (!$invoice) {
                $this->error("Factura #{$invoiceId} no encontrada");
                return 1;
            }
            
            $this->info("🌡️ Cargando datos climáticos para Factura #{$invoiceId}...");
            $this->info("Período: {$invoice->start_date} - {$invoice->end_date}");
            
            try {
                $climateService->loadDataForInvoice($invoice);
                $this->info("✅ Datos climáticos cargados exitosamente");
                
                // Mostrar estadísticas
                $locality = $invoice->contract->entity->locality;
                if ($locality) {
                    $stats = $climateService->getClimateStats(
                        $locality->latitude,
                        $locality->longitude,
                        \Carbon\Carbon::parse($invoice->start_date),
                        \Carbon\Carbon::parse($invoice->end_date)
                    );
                    
                    $this->newLine();
                    $this->info("📊 Estadísticas climáticas:");
                    $this->info("  • Días totales: {$stats['total_days']}");
                    $this->info("  • Días calurosos (≥28°C): {$stats['hot_days_count']}");
                    $this->info("  • Días fríos (<15°C): {$stats['cold_days_count']}");
                    $this->info("  • Temp. promedio: " . round($stats['avg_temp_avg'], 1) . "°C");
                    $this->info("  • Temp. máxima: " . round($stats['avg_temp_max'], 1) . "°C");
                    $this->info("  • Temp. mínima: " . round($stats['avg_temp_min'], 1) . "°C");
                    
                    if (isset($stats['avg_sunshine_duration'])) {
                        $this->info("  • Horas de sol promedio: " . round($stats['avg_sunshine_duration'] / 3600, 1) . " h");
                    }
                    if (isset($stats['avg_radiation'])) {
                        $this->info("  • Radiación solar promedio: " . round($stats['avg_radiation'], 2) . " MJ/m²");
                    }
                }
                
            } catch (\Exception $e) {
                $this->error("Error al cargar datos climáticos: " . $e->getMessage());
                return 1;
            }
            
        } else {
            // Cargar para todas las facturas
            $invoices = Invoice::all();
            $this->info("🌡️ Cargando datos climáticos para {$invoices->count()} facturas...");
            
            $bar = $this->output->createProgressBar($invoices->count());
            $bar->start();
            
            foreach ($invoices as $invoice) {
                try {
                    $climateService->loadDataForInvoice($invoice);
                    $bar->advance();
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("Error en factura #{$invoice->id}: " . $e->getMessage());
                }
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("✅ Datos climáticos cargados para todas las facturas");
        }
        
        return 0;
    }
}
