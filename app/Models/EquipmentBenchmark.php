<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentBenchmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'equipment_type_id',
        'name',
        'energy_label',
        'watts',
        'efficiency_gain_factor',
        'efficiency_ratio',
        'average_market_price',
        'meli_search_term',
        'affiliate_link',
        'recommendation_text',
    ];

    protected $casts = [
        'watts' => 'integer',
        'efficiency_gain_factor' => 'float',
        'efficiency_ratio' => 'float',
        'average_market_price' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }
}
