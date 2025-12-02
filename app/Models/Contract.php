<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'entity_id',
        'proveedor_id',
        'utility_company_id',
        'supply_number',
        'meter_number',
        'client_number',
        'tariff_type',
        'contract_number',
        'rate_name',
        'contracted_power_kw_p1',
        'contracted_power_kw_p2',
        'contracted_power_kw_p3',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }


    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'contract_id');
    }
}
