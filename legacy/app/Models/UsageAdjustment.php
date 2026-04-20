<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageAdjustment extends Model
{
    protected $fillable = [
        'invoice_id',
        'adjusted',
        'adjusted_at',
        'notes',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
