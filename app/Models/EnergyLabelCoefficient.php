<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyLabelCoefficient extends Model
{
    protected $fillable = ['category_id', 'label', 'coefficient'];

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }
}
