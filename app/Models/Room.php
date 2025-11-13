<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public function equipment()
    {
        return $this->hasMany(\App\Models\Equipment::class, 'room_id');
    }
    protected $fillable = [
        'entity_id',
        'name',
        'description',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
