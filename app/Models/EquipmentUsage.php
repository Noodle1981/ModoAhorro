<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EquipmentUsage extends Model
{
    protected $fillable = [
        'invoice_id', 'equipment_id', 'is_standby', 'avg_daily_use_hours', 'use_days_in_period', 'use_days_of_week',
        'usage_frequency', 'usage_count', 'avg_use_duration', 'consumption_kwh', 'climate_adjustment_percent',
        'kwh_reconciled', 'tank_assignment', 'audit_logs'
    ];

    protected $casts = [
        'audit_logs' => 'array',
        'use_days_of_week' => 'array',
        'tank_assignment' => 'integer',
        'kwh_reconciled' => 'decimal:4',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
