<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentHistory extends Model
{
    protected $table = 'equipment_history';
    protected $fillable = [
        'old_equipment_id', 'new_equipment_id', 'replacement_date', 'invoice_id', 'action'
    ];

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
