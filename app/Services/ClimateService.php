<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClimateService
{
    // Temperaturas base de confort humano (Estándar Modo Ahorro v3)
    const BASE_TEMP_COOLING = 24; // Grados a partir de los cuales prendemos el Aire
    const BASE_TEMP_HEATING = 18; // Grados debajo de los cuales prendemos la Calefacción

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
                DB::raw('SUM(CASE WHEN (' . self::BASE_TEMP_HEATING . ' - temp_avg) > 0 THEN (' . self::BASE_TEMP_HEATING . ' - temp_avg) ELSE 0 END) as hdd')  // Heating Degree Days
            )
            ->first();

        return [
            'cooling_days' => (float) ($data->cdd ?? 0),
            'heating_days' => (float) ($data->hdd ?? 0)
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
        
        $response = \Illuminate\Support\Facades\Http::timeout(30)->get($url, [
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
}
