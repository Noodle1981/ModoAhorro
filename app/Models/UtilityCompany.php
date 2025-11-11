<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtilityCompany extends Model
{
    protected $fillable = [
        'name',
        'cuit',
        'address',
        'phone',
        'email',
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
