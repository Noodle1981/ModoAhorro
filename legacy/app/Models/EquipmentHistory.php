<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EquipmentHistory extends Model
{
    protected $table = 'equipment_history';
    protected $fillable = [
        'equipment_id', 'action', 'reason', 'action_date', 'user_id', 'old_equipment_id', 'new_equipment_id', 'replacement_date', 'invoice_id'
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function oldEquipment()
    {
        return $this->belongsTo(Equipment::class, 'old_equipment_id');
    }

    public function newEquipment()
    {
        return $this->belongsTo(Equipment::class, 'new_equipment_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
