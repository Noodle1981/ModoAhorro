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
    
    // Prices (Updated: San Juan Dec 2024)
    const PRICE_GARRAFA_10KG = 10500; // ARS
    const PRICE_M3_GAS = 2943; // ARS
    const ENERGY_PER_M3_GAS = 9.3; // kWh
    const EFFICIENCY_GAS_NATURAL = 0.65;
    const PILOT_LIGHT_CONSUMPTION_M3 = 4.0; // m3/month wasted
    
    public function calculateWaterHeater(int $peopleCount, ?array $climateProfile, float $electricityTariff)
    {
        // 1. Demand Estimation
        // Assumption: All people are adults for base calculation if breakdown not available
        // Base: 50L per person (Adult)
        $litersPerDay = $peopleCount * 50; 
        
        // Habit Factor (Normal = 1.0)
        $habitFactor = 1.0;
        $totalDailyDemandLiters = $litersPerDay * $habitFactor;
        
        // 2. Sizing (Updated per Technical Spec)
        // <= 3 people: 150L
        // <= 4 people: 200L
        // 5+ people: 300L
        $equipmentSize = 150;
        if ($peopleCount <= 3) {
            $equipmentSize = 150;
        } elseif ($peopleCount <= 4) {
            $equipmentSize = 200;
        } else {
            $equipmentSize = 300;
        }
        
        // 3. Energy Calculation
        // We need monthly energy requirement.
        // Q = m * Cp * DeltaT
        // DeltaT = 45 - (T_min - 2)
        
        // Use climate profile min temp if available, else default to 10°C (Winter inlet)
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
        
        // Add Pilot Light Consumption (Passive waste)
        // This is fully saved if the gas heater is replaced or turned off in summer, 
        // but typically solar pre-heaters still use the gas heater as backup.
        // However, the spec implies we should consider the savings. 
        // If we assume the solar heater replaces the gas heater or significantly reduces usage:
        // The pilot light might still be on. 
        // BUT, usually "Ahorro" calculations include the efficiency gains.
        // Let's add the pilot light cost to the BASE cost, and assume we save 75% of the ACTIVE consumption.
        // Does the solar heater eliminate the pilot light? Only if it's a replacement.
        // Let's assume the pilot light is part of the inefficiency we are avoiding or that modern heaters don't have it.
        // The spec lists `PILOT_LIGHT_CONSUMPTION: 4.0`.
        // Let's add it to the monthly consumption of the CURRENT system.
        $totalM3PerMonth = $m3PerMonth + self::PILOT_LIGHT_CONSUMPTION_M3;
        
        $monthlyCostGasNatural = $totalM3PerMonth * self::PRICE_M3_GAS;
        
        // Savings: 75% of the active consumption + potentially pilot light if we switch systems?
        // Let's apply the solar fraction to the TOTAL for simplicity, or just the active.
        // "El sol cubre el 75% de la demanda anual".
        // If we have a solar heater, we might still have the gas heater as backup (so pilot light stays).
        // UNLESS we switch to an electric backup or the solar heater has an electric kit.
        // Let's stick to the simple formula: Savings = Cost * 0.75.
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
                    'm3_per_month' => round($totalM3PerMonth, 1),
                    'monthly_cost' => round($monthlyCostGasNatural, 0),
                    'monthly_savings' => round($monthlySavingsGasNatural, 0),
                    'annual_savings' => round($monthlySavingsGasNatural * 12, 0),
                ]
            ]
        ];
    }
}
