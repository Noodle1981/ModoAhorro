<?php

namespace App\Services;

use App\Models\EquipmentUsage;
use App\Models\Invoice;
use Carbon\Carbon;

class ConsumptionAnalysisService
{
    protected $usageSuggestionService;
    protected $climateDataService;
    protected $energyEngine;
    protected $climateService;
    protected $maintenanceService;

    public function __construct(
        \App\Services\Climate\UsageSuggestionService $usageSuggestionService,
        \App\Services\Climate\ClimateDataService $climateDataService,
        \App\Services\EnergyEngineService $energyEngine,
        \App\Services\ClimateService $climateService,
        \App\Services\MaintenanceService $maintenanceService
    ) {
        $this->usageSuggestionService = $usageSuggestionService;
        $this->climateDataService = $climateDataService;
        $this->energyEngine = $energyEngine;
        $this->climateService = $climateService;
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * Proxy para configurar el motor interno
     */
    public function setEngineData($facturaKwh, $diasPeriodo, $categoriaHogar = 'C', $gradosDia = [])
    {
        $this->energyEngine->setData($facturaKwh, $diasPeriodo, $categoriaHogar, $gradosDia);
    }
    /**
     * Calcula el consumo total de un equipo en un periodo
     * 
     * IMPORTANTE: Esta fórmula calcula el consumo FACTURADO (lo que cobra el medidor).
     * El medidor mide la potencia de ENTRADA (Input Power), no la potencia útil.
     * Por lo tanto, NO dividimos por efficiency.
     * 
     * Fórmula: Energía (kWh) = P × h × d × FC
     * Donde:
     * - P = Potencia nominal de etiqueta (kW) - Input Power
     * - h = Horas de uso promedio diario
     * - d = Días en el período
     * - FC = Factor de Uso Real (load_factor) - Incluye duty cycle y carga parcial
     * 
     * @param EquipmentUsage $usage
     * @param Invoice $invoice
     * @return float Consumo en kWh
     */
    public function calculateEquipmentConsumption(EquipmentUsage $usage, Invoice $invoice): float
    {
        // 1. Potencia Nominal (Convertida a kW)
        // Asumimos que nominal_power_w es la potencia de ETIQUETA (Input Power)
        $powerKw = ($usage->equipment->nominal_power_w ?? 0) / 1000;
        
        // 2. Factor de Uso Real
        // Combina Load Factor (Potencia real vs Nominal) + Duty Cycle (Tiempo encendido vs apagado)
        // Si no está definido, usamos 1.0 (peor escenario)
        $equipmentType = $usage->equipment->type;
        $realUsageFactor = $equipmentType->load_factor ?? 1.0;
        
        // CRITICAL FIX: Eliminamos $efficiency de la ecuación de costo/facturación.
        // El medidor cobra la energía entrante, la ineficiencia ya está incluida en el consumo.
        
        // 3. Cálculo para Frecuencia Diaria/Semanal (O cualquier frecuencia si se definieron horas diarias)
        // Si hay horas diarias definidas, usamos la lógica de días * horas
        if ($usage->avg_daily_use_hours > 0 || in_array($usage->usage_frequency, ['diario', 'diariamente', 'semanal']) || empty($usage->usage_frequency)) {
            $hoursPerDay = $usage->avg_daily_use_hours ?? 0;
            $daysInPeriod = $usage->use_days_in_period;
            
            // Fallback: Si no hay días guardados (null/0), calcular según función centralizada
            if (empty($daysInPeriod)) {
                $totalDays = \Carbon\Carbon::parse($invoice->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->end_date));
                // Asegurar al menos 1 día si las fechas son iguales
                $totalDays = max(1, $totalDays);
                
                $daysInPeriod = $this->getDaysByFrequency($usage->usage_frequency, $totalDays);
            }
            
            // 🌡️ AJUSTE CLIMÁTICO: Para equipos de climatización, ajustar días según clima
            $effectiveDays = $this->getEffectiveDaysWithClimate($usage, $invoice, $daysInPeriod);
            
            // Fórmula: Potencia (kW) * Horas * Días Efectivos * Factor de Uso Real
            $consumption = $powerKw * $hoursPerDay * $effectiveDays * $realUsageFactor;

            // 🌡️ AJUSTE CLIMÁTICO ESPECÍFICO: Termotanques
            // Consumen MÁS en invierno (agua fría, mayor pérdida) y MENOS en verano.
            if ($this->isWaterHeater($usage)) {
                $factor = $this->getWaterHeaterClimateFactor($usage, $invoice);
                $consumption *= $factor;
            }

            // 🛠️ AJUSTE POR MANTENIMIENTO: Penalización por tareas vencidas
            $maintenancePenalty = $this->maintenanceService->getPenaltyFactor($usage->equipment);
            $consumption *= $maintenancePenalty;

            // 🧛 CÁLCULO DE CONSUMO VAMPIRO (STANDBY)
            if ($usage->equipment->is_standby) {
                // Horas en espera = 24 - Horas de uso
                $standbyHoursPerDay = max(0, 24 - $hoursPerDay);
                
                // Potencia de standby (desde el tipo de equipo)
                $standbyPowerKw = ($usage->equipment->type->default_standby_power_w ?? 0) / 1000;
                
                // Consumo Standby = Potencia * Horas * Días
                // Nota: El standby ocurre todos los días que el equipo está enchufado (daysInPeriod),
                // independientemente de si se usó activamente o no.
                $standbyConsumption = $standbyPowerKw * $standbyHoursPerDay * $daysInPeriod;
                
                $consumption += $standbyConsumption;
            }
            
            return round($consumption, 4);
        }
        
        // 4. Cálculo para uso Puntual (quincenal, mensual, puntual)
        $usageCount = $usage->usage_count ?? 0;
        $avgUseDuration = $usage->avg_use_duration ?? 0; // en horas
        
        $consumption = $powerKw * $avgUseDuration * $usageCount * $realUsageFactor;
        
        // 🛠️ AJUSTE POR MANTENIMIENTO: Penalización por tareas vencidas
        $maintenancePenalty = $this->maintenanceService->getPenaltyFactor($usage->equipment);
        $consumption *= $maintenancePenalty;

        return round($consumption, 4);
    }
    
    /**
     * Obtiene los días efectivos de uso considerando datos climáticos
     * Para aires acondicionados: solo cuenta días con temp ≥28°C
     * Para calefacción: solo cuenta días con temp <15°C
     * Para otros equipos: retorna los días del período sin ajuste
     * 
     * @param EquipmentUsage $usage
     * @param Invoice $invoice
     * @param int $totalDays Días totales del período
     * @return int Días efectivos de uso
     */
    private function getEffectiveDaysWithClimate(EquipmentUsage $usage, Invoice $invoice, int $totalDays): int
    {
        $category = $usage->equipment->category->name ?? '';
        
        // Solo aplicar ajuste climático a equipos de climatización
        if ($category !== 'Climatización') {
            return $totalDays;
        }

        // Obtener datos climáticos del motor (si ya se corrió) o del servicio
        $climateDays = $this->energyEngine->getClimateDays(); 
        
        // Si hay datos climáticos definidos (aunque sean 0), los usamos.
        // Solo hacemos fallback a totalDays si NO hay datos (array vacío o keys faltantes)
        $hasClimateData = isset($climateDays['cooling_days']) || isset($climateDays['heating_days']);
        
        if (!$hasClimateData) {
             return $totalDays; 
        }

        // Detectar tipo según CATEGORÍA (Más robusto que nombre)
        // Climatización -> Frío (Aires, Ventiladores) -> Usa Días de Calor
        // Calefacción   -> Calor (Estufas)           -> Usa Días de Frío
        $isCooling = ($category === 'Climatización');
        
        $detectedDays = $isCooling ? ($climateDays['cooling_days'] ?? 0) : ($climateDays['heating_days'] ?? 0);

        // Si la API detectó 0 días (ej: invierno para aire), usamos 0.
        // Si detectó días, usamos eso, pero nunca más que los días totales del periodo.
        return min($detectedDays, $totalDays);
        
        try {
            // Obtener coordenadas de la localidad
            $locality = $invoice->contract->entity->locality;
            if (!$locality || !$locality->latitude || !$locality->longitude) {
                \Log::info("🌡️ Sin coordenadas para {$usage->equipment->name}");
                return $totalDays; // Sin datos de localidad, usar días totales
            }
            
            // Cargar datos climáticos si no existen
            $this->climateDataService->loadDataForInvoice($invoice);
            
            // Obtener estadísticas climáticas
            $stats = $this->climateDataService->getClimateStats(
                $locality->latitude,
                $locality->longitude,
                \Carbon\Carbon::parse($invoice->start_date),
                \Carbon\Carbon::parse($invoice->end_date)
            );
            
            // Determinar si es aire acondicionado o calefacción
            $equipmentName = strtolower($usage->equipment->name);
            $typeName = strtolower($usage->equipment->type->name ?? '');
            
            // Aire acondicionado o ventilador
            if (str_contains($equipmentName, 'aire') || str_contains($typeName, 'aire acondicionado')) {
                // Solo usar días calurosos (temp ≥28°C)
                $effectiveDays = $stats['hot_days_count'] ?? 0;
                
                \Log::info("🌡️ AIRE: {$usage->equipment->name} - Días: {$totalDays} → {$effectiveDays} (hot days)");
                
                // Si no hay días calurosos, retornar 0 (no debería haberse usado)
                return max(0, $effectiveDays);
            }
            
            // Calefacción
            $heatingKeywords = ['caloventor', 'estufa', 'radiador', 'panel calefactor', 'calefactor'];
            foreach ($heatingKeywords as $keyword) {
                if (str_contains($equipmentName, $keyword) || str_contains($typeName, $keyword)) {
                    // Solo usar días fríos (temp <15°C)
                    $effectiveDays = $stats['cold_days_count'] ?? 0;
                    \Log::info("🌡️ CALEFACCIÓN: {$usage->equipment->name} - Días: {$totalDays} → {$effectiveDays} (cold days)");
                    return max(0, $effectiveDays);
                }
            }
            
            // Ventiladores: usar días calurosos también
            if (str_contains($equipmentName, 'ventilador') || str_contains($typeName, 'ventilador')) {
                $effectiveDays = $stats['hot_days_count'] ?? 0;
                \Log::info("🌡️ VENTILADOR: {$usage->equipment->name} - Días: {$totalDays} → {$effectiveDays} (hot days)");
                return max(0, $effectiveDays);
            }
            
        } catch (\Exception $e) {
            // Si hay error al obtener datos climáticos, usar días totales
            \Log::warning('Error al obtener datos climáticos para ajuste: ' . $e->getMessage());
            return $totalDays;
        }
        
        // Para otros equipos de climatización sin clasificar, usar días totales
        \Log::info("🌡️ SIN CLASIFICAR: {$usage->equipment->name} - Días: {$totalDays} (sin ajuste)");
        return $totalDays;
    }

    private function isWaterHeater(EquipmentUsage $usage): bool
    {
        $name = strtolower($usage->equipment->name);
        $type = strtolower($usage->equipment->type->name ?? '');
        $keywords = ['termotanque', 'calefón', 'calefon', 'bomba de agua'];
        
        foreach ($keywords as $keyword) {
            if (str_contains($name, $keyword) || str_contains($type, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function getWaterHeaterClimateFactor(EquipmentUsage $usage, Invoice $invoice): float
    {
        try {
            $locality = $invoice->contract->entity->locality;
            if (!$locality || !$locality->latitude || !$locality->longitude) {
                return 1.0;
            }

            $this->climateDataService->loadDataForInvoice($invoice);
            
            $stats = $this->climateDataService->getClimateStats(
                $locality->latitude,
                $locality->longitude,
                \Carbon\Carbon::parse($invoice->start_date),
                \Carbon\Carbon::parse($invoice->end_date)
            );

            $avgTemp = $stats['avg_temp_avg'] ?? 20;

            if ($avgTemp < 15) {
                \Log::info("🌡️ TERMOTANQUE (Invierno): {$usage->equipment->name} - Factor x1.25 (Temp: {$avgTemp}°C)");
                return 1.25;
            }

            if ($avgTemp > 25) {
                \Log::info("🌡️ TERMOTANQUE (Verano): {$usage->equipment->name} - Factor x0.85 (Temp: {$avgTemp}°C)");
                return 0.85;
            }

        } catch (\Exception $e) {
            \Log::warning('Error calculando factor termotanque: ' . $e->getMessage());
        }

        return 1.0;
    }

    /**
     * Calcula el consumo total de todos los equipos de una factura
     * @param Invoice $invoice
     * @return array [equipo_id => consumo_kwh]
     */
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
     * Calcula y CALIBRA el consumo para coincidir con la factura usando Motor v3.
     */
    public function calibrateInvoiceConsumption(Invoice $invoice): array
    {
        $entity = $invoice->contract->entity;
        
        // 1. Obtener Grados-Día v3 (Días de Calor y Frío)
        // Usamos el servicio de datos climáticos para obtener los CONTADORES de días
        $climateData = $this->climateDataService->getOrFetchData(
            $entity->locality,
            $invoice->start_date,
            $invoice->end_date
        );
        
        // Mapear a formato esperado por el motor
        $gradosDia = [
            'cooling_days' => $climateData['cooling_days'] ?? 0,
            'heating_days' => $climateData['heating_days'] ?? 0
        ];

        // 2. Preparar Equipos para el Motor
        $usages = $invoice->equipmentUsages()->with(['equipment.category', 'equipment.type'])->get();
        $equiposData = $usages->map(function($u) {
            return [
                'id' => $u->id,
                'nombre' => $u->equipment->name,
                'potencia_w' => $u->equipment->nominal_power_w,
                'horas_declaradas' => $u->avg_daily_use_hours,
                'periodicidad' => $u->usage_frequency, // debe coincidir con el mapa del motor
                'intensity' => $u->equipment->type->intensity ?? 'medio',
                'load_factor' => $u->equipment->type->load_factor ?? 1.0,
                'es_climatizacion' => ($u->equipment->category->name === 'Climatización'),
                'tipo_clima' => str_contains(strtolower($u->equipment->name), 'aire') ? 'frio' : 'calor',
                'is_validated' => $u->equipment->is_validated ?? false,
            ];
        })->toArray();

        // 3. Ejecutar Motor v3
        $engineResult = $this->energyEngine->setData(
            $invoice->total_energy_consumed_kwh,
            Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date)),
            $entity->thermal_profile['energy_label'] ?? 'C',
            $gradosDia
        )->calibrate($equiposData);

        // 4. Mapear resultados de vuelta a los Usages
        foreach ($usages as $usage) {
            $calibrado = collect($engineResult['equipos'])->firstWhere('id', $usage->id);
            if ($calibrado) {
                $usage->kwh_reconciled = $calibrado['calibrado_kwh'];
                $usage->audit_logs = $engineResult['logs']; // Opcional: inyectar logs
            }
        }

        return [
            'usages' => $usages,
            'summary' => $engineResult['precision_summary'] ?? null,
            'climate_data' => $engineResult['climate_data'] ?? []
        ];
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
