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
        'default_standby_power_w',
        'is_shiftable',
        'process_type',
        'load_factor',
        'efficiency',
        'intensity',
    ];

    public function isClimate(): bool
    {
        return $this->category && $this->category->name === 'Climatización';
    }

    public function isBase(): bool
    {
        // Si es 24hs y no es clima, es base (Tanque 1)
        return !$this->isClimate() && ($this->default_avg_daily_use_hours == 24);
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function maintenanceTasks()
    {
        return $this->hasMany(MaintenanceTask::class);
    }
}
