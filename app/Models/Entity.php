<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Entity extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::created(function ($entity) {
            if ($entity->type === 'comercio') {
                $entity->rooms()->createMany([
                    ['name' => 'Cocina', 'description' => 'Área de preparación industrial'],
                    ['name' => 'Salón', 'description' => 'Atención al público'],
                    ['name' => 'Depósito / Cámara', 'description' => 'Almacenamiento y refrigeración'],
                    ['name' => 'Baños / Servicios', 'description' => 'Áreas comunes'],
                ]);
            } else {
                $entity->rooms()->createMany([
                    ['name' => 'Portables'],
                    ['name' => 'Eventos / Tareas Extras'],
                ]);
            }
        });
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, Contract::class, 'entity_id', 'contract_id');
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
    protected $fillable = [
        'name',
        'type',
        'usage_type',
        'address_street',
        'address_postal_code',
        'locality_id',
        'description',
        'square_meters',
        'people_count',
        'construction_year',
        'has_gas',
        'has_solar',
        'has_business_activity',
        'business_type',
        'opens_at',
        'closes_at',
        'operating_days',
        'thermal_profile',
        'comercio_type',
        'staff_count',
        'visitors_count',
        'service_turns',
    ];

    protected $casts = [
        'thermal_profile' => 'array',
        'has_gas' => 'boolean',
        'has_solar' => 'boolean',
        'has_business_activity' => 'boolean',
        'construction_year' => 'integer',
        'people_count' => 'integer',
        'square_meters' => 'float',
        'operating_days' => 'array',
        'staff_count' => 'integer',
        'visitors_count' => 'integer',
        'service_turns' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'entity_user')
            ->withPivot('plan_id', 'subscribed_at', 'expires_at')
            ->withTimestamps();
    }

    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }


    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Recalcula el score y la etiqueta energética basada en los datos técnicos.
     */
    public function updateThermalLabel()
    {
        $service = app(\App\Services\ThermalProfileService::class);
        $result = $service->calculate($this->thermal_profile ?? []);
        
        $currentProfile = $this->thermal_profile ?? [];
        $newProfile = array_merge($currentProfile, $result);
        
        $this->thermal_profile = $newProfile;
        $this->save();
        
        return $this;
    }
}
