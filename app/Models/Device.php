<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'room_id',
        'equipment_type_id',
        'quantity',
        'custom_name',
        'power_watts_override',
        'avg_daily_use_hours_override',
        'standby_active',
        'standby_power_watts',
        'is_active',
        'deactivation_date',
        'deactivation_reason',
        'replaced_by_device_id',
        'is_backup_for_id',
        'description',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function replacedBy()
    {
        return $this->belongsTo(Device::class, 'replaced_by_device_id');
    }

    public function backupFor()
    {
        return $this->belongsTo(Device::class, 'is_backup_for_id');
    }
}
