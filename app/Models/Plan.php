<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'features',
        'price',
        'max_entities',
    ];

    public function entityUsers()
    {
        return $this->hasManyThrough(Entity::class, 'entity_user', 'plan_id', 'id', 'id', 'entity_id');
    }
}
