<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TariffBand extends Model
{
    protected $fillable = [
        'tariff_scheme_id',
        'name',
        'start_time',
        'end_time',
        'price_per_kwh',
        'is_weekend_applicable'
    ];

    public function scheme()
    {
        return $this->belongsTo(TariffScheme::class, 'tariff_scheme_id');
    }
}
