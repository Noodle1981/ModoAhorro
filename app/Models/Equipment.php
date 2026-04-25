<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'brand', 'model', 'serial_number', 'category_id', 'type_id', 'nominal_power_w', 'is_standby', 'avg_daily_use_hours', 'has_defined_pattern', 'usage_frequency', 'use_days_per_week', 'is_active', 'room_id', 'is_validated', 'intensity',
        'installed_at', 'removed_at',
        'acquisition_year', 'energy_label', 'is_inverter', 'capacity', 'capacity_unit', 'extra_attributes'
    ];

    protected $casts = [
        'is_standby' => 'boolean',
        'is_active' => 'boolean',
        'is_validated' => 'boolean',
        'has_defined_pattern' => 'boolean',
        'installed_at' => 'date',
        'removed_at' => 'date',
        'extra_attributes' => 'array',
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
