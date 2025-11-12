<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meter extends Model
{
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    protected $fillable = [
        'serial_number',
        'company_id',
        'entity_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
