<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ClimateData;
use App\Models\Locality;
use App\Models\Invoice;

class ClimateService
{
    // Temperaturas base de confort humano (Estándar Modo Ahorro v3)
    const BASE_TEMP_COOLING = 24; // Grados a partir de los cuales prendemos el Aire
    const BASE_TEMP_HEATING = 18; // Grados debajo de los cuales prendemos la Calefacción
    
    private static $cache = [];
    private static $loadedInvoices = [];

    /**
     * Calcula los Grados-Día acumulados en un periodo para una localidad.
     * Utiliza los campos existentes en la tabla climate_data pero recalculando 
     * con las bases físicas de v3.
     * 
     * @param float $latitude
     * @param float $longitude
     * @param string $fechaInicio (Y-m-d)
     * @param string $fechaFin (Y-m-d)
     * @return array
     */
    public function getDegreeDays($latitude, $longitude, $fechaInicio, $fechaFin)
    {
        $data = DB::table('climate_data')
            ->where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->whereBetween('date', [$fechaInicio, $fechaFin])
            ->select(
                DB::raw('SUM(CASE WHEN (temp_avg - ' . self::BASE_TEMP_COOLING . ') > 0 THEN (temp_avg - ' . self::BASE_TEMP_COOLING . ') ELSE 0 END) as cdd'), // Cooling Degree Days
                DB::raw('SUM(CASE WHEN (' . self::BASE_TEMP_HEATING . ' - temp_avg) > 0 THEN (' . self::BASE_TEMP_HEATING . ' - temp_avg) ELSE 0 END) as hdd'),  // Heating Degree Days
                DB::raw('COUNT(CASE WHEN temp_avg > ' . self::BASE_TEMP_COOLING . ' THEN 1 END) as hot_day_count'), // Count of hot days
                DB::raw('COUNT(CASE WHEN temp_avg < ' . self::BASE_TEMP_HEATING . ' THEN 1 END) as cold_day_count')  // Count of cold days
            )
            ->first();

        return [
            'cooling_days' => (float) ($data->cdd ?? 0),
            'heating_days' => (float) ($data->hdd ?? 0),
            'hot_day_count' => (int) ($data->hot_day_count ?? 0),
            'cold_day_count' => (int) ($data->cold_day_count ?? 0)
        ];
    }

    /**
     * Calcula CDD para una temperatura promedio diaria específica.
     */
    public function calculateCDD(float $tempAvg): float
    {
        return max(0, $tempAvg - self::BASE_TEMP_COOLING);
    }

    /**
     * Calcula HDD para una temperatura promedio diaria específica.
     */
    public function calculateHDD(float $tempAvg): float
    {
        return max(0, self::BASE_TEMP_HEATING - $tempAvg);
    }

    /**
     * Alias para usar con el modelo Locality directamente
     */
    public function getDegreeDaysForLocality($locality, $fechaInicio, $fechaFin)
    {
        return $this->getDegreeDays(
            $locality->latitude,
            $locality->longitude,
            $fechaInicio,
            $fechaFin
        );
    }
    /**
     * Obtiene datos climáticos históricos para un período y localidad
     */
    public function fetchHistoricalData(\App\Models\Locality $locality, Carbon $startDate, Carbon $endDate): array
    {
        if (!$locality->latitude || !$locality->longitude) {
            return ['success' => false, 'message' => 'La localidad no tiene coordenadas definidas.', 'data' => []];
        }

        try {
            return $this->fetchFromOpenMeteo($locality, $startDate, $endDate);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error obteniendo datos climáticos: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al obtener datos: ' . $e->getMessage(), 'data' => []];
        }
    }

    /**
     * Open-Meteo API (GRATIS)
     */
    private function fetchFromOpenMeteo($locality, $startDate, $endDate): array
    {
        $url = 'https://archive-api.open-meteo.com/v1/archive';
        
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->when(app()->environment('local'), fn($h) => $h->withoutVerifying())
            ->get($url, [
            'latitude' => $locality->latitude,
            'longitude' => $locality->longitude,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'daily' => 'temperature_2m_max,temperature_2m_min,temperature_2m_mean,precipitation_sum,wind_speed_10m_max,relative_humidity_2m_mean,cloudcover_mean,sunshine_duration,shortwave_radiation_sum',
            'timezone' => 'auto',
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'message' => 'Error en la respuesta de Open-Meteo: ' . $response->status(), 'data' => []];
        }

        $data = $response->json();

        if (!isset($data['daily']['time'])) {
            return ['success' => false, 'message' => 'Respuesta inválida de Open-Meteo', 'data' => []];
        }

        $weatherData = [];
        $dailyData = $data['daily'];
        $count = count($dailyData['time']);

        for ($i = 0; $i < $count; $i++) {
            $avgTemp = $dailyData['temperature_2m_mean'][$i] ?? null;
            $maxTemp = $dailyData['temperature_2m_max'][$i] ?? null;
            $minTemp = $dailyData['temperature_2m_min'][$i] ?? null;

            if ($avgTemp === null) continue;

            // Calcular CDD y HDD (base v3: 24°C / 18°C)
            $cdd = $this->calculateCDD($avgTemp);
            $hdd = $this->calculateHDD($avgTemp);

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

        return ['success' => true, 'message' => 'Datos obtenidos exitosamente desde Open-Meteo', 'data' => $weatherData, 'provider' => 'open-meteo'];
    }

    /**
     * Guarda los datos obtenidos en la base de datos
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
    public function loadDataForInvoice(Invoice $invoice): array
    {
        if (isset(self::$loadedInvoices[$invoice->id])) {
            return ['success' => true, 'message' => 'Datos ya verificados en esta solicitud', 'cached' => true];
        }

        $locality = $invoice->contract->entity->locality ?? null;
        
        if (!$locality) {
            return ['success' => false, 'message' => 'La entidad no tiene localidad asignada'];
        }

        $startDate = Carbon::parse($invoice->start_date);
        $endDate = Carbon::parse($invoice->end_date);

        $exists = ClimateData::where('latitude', $locality->latitude)
            ->where('longitude', $locality->longitude)
            ->where('date', $startDate->format('Y-m-d'))
            ->exists();

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
     * Carga datos para un rango de fechas específico y una entidad (vía su localidad)
     */
    public function loadDataForDateRange($entity, $startDate, $endDate): array
    {
        $locality = $entity->locality ?? null;
        
        if (!$locality) {
            return [
                'success' => false, 
                'message' => 'La entidad no tiene localidad asignada',
                'is_fallback' => true
            ];
        }

        // Delegar al motor de obtención de datos (soporta strings o Carbon)
        return $this->getOrFetchData(
            $locality, 
            (string)$startDate, 
            (string)$endDate
        );
    }

    /**
     * Calcula estadísticas climáticas para un período usando datos de la BD
     */
    public function getClimateStats(float $latitude, float $longitude, Carbon $startDate, Carbon $endDate, float $hotThreshold = 24.0, float $coldThreshold = 18.0): array
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
        
        $totalTempMax = $data->sum('temp_max');
        $totalTempMin = $data->sum('temp_min');
        $totalTempAvg = $data->sum('temp_avg');
        $hotDays = $data->where('temp_avg', '>=', $hotThreshold)->count();
        $coldDays = $data->where('temp_min', '<=', $coldThreshold)->count();
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
     */
    public function getLocalityClimateProfile(Locality $locality): array
    {
        $oneYearAgo = Carbon::now()->subYear();
        $yesterday = Carbon::yesterday();
        
        $count = ClimateData::where('latitude', $locality->latitude)
            ->where('longitude', $locality->longitude)
            ->where('date', '>=', $oneYearAgo->format('Y-m-d'))
            ->count();

        if ($count < 200) { // Bajamos el umbral para aceptar perfiles parciales si la API es lenta
            try {
                $fetchResult = $this->fetchHistoricalData($locality, $oneYearAgo, $yesterday);
                if ($fetchResult['success']) {
                    $this->saveWeatherData($locality, $fetchResult['data']);
                }
            } catch (\Exception $e) {
                Log::warning("No se pudo descargar histórica: " . $e->getMessage());
            }
        }

        $data = ClimateData::where('latitude', $locality->latitude)
            ->where('longitude', $locality->longitude)
            ->where('date', '>=', $oneYearAgo->format('Y-m-d'))
            ->get();

        if ($data->isEmpty()) {
            return $this->getRegionalFallbackProfile($locality);
        }

        $avgTemp = round($data->avg('temp_avg'), 1);
        $hdd = $data->sum('heating_degree_days');

        $zone = match(true) {
            $hdd < 700  => 'I (Muy Cálida)',
            $hdd < 1400 => 'II (Cálida)',
            $hdd < 2400 => 'III (Templada)',
            $hdd < 3500 => 'IV (Fría)',
            default     => 'V (Muy Fría)',
        };

        return [
            'avg_temperature' => $avgTemp,
            'climate_zone' => $zone,
            'avg_temp' => $avgTemp,
            'avg_max_temp' => round($data->avg('temp_max'), 1),
            'avg_min_temp' => round($data->avg('temp_min'), 1),
            'avg_cloud_cover' => round($data->avg('cloudcover_mean'), 0),
            'avg_sunshine_duration' => round($data->avg('sunshine_duration') / 3600, 1),
            'avg_radiation' => round($data->avg('shortwave_radiation_sum'), 2),
            'total_days_analyzed' => $data->count(),
            'data_start_date' => $data->min('date'),
            'data_end_date' => $data->max('date'),
            'is_fallback' => false
        ];
    }

    /**
     * Proporciona un perfil climático estimado por región si falla la API
     */
    private function getRegionalFallbackProfile(Locality $locality): array
    {
        $province = $locality->province->name ?? '';
        
        $presets = [
            'San Juan' => [
                'zone' => 'III (Templada)',
                'temp' => 18.2,
                'hdd' => 1150,
                'rad' => 1950,
            ],
            'Mendoza' => [
                'zone' => 'III-IV (Templada/Fría)',
                'temp' => 16.5,
                'hdd' => 1380,
                'rad' => 1820,
            ],
            'San Luis' => [
                'zone' => 'III (Templada)',
                'temp' => 17.1,
                'hdd' => 1210,
                'rad' => 1880,
            ],
        ];

        $p = $presets[$province] ?? [
            'zone' => 'No definida',
            'temp' => 18.0,
            'hdd' => 1000,
            'rad' => 1800,
        ];

        return [
            'avg_temperature' => $p['temp'],
            'climate_zone' => $p['zone'],
            'avg_temp' => $p['temp'],
            'avg_max_temp' => $p['temp'] + 7,
            'avg_min_temp' => $p['temp'] - 7,
            'avg_cloud_cover' => 20,
            'avg_sunshine_duration' => 8.5,
            'avg_radiation' => $p['rad'],
            'total_days_analyzed' => 365,
            'is_fallback' => true
        ];
    }

    /**
     * Obtiene datos climáticos o los descarga si no existen
     */
    public function getOrFetchData(Locality $locality, string $startDateStr, string $endDateStr): array
    {
        $startDate = Carbon::parse($startDateStr);
        $endDate = Carbon::parse($endDateStr);
        $isFallback = false;

        $exists = ClimateData::where('latitude', $locality->latitude)
            ->where('longitude', $locality->longitude)
            ->where('date', $startDate->format('Y-m-d'))
            ->exists();

        if (!$exists) {
            try {
                $result = $this->fetchHistoricalData($locality, $startDate, $endDate);
                if ($result['success']) {
                    $this->saveWeatherData($locality, $result['data']);
                } else {
                    $isFallback = true;
                }
            } catch (\Exception $e) {
                $isFallback = true;
                Log::error("❌ Error fetch clima: " . $e->getMessage());
            }
        }

        if ($isFallback) {
             $stats = $this->getFallbackStats($startDate);
        } else {
            $stats = $this->getClimateStats($locality->latitude, $locality->longitude, $startDate, $endDate);
            if (($stats['total_days'] ?? 0) === 0) {
                $isFallback = true;
                $stats = $this->getFallbackStats($startDate);
            }
        }

        return [
            'cooling_days' => $stats['hot_days_count'] ?? 0,
            'heating_days' => $stats['cold_days_count'] ?? 0,
            'avg_temp'     => $stats['avg_temp_avg'] ?? 20,
            'is_fallback'  => $isFallback
        ];
    }

    /**
     * Genera estadísticas genéricas basadas en el mes (Hemisferio Sur)
     */
    private function getFallbackStats(Carbon $date): array
    {
        $month = $date->month;
        $fallbackMap = [
            1  => ['hot' => 25, 'cold' => 0,  'avg' => 26],
            2  => ['hot' => 20, 'cold' => 0,  'avg' => 24],
            3  => ['hot' => 12, 'cold' => 2,  'avg' => 21],
            4  => ['hot' => 5,  'cold' => 10, 'avg' => 17],
            5  => ['hot' => 0,  'cold' => 20, 'avg' => 13],
            6  => ['hot' => 0,  'cold' => 28, 'avg' => 10],
            7  => ['hot' => 0,  'cold' => 30, 'avg' => 9],
            8  => ['hot' => 0,  'cold' => 25, 'avg' => 12],
            9  => ['hot' => 2,  'cold' => 15, 'avg' => 15],
            10 => ['hot' => 8,  'cold' => 8,  'avg' => 19],
            11 => ['hot' => 15, 'cold' => 3,  'avg' => 22],
            12 => ['hot' => 22, 'cold' => 0,  'avg' => 25],
        ];

        $data = $fallbackMap[$month] ?? ['hot' => 5, 'cold' => 5, 'avg' => 20];

        return [
            'hot_days_count' => $data['hot'],
            'cold_days_count' => $data['cold'],
            'avg_temp_avg' => $data['avg'],
            'total_days' => 30
        ];
    }

    /**
     * Obtiene el clima actual para una localidad (Tiempo Real)
     */
    public function getCurrentWeather(Locality $locality): array
    {
        if ($locality->latitude && $locality->longitude) {
            try {
                $response = Http::timeout(5)
                    ->when(app()->environment('local'), fn($h) => $h->withoutVerifying())
                    ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $locality->latitude,
                    'longitude' => $locality->longitude,
                    'current_weather' => true,
                    'timezone' => 'auto',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return [
                        'success' => true,
                        'temp' => $data['current_weather']['temperature'],
                        'windspeed' => $data['current_weather']['windspeed'],
                        'condition_code' => $data['current_weather']['weathercode'],
                        'is_fallback' => false
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("Fallo API Clima: " . $e->getMessage());
            }
        }

        // Fallback estacional si falla la API
        $stats = $this->getFallbackStats(now());
        return [
            'success' => true,
            'temp' => $stats['avg_temp_avg'],
            'windspeed' => 12,
            'condition_code' => 0,
            'is_fallback' => true,
            'message' => 'Basado en promedio mensual'
        ];
    }
}
