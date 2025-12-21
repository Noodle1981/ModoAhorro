<?php

namespace App\Services;

use App\Models\Entity;
use App\Services\Climate\ClimateDataService;
use App\Services\Solar\SolarWaterService;

class SolarWaterHeaterService
{
    public function __construct(
        private ClimateDataService $climateService,
        private SolarWaterService $waterService
    ) {
    }

    /**
     * Calculate solar water heater data for an entity
     */
    public function calculateWaterHeaterData(Entity $entity): array
    {
        // Load invoices
        $entity->load(['locality', 'contracts.invoices']);

        // Get climate profile
        $climateProfile = null;
        if ($entity->locality) {
            $climateProfile = $this->climateService->getLocalityClimateProfile($entity->locality);
        }

        // Calculate average tariff
        $invoices = $entity->contracts()
            ->with('invoices')
            ->get()
            ->flatMap(fn($contract) => $contract->invoices);

        $averageTariff = $this->calculateAverageTariff($invoices);

        // Calculate water heater data
        $waterHeaterData = $this->waterService->calculateWaterHeater(
            $entity->people_count,
            $climateProfile,
            $averageTariff
        );

        return [
            'climateProfile' => $climateProfile,
            'waterHeaterData' => $waterHeaterData,
            'averageTariff' => $averageTariff,
        ];
    }

    /**
     * Calculate average tariff from invoices
     */
    private function calculateAverageTariff($invoices): float
    {
        $averageTariff = 150; // Default fallback

        if ($invoices->count() > 0) {
            $totalConsumption = 0;
            $totalCost = 0;

            foreach ($invoices as $invoice) {
                $consumption = $invoice->total_energy_consumed_kwh ?? 0;
                $cost = $invoice->total_amount;

                if ($consumption > 0) {
                    $totalConsumption += $consumption;
                    $totalCost += $cost;
                }
            }

            if ($totalConsumption > 0) {
                $averageTariff = $totalCost / $totalConsumption;
            }
        }

        return $averageTariff;
    }
}
