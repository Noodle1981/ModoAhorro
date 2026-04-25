<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentBenchmark extends Model
{
    protected $fillable = ['category_id', 'name', 'energy_label', 'watts', 'efficiency_ratio', 'recommendation_text'];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }
}
