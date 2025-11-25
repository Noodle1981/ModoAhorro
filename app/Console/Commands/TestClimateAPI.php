<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Climate\ClimateDataService;
use App\Models\Locality;
use Carbon\Carbon;

class TestClimateAPI extends Command
{
    protected $signature = 'test:climate {locality_id?}';
    protected $description = 'Test Open-Meteo API integration';

    public function handle()
    {
        $this->info('🌡️  Probando integración con Open-Meteo API...');
        $this->newLine();
        
        $localityId = $this->argument('locality_id');
        
        if ($localityId) {
            $locality = Locality::find($localityId);
        } else {
            // Default: San Juan Capital
            $locality = Locality::where('name', 'Capital')
                ->whereHas('province', function($q) {
                    $q->where('name', 'San Juan');
                })
                ->first();
        }
        
        if (!$locality) {
            $this->error('❌ No se encontró la localidad (ID: ' . ($localityId ?? 'Capital, San Juan') . ')');
            return 1;
        }
        
        // Asegurar que tenga coordenadas (si no tiene, poner las de San Juan Capital para probar)
        if (!$locality->latitude || !$locality->longitude) {
            $this->warn('⚠️  Localidad sin coordenadas. Usando coordenadas de prueba (San Juan).');
            $locality->latitude = -31.5375;
            $locality->longitude = -68.5364;
            $locality->save();
        }
        
        $this->info("📍 Localidad: {$locality->name}, {$locality->province->name}");
        $this->info("📍 Coordenadas: Lat {$locality->latitude}, Long {$locality->longitude}");
        $this->newLine();
        
        // Probar servicio de clima
        $climateService = new ClimateDataService();
        
        // Usar un rango de fechas reciente pero histórico (ej. mes pasado)
        $endDate = Carbon::now()->subDays(2);
        $startDate = $endDate->copy()->subDays(30);
        
        $this->info("📡 Consultando datos históricos ({$startDate->format('Y-m-d')} a {$endDate->format('Y-m-d')})...");
        
        try {
            // 1. Fetch and Store
            $result = $climateService->fetchHistoricalData($locality, $startDate, $endDate);
            
            if (!$result['success']) {
                $this->error('❌ Error al obtener datos: ' . $result['message']);
                return 1;
            }
            
            $this->info("✅ {$result['message']}");
            
            // 2. Save (already done in fetchHistoricalData if using the new service structure? No, user reference separated them)
            // Wait, my implementation of fetchHistoricalData calls fetchFromOpenMeteo which returns array.
            // It DOES NOT save to DB automatically in my implementation of fetchHistoricalData?
            // Let me check my implementation of fetchHistoricalData.
            // It calls fetchFromOpenMeteo. fetchFromOpenMeteo returns array.
            // It DOES NOT call saveWeatherData.
            // The user's reference separated them.
            // So I need to call saveWeatherData here.
            
            $inserted = $climateService->saveWeatherData($locality, $result['data']);
            $this->info("💾 Guardados {$inserted} registros en la base de datos.");
            
            // 3. Get Stats
            /*
            $stats = $climateService->getClimateStats(
                $locality->latitude,
                $locality->longitude,
                $startDate,
                $endDate
            );
            
            $this->newLine();
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->line("📊 ESTADÍSTICAS CLIMÁTICAS");
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();
            
            $this->line("🌡️  Temperatura máxima promedio: {$stats['avg_temp_max']}°C");
            $this->line("🌡️  Temperatura mínima promedio: {$stats['avg_temp_min']}°C");
            $this->line("☀️  Días de calor (>28°C): {$stats['hot_days_count']} de {$stats['total_days']} días");
            $this->line("❄️  Días de frío (<15°C): {$stats['cold_days_count']} de {$stats['total_days']} días");
            $this->newLine();
            
            // Mostrar muestra de datos
            if (!empty($result['data'])) {
                $sample = $result['data'][0];
                $this->line("📝 Muestra del primer día ({$sample['date']}):");
                $this->line("   - Temp Max: {$sample['max_temp_celsius']}°C");
                $this->line("   - CDD: {$sample['cooling_degree_days']}");
                $this->line("   - HDD: {$sample['heating_degree_days']}");
                $this->line("   - Precipitación: {$sample['precipitation_mm']}mm");
                $this->line("   - Viento: {$sample['wind_speed_kmh']}km/h");
                $this->line("   - Humedad: " . ($sample['humidity_percent'] ?? 'N/A') . "%");
            }
            */
            
            $this->info("Reached step 4");
            // 4. Test Consumption Analysis
            $this->newLine();
            $this->line("----------------------------------");
            $this->line("ANALISIS DE CONSUMO (SIMULADO)");
            $this->line("----------------------------------");
            
            // Buscar una factura real para probar
            
            $invoice = \App\Models\Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment'])->first();
            
            if ($invoice) {
                $this->info("Factura #{$invoice->invoice_number} ({$invoice->start_date} - {$invoice->end_date})");
                
                $analysisService = app(\App\Services\ConsumptionAnalysisService::class);
                
                // Debug: Test loadDataForInvoice separately
                $this->info("Testing loadDataForInvoice...");
                $climateService = app(\App\Services\Climate\ClimateDataService::class);
                $loadResult = $climateService->loadDataForInvoice($invoice);
                if (!$loadResult['success']) {
                    $this->error("loadDataForInvoice failed: " . $loadResult['message']);
                } else {
                    $this->info("loadDataForInvoice success");
                }

                $this->info("Testing analyzeConsumptionWithClimate...");
                
                try {
                    $analysis = $analysisService->analyzeConsumptionWithClimate($invoice);
                } catch (\Throwable $t) {
                    $this->error("Crash in analyzeConsumptionWithClimate: " . $t->getMessage());
                    $this->error($t->getTraceAsString());
                    return 1;
                }
                
                if ($analysis['success']) {
                    foreach ($analysis['details'] as $item) {
                        $this->line("🔹 {$item['equipment_name']} ({$item['category']})");
                        $this->line("   - Declarado: {$item['declared_hours']}h/día ({$item['declared_kwh']} kWh)");
                        
                        if ($item['suggestion']) {
                            $diff = $item['discrepancy_kwh'];
                            $icon = $item['is_efficient'] ? '✅' : '⚠️';
                            $this->line("   - Sugerido:  {$item['suggestion']['hours']}h/día ({$item['suggestion']['kwh']} kWh)");
                            $this->line("   - Diferencia: {$diff} kWh {$icon}");
                            $this->line("   - Razón: {$item['suggestion']['reason']}");
                        } else {
                            $this->line("   - Sin sugerencia climática (OK)");
                        }
                        $this->newLine();
                    }
                } else {
                    $this->error("Error en análisis: " . $analysis['message']);
                }
                
            } else {
                $this->warn("No se encontraron facturas para probar el análisis completo.");
            }
            
            //$this->info("Skipping analysis logic for now.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error excepción:');
            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
