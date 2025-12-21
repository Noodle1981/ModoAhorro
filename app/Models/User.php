<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación con entidades a través de la tabla pivote y plan
    public function entities()
    {
        return $this->belongsToMany(Entity::class, 'entity_user')
            ->withPivot('plan_id', 'subscribed_at', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Get the current active plan for the user
     */
    public function currentPlan(): ?\App\Models\Plan
    {
        // Obtener la relación entity_user más reciente que no haya expirado
        $pivot = \DB::table('entity_user')
            ->where('user_id', $this->id)
            ->where(function ($query) {
                $query->where('expires_at', '>', now())
                    ->orWhereNull('expires_at');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if ($pivot && $pivot->plan_id) {
            return \App\Models\Plan::find($pivot->plan_id);
        }

        // Fallback: Plan Gratuito por defecto
        return \App\Models\Plan::where('name', 'Gratuito')->first();
    }
}
