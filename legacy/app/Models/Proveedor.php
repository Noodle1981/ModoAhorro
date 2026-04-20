<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    protected $table = 'proveedores';
    protected $fillable = [
        'name',
        'cuit',
        'address',
        'phone',
        'email',
        'province_id',
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
