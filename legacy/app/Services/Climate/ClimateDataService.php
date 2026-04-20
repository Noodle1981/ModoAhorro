<?php

namespace App\Services\Climate;

use App\Models\Locality;
use App\Models\Invoice;
use App\Services\ClimateService;
use Carbon\Carbon;

/**
 * Legacy Proxy Service.
 * Todo el procesamiento climático se ha movido a App\Services\ClimateService.
 * Este servicio se mantiene por retrocompatibilidad pero delega todas sus funciones al nuevo motor.
 */
class ClimateDataService
{
    protected $newService;

    public function __construct(ClimateService $newService)
    {
        $this->newService = $newService;
    }

    /**
     * @deprecated Use ClimateService::fetchHistoricalData
     */
    public function fetchHistoricalData(Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        return $this->newService->fetchHistoricalData($locality, $startDate, $endDate);
    }

    /**
     * @deprecated Use ClimateService::saveWeatherData
     */
    public function saveWeatherData(Locality $locality, array $weatherData): int
    {
        return $this->newService->saveWeatherData($locality, $weatherData);
    }

    /**
     * @deprecated Use ClimateService::loadDataForInvoice
     */
    public function loadDataForInvoice(Invoice $invoice): array
    {
        return $this->newService->loadDataForInvoice($invoice);
    }

    /**
     * @deprecated Use ClimateService::getClimateStats
     */
    public function getClimateStats(float $latitude, float $longitude, Carbon $startDate, Carbon $endDate, float $hotThreshold = 28.0, float $coldThreshold = 15.0): array
    {
        // Nota: El nuevo servicio usa 24/18 por defecto, pero respetamos los umbrales pasados aquí
        return $this->newService->getClimateStats($latitude, $longitude, $startDate, $endDate, $hotThreshold, $coldThreshold);
    }

    /**
     * @deprecated Use ClimateService::getLocalityClimateProfile
     */
    public function getLocalityClimateProfile(Locality $locality): array
    {
        return $this->newService->getLocalityClimateProfile($locality);
    }

    /**
     * @deprecated Use ClimateService::getOrFetchData
     */
    public function getOrFetchData(Locality $locality, string $startDateStr, string $endDateStr): array
    {
        return $this->newService->getOrFetchData($locality, $startDateStr, $endDateStr);
    }
}
