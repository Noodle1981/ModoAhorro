<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class EquipmentType extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id', 'name', 'default_power_watts', 'default_avg_daily_use_hours',
        'default_standby_power_w', 'standby_power', 'is_shiftable', 'process_type',
        'load_factor', 'efficiency', 'intensity', 'is_climatization',
        'default_tank', 'is_thermal_sensitive', 'base_efficiency_ratio', 'thermal_efficiency_penalty',
        'consumption_logic', 'is_inverter_capable',
        'usage_unit', 'determinism_score', 'social_coefficient',
        'min_watts', 'max_watts',
    ];

    protected $casts = [
        'is_climatization' => 'boolean',
        'is_inverter_capable' => 'boolean',
        'is_thermal_sensitive' => 'boolean',
        'is_shiftable' => 'boolean',
        'default_tank' => 'integer',
        'base_efficiency_ratio' => 'float',
        'thermal_efficiency_penalty' => 'float',
        'determinism_score' => 'float',
        'social_coefficient' => 'float',
        'min_watts' => 'integer',
        'max_watts' => 'integer',
    ];

    public function isClimate(): bool
    {
        if ($this->is_climatization) return true;
        
        $climateCategories = ['Climatización'];
        return $this->category && in_array($this->category->name, $climateCategories);
    }

    public function isBase(): bool
    {
        // Si es 24hs y no es clima, es base (Tanque 1)
        if (!$this->isClimate() && $this->default_avg_daily_use_hours == 24) {
            return true;
        }

        // Categorías que son inherentemente Base (Tanque 1)
        $baseCategories = ['Refrigeración', 'Seguridad y Redes'];
        return $this->category && in_array($this->category->name, $baseCategories);
    }

    public function isHighIntensity(): bool
    {
        $highIntensityCategories = ['Agua Caliente (ACS)', 'Cuidado Personal', 'Lavado y Limpieza'];
        return $this->category && in_array($this->category->name, $highIntensityCategories);
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function maintenanceTasks()
    {
        return $this->hasMany(MaintenanceTask::class);
    }
}
