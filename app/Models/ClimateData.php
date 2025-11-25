<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClimateData extends Model
{
    protected $fillable = [
        'date',
        'latitude',
        'longitude',
        'temp_max',
        'temp_min',
        'temp_avg',
        'cooling_degree_days',
        'heating_degree_days',
        'precipitation_mm',
        'wind_speed_kmh',
        'humidity_percent',
    ];
}
