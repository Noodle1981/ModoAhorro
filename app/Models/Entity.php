<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    protected $fillable = [
        'name',
        'type',
        'address_street',
        'address_postal_code',
        'locality_id',
        'description',
        'square_meters',
        'people_count',
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
