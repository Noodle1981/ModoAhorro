<?php

namespace App\Services\Solar;

class SolarWaterService
{
    // Constants
    const CP_WATER = 1; // kcal/kg°C
    const KCAL_TO_KWH = 0.00116;
    const TARGET_TEMP = 45; // °C
    
    // Efficiencies
    const EFFICIENCY_ELECTRIC = 0.95;
    const EFFICIENCY_GAS_GLP = 0.65; // Garrafa
    const ENERGY_PER_GARRAFA_10KG = 128; // kWh
    
    // Prices (Estimated defaults, should ideally come from DB or config)
    const PRICE_GARRAFA_10KG = 12000; // ARS (Estimated)
    const PRICE_M3_GAS = 800; // ARS (Estimated)
    const ENERGY_PER_M3_GAS = 9.3; // kWh
    const EFFICIENCY_GAS_NATURAL = 0.65;
    
    public function calculateWaterHeater(int $peopleCount, ?array $climateProfile, float $electricityTariff)
    {
        // 1. Demand Estimation
        // Assumption: All people are adults for base calculation if breakdown not available
        // Base: 50L per person (Adult)
        $litersPerDay = $peopleCount * 50; 
        
        // Habit Factor (Normal = 1.0)
        $habitFactor = 1.0;
        $totalDailyDemandLiters = $litersPerDay * $habitFactor;
        
        // 2. Sizing
        $equipmentSize = 150;
        if ($totalDailyDemandLiters <= 150) {
            $equipmentSize = 150;
        } elseif ($totalDailyDemandLiters <= 200) {
            $equipmentSize = 200;
        } elseif ($totalDailyDemandLiters <= 250) {
            $equipmentSize = 250;
        } else {
            $equipmentSize = 300;
        }
        
        // 3. Energy Calculation
        // We need monthly energy requirement.
        // Q = m * Cp * DeltaT
        // DeltaT = 45 - (T_min - 2)
        
        // Use climate profile min temp if available, else default to 10°C
        $minTemp = $climateProfile['temp_min_avg'] ?? 10;
        $inletTemp = $minTemp - 2;
        $deltaT = self::TARGET_TEMP - $inletTemp;
        
        // Daily Energy in kcal
        $dailyEnergyKcal = $totalDailyDemandLiters * self::CP_WATER * $deltaT;
        
        // Daily Energy in kWh
        $dailyEnergyKwh = $dailyEnergyKcal * self::KCAL_TO_KWH;
        
        // Monthly Energy in kWh
        $monthlyEnergyKwh = $dailyEnergyKwh * 30;
        
        // 4. Financial Savings (ROI)
        // Solar Fraction: 75% average annual savings
        $solarFraction = 0.75;
        
        // Case 1: Electric Water Heater
        $monthlyCostElectric = ($monthlyEnergyKwh / self::EFFICIENCY_ELECTRIC) * $electricityTariff;
        $monthlySavingsElectric = $monthlyCostElectric * $solarFraction;
        
        // Case 2: Gas (Garrafa 10kg)
        // Effective energy per garrafa = 128 * 0.65 = 83.2 kWh
        $effectiveEnergyPerGarrafa = self::ENERGY_PER_GARRAFA_10KG * self::EFFICIENCY_GAS_GLP;
        $garrafasPerMonth = $monthlyEnergyKwh / $effectiveEnergyPerGarrafa;
        $monthlyCostGas = $garrafasPerMonth * self::PRICE_GARRAFA_10KG;
        $monthlySavingsGas = $monthlyCostGas * $solarFraction;

        // Case 3: Natural Gas (Domiciliario)
        // Effective energy per m3 = 9.3 * 0.65 = 6.045 kWh
        $effectiveEnergyPerM3 = self::ENERGY_PER_M3_GAS * self::EFFICIENCY_GAS_NATURAL;
        $m3PerMonth = $monthlyEnergyKwh / $effectiveEnergyPerM3;
        $monthlyCostGasNatural = $m3PerMonth * self::PRICE_M3_GAS;
        $monthlySavingsGasNatural = $monthlyCostGasNatural * $solarFraction;
        
        return [
            'people_count' => $peopleCount,
            'daily_liters' => $totalDailyDemandLiters,
            'recommended_equipment_liters' => $equipmentSize,
            'monthly_energy_kwh' => round($monthlyEnergyKwh, 1),
            'savings' => [
                'electric' => [
                    'monthly_cost' => round($monthlyCostElectric, 0),
                    'monthly_savings' => round($monthlySavingsElectric, 0),
                    'annual_savings' => round($monthlySavingsElectric * 12, 0),
                ],
                'gas' => [
                    'garrafas_per_month' => round($garrafasPerMonth, 1),
                    'monthly_cost' => round($monthlyCostGas, 0),
                    'monthly_savings' => round($monthlySavingsGas, 0),
                    'annual_savings' => round($monthlySavingsGas * 12, 0),
                ],
                'gas_natural' => [
                    'm3_per_month' => round($m3PerMonth, 1),
                    'monthly_cost' => round($monthlyCostGasNatural, 0),
                    'monthly_savings' => round($monthlySavingsGasNatural, 0),
                    'annual_savings' => round($monthlySavingsGasNatural * 12, 0),
                ]
            ]
        ];
    }
}
