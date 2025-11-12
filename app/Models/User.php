<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // si usas sanctum

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'surname1',
        'surname2',
        'email',
        'phone',
        'password',
        'role',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Laravel 10+ soporta el cast 'hashed' para password
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Ejemplo de relaciones que te pueden servir
    public function businesses()
    {
        return $this->hasMany(Business::class, 'user_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'fk_user_client');
    }
}
