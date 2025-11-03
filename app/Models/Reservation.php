<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'fk_user_client',
        'fk_business_service',
        'time_start',
        'estimated_time_end',
        'status',
        'aforo',
        'token',
        'token_expires_at',
    ];

    // Relaciones
    public function client()
    {
        return $this->belongsTo(User::class, 'fk_user_client');
    }

    public function service()
    {
        return $this->belongsTo(BusinessService::class, 'fk_business_service');
    }

    public function business()
    {
        return $this->hasOneThrough(
            Business::class,
            BusinessService::class,
            'id', // Foreign key on business_services
            'id', // Foreign key on businesses
            'fk_business_service', // Local key on reservations
            'business_id' // Local key on business_services
        );
    }
}
