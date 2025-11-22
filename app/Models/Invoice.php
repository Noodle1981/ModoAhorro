<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'contract_id',
        'invoice_number',
        'invoice_date',
        'issue_date',
        'start_date',
        'end_date',
        'consumption_kwh',
        'energy_cost',
        'taxes_cost',
        'energy_consumed_p1_kwh',
        'energy_consumed_p2_kwh',
        'energy_consumed_p3_kwh',
        'total_energy_consumed_kwh',
        'cost_for_energy',
        'cost_for_power',
        'taxes',
        'other_charges',
        'total_amount',
        'total_energy_injected_kwh',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function usageAdjustment()
    {
        return $this->hasOne(UsageAdjustment::class);
    }

    public function equipmentUsages()
    {
        return $this->hasMany(\App\Models\EquipmentUsage::class, 'invoice_id');
    }
}
