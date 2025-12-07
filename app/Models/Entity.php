<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
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
        'address_street',
        'address_postal_code',
        'locality_id',
        'description',
        'square_meters',
        'people_count',
        'thermal_profile',
    ];

    protected $casts = [
        'thermal_profile' => 'array',
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
}
