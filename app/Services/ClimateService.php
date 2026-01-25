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
                DB::raw('SUM(GREATEST(0, temp_avg - ' . self::BASE_TEMP_COOLING . ')) as cdd'), // Cooling Degree Days
                DB::raw('SUM(GREATEST(0, ' . self::BASE_TEMP_HEATING . ' - temp_avg)) as hdd')  // Heating Degree Days
            )
            ->first();

        return [
            'cooling_days' => (float) ($data->cdd ?? 0),
            'heating_days' => (float) ($data->hdd ?? 0)
        ];
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
}
