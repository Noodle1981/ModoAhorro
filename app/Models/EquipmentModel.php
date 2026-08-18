<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'type_id',
        'brand',
        'model',
        'nominal_power_w',
        'is_inverter',
        'energy_label',
        'capacity',
        'capacity_unit',
        'extra_attributes',
        'is_verified',
        'occurrences_count',
        'source',
        'notes',
    ];

    protected $casts = [
        'is_inverter' => 'boolean',
        'is_verified' => 'boolean',
        'nominal_power_w' => 'integer',
        'occurrences_count' => 'integer',
        'capacity' => 'float',
        'extra_attributes' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function type()
    {
        return $this->belongsTo(EquipmentType::class, 'type_id');
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'model_id');
    }
}
