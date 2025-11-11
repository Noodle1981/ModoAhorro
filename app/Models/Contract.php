<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'entity_id',
        'supply_id',
        'utility_company_id',
        'contract_identifier',
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

    public function supply()
    {
        return $this->belongsTo(Supply::class);
    }

    public function utilityCompany()
    {
        return $this->belongsTo(UtilityCompany::class);
    }
}
