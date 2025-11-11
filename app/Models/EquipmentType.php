<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentType extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'default_power_watts',
        'default_avg_daily_use_hours',
    ];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
