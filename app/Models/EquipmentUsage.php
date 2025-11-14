<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EquipmentUsage extends Model
{
    protected $fillable = [
        'invoice_id', 'equipment_id', 'is_standby', 'avg_daily_use_hours', 'use_days_in_period', 'use_days_of_week'
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
