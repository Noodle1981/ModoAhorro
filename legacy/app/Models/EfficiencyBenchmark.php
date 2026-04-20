<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EfficiencyBenchmark extends Model
{
    protected $fillable = [
        'equipment_type_id',
        'efficiency_gain_factor',
        'average_market_price',
        'meli_search_term',
        'affiliate_link',
    ];

    protected $casts = [
        'efficiency_gain_factor' => 'float',
        'average_market_price' => 'float',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}
