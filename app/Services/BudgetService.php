<?php

namespace App\Services;

use App\Models\Entity;
use App\Services\Climate\ClimateDataService;
use App\Services\Solar\SolarPowerService;

class BudgetService
{
    public function __construct(
        private ClimateDataService $climateService,
        private SolarPowerService $solarService
    ) {
    }

    /**
     * Calculate complete budget data for an entity
     */
    public function calculateBudgetData(Entity $entity): array
    {
        // Load necessary relationships
        $entity->load(['locality', 'contracts.invoices.equipmentUsages']);

        // Get climate profile
        $climateProfile = null;
        if ($entity->locality) {
            $climateProfile = $this->climateService->getLocalityClimateProfile($entity->locality);
        }

        // Get all invoices
        $invoices = $entity->contracts()
            ->with('invoices.equipmentUsages')
            ->get()
            ->flatMap(fn($contract) => $contract->invoices)
            ->sortByDesc('end_date');

        $latestInvoice = $invoices->first();

        // Calculate averages
        $averages = $this->calculateAverages($invoices);
        $monthlyConsumption = $averages['monthlyConsumption'];
        $maxMonthlyConsumption = $averages['maxMonthlyConsumption'];
        $averageTariff = $averages['averageTariff'];
        $invoiceCount = $invoices->count();

        // Prepare invoice data
        $invoiceData = null;
        if ($latestInvoice) {
            $latestConsumption = $latestInvoice->total_energy_consumed_kwh ?? $latestInvoice->equipmentUsages->sum('consumption_kwh');
            $startDate = \Carbon\Carbon::parse($latestInvoice->start_date);
            $endDate = \Carbon\Carbon::parse($latestInvoice->end_date);
            $periodDays = $startDate->diffInDays($endDate);

            $invoiceData = [
                'number' => $latestInvoice->invoice_number,
                'start_date' => $latestInvoice->start_date,
                'end_date' => $latestInvoice->end_date,
                'total_amount' => $latestInvoice->total_amount,
                'period_days' => $periodDays,
                'total_consumption' => $latestConsumption,
            ];
        }

        // Solar coverage calculation
        $solarData = null;
        $estimatedMonthlySavings = 0;
        $estimatedAnnualSavings = 0;

        if ($monthlyConsumption) {
            $availableArea = $entity->square_meters * 0.5;

            $solarData = $this->solarService->calculateSolarCoverage(
                $availableArea,
                $maxMonthlyConsumption,
                $monthlyConsumption
            );

            $savings = $this->simulateSolarSavings($invoices, $solarData, $averageTariff);
            $estimatedAnnualSavings = $savings;
            $estimatedMonthlySavings = $savings / 12;
        }

        return [
            'monthlyConsumption' => $monthlyConsumption,
            'invoiceData' => $invoiceData,
            'invoiceCount' => $invoiceCount,
            'averageTariff' => $averageTariff,
            'invoices' => $invoices,
            'climateProfile' => $climateProfile,
            'solarData' => $solarData,
            'estimatedMonthlySavings' => $estimatedMonthlySavings,
            'estimatedAnnualSavings' => $estimatedAnnualSavings,
        ];
    }

    /**
     * Calculate consumption and tariff averages from invoices
     */
    private function calculateAverages($invoices): array
    {
        $monthlyConsumption = null;
        $maxMonthlyConsumption = 0;
        $averageTariff = 150; // Default fallback

        if ($invoices->count() > 0) {
            $totalConsumption = 0;
            $totalDays = 0;
            $totalCost = 0;

            $totalRepConsumption = 0;
            $totalRepDays = 0;

            foreach ($invoices as $invoice) {
                $consumption = $invoice->total_energy_consumed_kwh ?? $invoice->equipmentUsages->sum('consumption_kwh');
                $cost = $invoice->total_amount;

                $startDate = \Carbon\Carbon::parse($invoice->start_date);
                $endDate = \Carbon\Carbon::parse($invoice->end_date);
                $days = $startDate->diffInDays($endDate);

                if ($days > 0 && $consumption > 0) {
                    $totalConsumption += $consumption;
                    $totalDays += $days;
                    $totalCost += $cost;

                    if ($invoice->is_representative) {
                        $totalRepConsumption += $consumption;
                        $totalRepDays += $days;

                        $monthlyNormalized = ($consumption / $days) * 30;
                        if ($monthlyNormalized > $maxMonthlyConsumption) {
                            $maxMonthlyConsumption = $monthlyNormalized;
                        }
                    }
                }
            }

            if ($totalRepDays > 0) {
                $monthlyConsumption = ($totalRepConsumption / $totalRepDays) * 30;
            } elseif ($totalDays > 0) {
                $monthlyConsumption = ($totalConsumption / $totalDays) * 30;
            }

            if ($totalConsumption > 0) {
                $averageTariff = $totalCost / $totalConsumption;
            }
        }

        return [
            'monthlyConsumption' => $monthlyConsumption,
            'maxMonthlyConsumption' => $maxMonthlyConsumption,
            'averageTariff' => $averageTariff,
        ];
    }

    /**
     * Simulate solar savings across historical invoices
     */
    private function simulateSolarSavings($invoices, $solarData, $averageTariff): float
    {
        $totalSimulatedSavings = 0;
        $totalDaysAnalyzed = 0;
        $dailyGeneration = $solarData['monthly_generation_kwh'] / 30;

        foreach ($invoices as $invoice) {
            $consumption = $invoice->total_energy_consumed_kwh ?? $invoice->equipmentUsages->sum('consumption_kwh');
            $startDate = \Carbon\Carbon::parse($invoice->start_date);
            $endDate = \Carbon\Carbon::parse($invoice->end_date);
            $days = $startDate->diffInDays($endDate);

            if ($days > 0) {
                $dailyConsumption = $consumption / $days;
                $dailySavingKwh = min($dailyGeneration, $dailyConsumption);
                $periodSavingKwh = $dailySavingKwh * $days;
                $periodSavingMoney = $periodSavingKwh * $averageTariff;

                $totalSimulatedSavings += $periodSavingMoney;
                $totalDaysAnalyzed += $days;
            }
        }

        if ($totalDaysAnalyzed > 0) {
            return ($totalSimulatedSavings / $totalDaysAnalyzed) * 365;
        }

        return $solarData['monthly_generation_kwh'] * $averageTariff * 12;
    }
}
