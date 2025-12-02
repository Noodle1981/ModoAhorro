<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'name', 'category_id', 'type_id', 'nominal_power_w', 'is_standby', 'avg_daily_use_hours', 'use_days_per_week', 'is_active', 'room_id',
        'installed_at', 'removed_at'
    ];

    protected $casts = [
        'is_standby' => 'boolean',
        'is_active' => 'boolean',
        'installed_at' => 'date',
        'removed_at' => 'date',
    ];
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function type()
    {
        return $this->belongsTo(EquipmentType::class, 'type_id');
    }
}
