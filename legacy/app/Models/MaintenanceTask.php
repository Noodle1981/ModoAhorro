<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    protected $fillable = [
        'equipment_type_id',
        'title',
        'frequency_days',
        'season_month',
        'efficiency_impact',
    ];

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }
}
