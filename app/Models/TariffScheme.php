<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TariffScheme extends Model
{
    protected $fillable = ['name', 'provider'];

    public function bands()
    {
        return $this->hasMany(TariffBand::class);
    }
}
