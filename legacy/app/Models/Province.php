<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = [
        'name',
    ];

    public function localities()
    {
        return $this->hasMany(Locality::class);
    }

}
