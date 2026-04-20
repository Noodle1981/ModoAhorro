<?php

namespace App\Services;

use App\Models\TariffScheme;
use Illuminate\Support\Collection;

class GridOptimizerService
{
    /**
     * Calculate potential savings by shifting usage to off-peak hours.
     *
     * @param Collection $usages Collection of EquipmentUsage
     * @param TariffScheme $tariffScheme The tariff scheme to compare against
     * @return array List of opportunities
     */
    public function calculateShiftSavings(Collection $usages, TariffScheme $tariffScheme): array
    {
        // 1. Get Prices
        // Assuming the scheme has bands. We need to find the most expensive (Peak) and cheapest (Off-Peak).
        $bands = $tariffScheme->bands;
        
        if ($bands->isEmpty()) {
            return [];
        }

        $peakBand = $bands->sortByDesc('price_per_kwh')->first();
        $offPeakBand = $bands->sortBy('price_per_kwh')->first();

        $pricePeak = $peakBand->price_per_kwh;
        $priceOffPeak = $offPeakBand->price_per_kwh;
        $priceDiff = $pricePeak - $priceOffPeak;

        // Plan B: Shoulder Band (Resto)
        // Definition: Not Off-Peak, but price < Off-Peak * 1.20
        $shoulderBand = $bands->filter(function($b) use ($offPeakBand) {
            return $b->id !== $offPeakBand->id && $b->price_per_kwh < ($offPeakBand->price_per_kwh * 1.20);
        })->first();

        // If there's no difference or negative (weird), no savings.
        if ($priceDiff <= 0) {
            return [];
        }

        $opportunities = [];

        // 2. Analyze Shiftable Equipment
        foreach ($usages as $usage) {
            // Check if equipment type is shiftable
            // We assume $usage->equipment->type is loaded or accessible
            $equipment = $usage->equipment;
            if (!$equipment || !$equipment->type || !$equipment->type->is_shiftable) {
                continue;
            }

            // Monthly Consumption (Reconciled if available, else estimated)
            // Assuming usage has kwh_reconciled or we calculate it.
            // For now, let's use a fallback if kwh_reconciled is null.
            $kwhMonth = $usage->kwh_reconciled ?? ($usage->daily_kwh * 30); // Fallback to daily * 30

            if ($kwhMonth <= 0) continue;

            // Potential Saving = Consumption * (PeakPrice - OffPeakPrice)
            // We assume the user is currently using it in Peak hours (Worst Case Scenario)
            $saving = $kwhMonth * $priceDiff;

            // Filter insignificant savings (e.g., < $100)
            if ($saving > 100) {
                $opp = [
                    'equipment' => $equipment->name,
                    'current_cost' => $kwhMonth * $pricePeak,
                    'optimized_cost' => $kwhMonth * $priceOffPeak,
                    'potential_savings' => $saving,
                    'suggestion' => "Úsalo entre las " . substr($offPeakBand->start_time, 0, 5) . " y " . substr($offPeakBand->end_time, 0, 5) . " hs",
                    'peak_band_name' => $peakBand->name,
                    'off_peak_band_name' => $offPeakBand->name,
                ];

                // Add Plan B if available
                if ($shoulderBand) {
                    $priceShoulder = $shoulderBand->price_per_kwh;
                    $savingShoulder = $kwhMonth * ($pricePeak - $priceShoulder);
                    
                    if ($savingShoulder > 0) {
                        $opp['suggestion_secondary'] = "O entre las " . substr($shoulderBand->start_time, 0, 5) . " y " . substr($shoulderBand->end_time, 0, 5) . " hs (Ahorras $" . number_format($savingShoulder, 0, ',', '.') . ")";
                    }
                }

                $opportunities[] = $opp;
            }
        }

        return $opportunities;
    }
}
