<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
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
        'is_three_phase',
        'contracted_power_kw_p1',
        'contracted_power_kw_p2',
        'contracted_power_kw_p3',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_three_phase' => 'boolean',
        'contracted_power_kw_p1' => 'decimal:3',
        'contracted_power_kw_p2' => 'decimal:3',
        'contracted_power_kw_p3' => 'decimal:3',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function utilityCompany()
    {
        return $this->belongsTo(UtilityCompany::class);
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
