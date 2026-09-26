<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'invoice_number',
        'tariff',
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
        'bimonthly_consumption_kwh',
        'installment_number',
        'total_installments',
        'is_representative',
        'anomaly_reason',
        'usage_locked',
        'recommended_kwh',
        'source_type',
        'shift_code',
        'demand_kw_peak',
        'generator_hours',
        'occupancy_count',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'issue_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_representative' => 'boolean',
            'usage_locked' => 'boolean',
            'demand_kw_peak' => 'float',
            'generator_hours' => 'float',
            'occupancy_count' => 'integer',
        ];
    }

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
        return $this->hasMany(EquipmentUsage::class, 'invoice_id');
    }

    protected function daysInPeriod(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->start_date && $this->end_date) {
                    return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1; // Inclusive
                }

                return 30; // Fallback
            }
        );
    }
}
