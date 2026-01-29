<?php

namespace App\Services\Climate;

use App\Models\ClimateData;
use App\Models\Locality;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClimateDataService
{
    /**
     * Obtiene datos climáticos históricos para un período y localidad
     * 
     * @param Locality $locality
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function fetchHistoricalData(Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        if (!$locality->latitude || !$locality->longitude) {
            return [
                'success' => false,
                'message' => 'La localidad no tiene coordenadas definidas.',
                'data' => [],
            ];
        }

        try {
            return $this->fetchFromOpenMeteo($locality, $startDate, $endDate);
        } catch (\Exception $e) {
            Log::error("Error obteniendo datos climáticos: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Open-Meteo API (GRATIS, sin API key, excelente para históricos)
     * https://open-meteo.com/en/docs/historical-weather-api
     */
    private function fetchFromOpenMeteo(Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        $url = 'https://archive-api.open-meteo.com/v1/archive';
        
        $response = Http::timeout(30)->get($url, [
            'latitude' => $locality->latitude,
            'longitude' => $locality->longitude,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'daily' => 'temperature_2m_max,temperature_2m_min,temperature_2m_mean,precipitation_sum,wind_speed_10m_max,relative_humidity_2m_mean,cloudcover_mean,sunshine_duration,shortwave_radiation_sum',
            'timezone' => 'auto',
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'message' => 'Error en la respuesta de Open-Meteo: ' . $response->status(),
                'data' => [],
            ];
        }

        $data = $response->json();

        if (!isset($data['daily']['time'])) {
            return [
                'success' => false,
                'message' => 'Respuesta inválida de Open-Meteo',
                'data' => [],
            ];
        }

        $weatherData = [];
        $dailyData = $data['daily'];
        $count = count($dailyData['time']);

        for ($i = 0; $i < $count; $i++) {
            $avgTemp = $dailyData['temperature_2m_mean'][$i] ?? null;
            $maxTemp = $dailyData['temperature_2m_max'][$i] ?? null;
            $minTemp = $dailyData['temperature_2m_min'][$i] ?? null;

            if ($avgTemp === null) {
                continue; // Saltar días sin datos
            }

            // Calcular CDD y HDD (base 18°C)
            $cdd = max(0, $avgTemp - 18);
            $hdd = max(0, 18 - $avgTemp);

            $weatherData[] = [
                'date' => $dailyData['time'][$i],
                'avg_temp_celsius' => round($avgTemp, 1),
                'max_temp_celsius' => round($maxTemp ?? $avgTemp, 1),
                'min_temp_celsius' => round($minTemp ?? $avgTemp, 1),
                'cooling_degree_days' => round($cdd, 2),
                'heating_degree_days' => round($hdd, 2),
                'precipitation_mm' => round($dailyData['precipitation_sum'][$i] ?? 0, 1),
                'wind_speed_kmh' => round($dailyData['wind_speed_10m_max'][$i] ?? 0, 1),
                'humidity_percent' => isset($dailyData['relative_humidity_2m_mean'][$i]) ? round($dailyData['relative_humidity_2m_mean'][$i]) : null,
                'cloudcover_mean' => isset($dailyData['cloudcover_mean'][$i]) ? round($dailyData['cloudcover_mean'][$i]) : null,
                'sunshine_duration' => isset($dailyData['sunshine_duration'][$i]) ? round($dailyData['sunshine_duration'][$i], 2) : null,
                'shortwave_radiation_sum' => isset($dailyData['shortwave_radiation_sum'][$i]) ? round($dailyData['shortwave_radiation_sum'][$i], 2) : null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Datos obtenidos exitosamente desde Open-Meteo',
            'data' => $weatherData,
            'provider' => 'open-meteo',
        ];
    }

    /**
     * Guarda los datos obtenidos en la base de datos
     * 
     * @param Locality $locality
     * @param array $weatherData
     * @return int Cantidad de registros insertados
     */
    public function saveWeatherData(Locality $locality, array $weatherData): int
    {
        $inserted = 0;

        foreach ($weatherData as $dayData) {
            ClimateData::updateOrCreate(
                [
                    'latitude' => $locality->latitude,
                    'longitude' => $locality->longitude,
                    'date' => $dayData['date'],
                ],
                [
                    'temp_avg' => $dayData['avg_temp_celsius'],
                    'temp_max' => $dayData['max_temp_celsius'],
                    'temp_min' => $dayData['min_temp_celsius'],
                    'cooling_degree_days' => $dayData['cooling_degree_days'],
                    'heating_degree_days' => $dayData['heating_degree_days'],
                    'precipitation_mm' => $dayData['precipitation_mm'],
                    'wind_speed_kmh' => $dayData['wind_speed_kmh'],
                    'humidity_percent' => $dayData['humidity_percent'],
                    'cloudcover_mean' => $dayData['cloudcover_mean'],
                    'sunshine_duration' => $dayData['sunshine_duration'],
                    'shortwave_radiation_sum' => $dayData['shortwave_radiation_sum'],
                ]
            );
            
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Carga datos automáticamente para una factura
     */
    private static $cache = [];
    private static $loadedInvoices = [];

    /**
     * Carga datos automáticamente para una factura
     */
    public function loadDataForInvoice(Invoice $invoice): array
    {
        // Evitar múltiples llamadas para la misma factura en la misma solicitud
        if (isset(self::$loadedInvoices[$invoice->id])) {
            return ['success' => true, 'message' => 'Datos ya verificados en esta solicitud', 'cached' => true];
        }

        // Navegar desde Invoice -> Contract -> Entity -> Locality
        $locality = $invoice->contract->entity->locality ?? null;
        
        if (!$locality) {
            return [
                'success' => false,
                'message' => 'La entidad no tiene localidad asignada',
            ];
        }

        $startDate = Carbon::parse($invoice->start_date);
        $endDate = Carbon::parse($invoice->end_date);

        // VERIFICAR SI YA EXISTEN DATOS EN BD ANTES DE LLAMAR A LA API
        $exists = ClimateData::where('latitude', $locality->latitude)
            ->where('longitude', $locality->longitude)
            ->where('date', $startDate->format('Y-m-d'))
            ->exists();

        // Si ya tenemos datos para el día de inicio, asumimos que tenemos el periodo (o la mayoría)
        // Esto evita llamar a la API en cada carga de página
        if ($exists) {
            self::$loadedInvoices[$invoice->id] = true;
            return ['success' => true, 'message' => 'Datos encontrados en base de datos', 'cached' => true];
        }

        $result = $this->fetchHistoricalData($locality, $startDate, $endDate);
        
        if (!$result['success']) {
            return $result;
        }

        $inserted = $this->saveWeatherData($locality, $result['data']);
        self::$loadedInvoices[$invoice->id] = true;

        return [
            'success' => true,
            'message' => "Cargados {$inserted} días de datos climáticos desde {$result['provider']}",
            'inserted' => $inserted,
            'provider' => $result['provider'],
        ];
    }

    /**
     * Calcula estadísticas climáticas para un período usando datos de la BD
     * 
     * @return array ['avg_temp_max', 'hot_days_count', 'cold_days_count', 'avg_temp']
     */
    public function getClimateStats(float $latitude, float $longitude, Carbon $startDate, Carbon $endDate, float $hotThreshold = 28.0, float $coldThreshold = 15.0): array
    {
        $cacheKey = "{$latitude}_{$longitude}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}_{$hotThreshold}_{$coldThreshold}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $data = ClimateData::where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        
        if ($data->isEmpty()) {
            $result = [
                'avg_temp_max' => null,
                'avg_temp_min' => null,
                'avg_temp_avg' => null,
                'hot_days_count' => 0,
                'cold_days_count' => 0,
                'total_days' => 0,
            ];
            self::$cache[$cacheKey] = $result;
            return $result;
        }
        
        $totalTempMax = 0;
        $totalTempMin = 0;
        $totalTempAvg = 0;
        $hotDays = 0;
        $coldDays = 0;

        foreach ($data as $day) {
            $totalTempMax += $day->temp_max;
            $totalTempMin += $day->temp_min;
            $totalTempAvg += $day->temp_avg;

            // Usar temp_avg como umbral de calor
            if ($day->temp_avg >= $hotThreshold) {
                $hotDays++;
            }

            if ($day->temp_min <= $coldThreshold) {
                $coldDays++;
            }
        }
        
        $count = $data->count();

        $result = [
            'avg_temp_max' => round($totalTempMax / $count, 1),
            'avg_temp_min' => round($totalTempMin / $count, 1),
            'avg_temp_avg' => round($totalTempAvg / $count, 1),
            'hot_days_count' => $hotDays,
            'cold_days_count' => $coldDays,
            'total_days' => $count,
            'avg_sunshine_duration' => $data->avg('sunshine_duration'),
            'avg_radiation' => $data->avg('shortwave_radiation_sum'),
        ];

        self::$cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Obtiene el perfil climático histórico de una localidad
     * 
     * @param Locality $locality
     * @return array
     */
    public function getLocalityClimateProfile(Locality $locality): array
    {
        // 1. Verificar si tenemos datos recientes (últimos 365 días)
        $oneYearAgo = Carbon::now()->subYear();
        $yesterday = Carbon::yesterday();
        
        $count = ClimateData::where('latitude', $locality->latitude)
            ->where('longitude', $locality->longitude)
            ->where('date', '>=', $oneYearAgo->format('Y-m-d'))
            ->count();

        // 2. Si tenemos menos de 300 días de datos, intentar obtener históricos
        if ($count < 300) {
            $fetchResult = $this->fetchHistoricalData($locality, $oneYearAgo, $yesterday);
            
            if ($fetchResult['success']) {
                $this->saveWeatherData($locality, $fetchResult['data']);
            }
        }

        $cacheKey = "climate_profile_{$locality->latitude}_{$locality->longitude}";

        // Force refresh cache if we just fetched data
        if ($count < 300 && isset(self::$cache[$cacheKey])) {
            unset(self::$cache[$cacheKey]);
        }

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $data = ClimateData::where('latitude', $locality->latitude)
            ->where('longitude', $locality->longitude)
            ->where('date', '>=', $oneYearAgo->format('Y-m-d'))
            ->get();

        if ($data->isEmpty()) {
            return [];
        }

        $avgTemp = round($data->avg('temp_avg'), 1);
        $hdd = $data->sum('heating_degree_days');

        // Estimación muy simplificada de Zona Bioambiental (IRAM 11603) basada en HDD anuales
        // Esta es una aproximación para mostrar algo útil
        $zone = 'N/A';
        if ($hdd < 700) { // Muy cálido
            $zone = 'I (Muy Cálida)';
        } elseif ($hdd < 1400) { // Cálido
            $zone = 'II (Cálida)';
        } elseif ($hdd < 2400) { // Templado Cálido
            $zone = 'III (Templada)';
        } elseif ($hdd < 3500) { // Templado Frio
            $zone = 'IV (Fría)';
        } else { // Muy Frío
            $zone = 'V (Muy Fría)';
        }

        $result = [
            'avg_temperature' => $avgTemp, // Key expected by view
            'climate_zone' => $zone,       // Key expected by view
            'avg_temp' => $avgTemp,
            'avg_max_temp' => round($data->avg('temp_max'), 1),
            'avg_min_temp' => round($data->avg('temp_min'), 1),
            'avg_cloud_cover' => round($data->avg('cloudcover_mean'), 0),
            'avg_sunshine_duration' => round($data->avg('sunshine_duration') / 3600, 1), // horas
            'avg_radiation' => round($data->avg('shortwave_radiation_sum'), 2), // MJ/m²
            'total_days_analyzed' => $data->count(),
            'data_start_date' => $data->min('date'),
            'data_end_date' => $data->max('date'),
        ];

        self::$cache[$cacheKey] = $result;
        return $result;
    }
}
