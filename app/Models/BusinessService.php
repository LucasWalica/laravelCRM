<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessService extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price',
        'time_estimation',
        'aforo',
    ];

    // Relaciones
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
