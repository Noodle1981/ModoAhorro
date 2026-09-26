<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mining Operations & High-Altitude Energy Parameters
    |--------------------------------------------------------------------------
    |
    | Constantes y factores físicos para campamentos mineros de alta montaña
    | (Puna andina / Cordillera sanjuanina > 3.500 msnm).
    |
    */

    'diesel_usd_per_liter' => env('MINING_DIESEL_USD_PER_LITER', 1.35),
    'diesel_liters_per_kwh' => env('MINING_DIESEL_LITERS_PER_KWH', 0.28),
    'co2_kg_per_kwh' => env('MINING_CO2_KG_PER_KWH', 0.27),

    'peak_sun_hours_andean' => env('MINING_PEAK_SUN_HOURS_ANDEAN', 6.5),
    'andean_system_efficiency' => env('MINING_ANDEAN_SYSTEM_EFFICIENCY', 0.82),

    'altitude_extreme_cold_threshold' => env('MINING_COLD_TEMP_THRESHOLD', 5.0), // °C

    'default_shift_schedule' => [
        'faena' => ['07:00', '19:00'],
        'descanso' => ['19:00', '07:00'],
    ],

    'thresholds' => [
        'ok_max_delta_pct' => 5.0,
        'warn_max_delta_pct' => 20.0,
        'critical_min_delta_pct' => 20.0,
        'penalty_consecutive_periods' => 3,
    ],
];
