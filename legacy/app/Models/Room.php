<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;
    public function equipment()
    {
        return $this->hasMany(\App\Models\Equipment::class, 'room_id');
    }
    protected $fillable = [
        'entity_id',
        'name',
        'description',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Determina si la habitación es un área protegida del sistema.
     */
    public function isSystemRoom(): bool
    {
        return in_array($this->name, ['Portátiles', 'Temporales']);
    }

    /**
     * Obtiene la descripción predeterminada para áreas del sistema.
     */
    public function getSystemDescription(): ?string
    {
        return match($this->name) {
            'Portátiles' => 'Equipos móviles que se trasladan entre diferentes ambientes.',
            'Temporales' => 'Gastos y consumos ocasionales, eventos, reparaciones o trabajos temporales.',
            default => $this->description
        };
    }
}
