<?php

namespace App\Services;

use App\Models\EquipmentUsage;
use App\Models\Invoice;

class ConsumptionAnalysisService
{
    protected $usageSuggestionService;
    protected $climateDataService;

    public function __construct(
        \App\Services\Climate\UsageSuggestionService $usageSuggestionService,
        \App\Services\Climate\ClimateDataService $climateDataService
    ) {
        $this->usageSuggestionService = $usageSuggestionService;
        $this->climateDataService = $climateDataService;
    }
    /**
     * Calcula el consumo total de un equipo en un periodo
     * Implementa la fórmula: Energía Secundaria (kWh) = (P × h × d × FC) / η
     * Donde:
     * - P = Potencia nominal (kW)
     * - h = Horas de uso
     * - d = Días en período
     * - FC = Factor de Carga (duty cycle)
     * - η = Eficiencia del equipo
     * 
     * @param EquipmentUsage $usage
     * @param Invoice $invoice
     * @return float Consumo en kWh
     */
    public function calculateEquipmentConsumption(EquipmentUsage $usage, Invoice $invoice): float
    {
        $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
        
        // Obtener factor de carga y eficiencia del tipo de equipo
        $equipmentType = $usage->equipment->type;
        $loadFactor = $equipmentType->load_factor ?? 1.0;
        $efficiency = $equipmentType->efficiency ?? 1.0;
        
        // Si la frecuencia es diaria o semanal, usar la lógica tradicional
        if (in_array($usage->usage_frequency, ['diario', 'semanal']) || empty($usage->usage_frequency)) {
            $hoursPerDay = $usage->avg_daily_use_hours ?? 0;
            $daysInPeriod = $usage->use_days_in_period ?? 0;
            
            // Fórmula con factor de carga y eficiencia
            // Energía Secundaria = (P × h × d × FC) / η
            return round(($powerKw * $hoursPerDay * $daysInPeriod * $loadFactor) / $efficiency, 2);
        }
        
        // Si la frecuencia es quincenal, mensual o puntual, usar cantidad de usos y duración promedio
        $usageCount = $usage->usage_count ?? 0;
        $avgUseDuration = $usage->avg_use_duration ?? 0; // en horas
        
        // Aplicar factor de carga y eficiencia también aquí
        return round(($powerKw * $avgUseDuration * $usageCount * $loadFactor) / $efficiency, 2);
    }

    /**
     * Calcula el consumo total de todos los equipos de una factura
     * @param Invoice $invoice
     * @return array [equipo_id => consumo_kwh]
     */
    public function calculateInvoiceConsumption(Invoice $invoice): array
    {
        $result = [];
        foreach ($invoice->equipmentUsages()->with('equipment')->get() as $usage) {
            $result[$usage->equipment_id] = $this->calculateEquipmentConsumption($usage, $invoice);
        }
        return $result;
    }

    /**
     * Analiza el consumo comparando lo declarado vs lo sugerido por clima
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function analyzeConsumptionWithClimate(Invoice $invoice): array
    {
        // 1. Cargar datos climáticos si no existen
        $this->climateDataService->loadDataForInvoice($invoice);
        
        $locality = $invoice->contract->entity->locality;
        if (!$locality || !$locality->latitude || !$locality->longitude) {
            return [
                'success' => false,
                'message' => 'Localidad no configurada o sin coordenadas',
                'details' => []
            ];
        }

        $analysis = [];
        $usages = $invoice->equipmentUsages()->with(['equipment.category', 'equipment.type'])->get();

        foreach ($usages as $usage) {
            // Consumo declarado (calculado con input usuario)
            $declaredKwh = $this->calculateEquipmentConsumption($usage, $invoice);
            
            // Sugerencia climática
            $suggestion = $this->usageSuggestionService->suggestClimateUsage(
                $usage->equipment,
                $invoice,
                $locality->latitude,
                $locality->longitude
            );

            $item = [
                'equipment_name' => $usage->equipment->name,
                'category' => ($usage->equipment->category && $usage->equipment->category->name) ? $usage->equipment->category->name : 'Otro',
                'declared_kwh' => $declaredKwh,
                'declared_hours' => $usage->avg_daily_use_hours,
                'suggestion' => null,
                'discrepancy_kwh' => 0,
                'is_efficient' => true
            ];

            if ($suggestion) {
                // Calcular consumo sugerido
                // Clonamos el usage para no modificar el original en BD, solo para cálculo
                $suggestedUsage = $usage->replicate();
                $suggestedUsage->avg_daily_use_hours = $suggestion['suggested_hours_per_day'];
                // Asumimos que los días efectivos son los días del período para simplificar comparación diaria,
                // o usamos los effective_days si queremos ser más precisos con "días que se prendió".
                // Para comparar peras con peras (promedio diario), mantenemos los días del período
                // pero ajustamos las horas promedio.
                
                $suggestedKwh = $this->calculateEquipmentConsumption($suggestedUsage, $invoice);

                $item['suggestion'] = [
                    'hours' => $suggestion['suggested_hours_per_day'],
                    'kwh' => $suggestedKwh,
                    'reason' => $suggestion['explanation'],
                    'confidence' => $suggestion['confidence']
                ];

                $item['discrepancy_kwh'] = round($declaredKwh - $suggestedKwh, 2);
                
                // Si consume más de lo sugerido (+10% tolerancia), no es eficiente
                if ($declaredKwh > ($suggestedKwh * 1.1)) {
                    $item['is_efficient'] = false;
                }
            }

            $analysis[] = $item;
        }

        return [
            'success' => true,
            'locality' => $locality->name,
            'details' => $analysis
        ];
    }
}
