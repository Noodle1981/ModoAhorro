<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
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
