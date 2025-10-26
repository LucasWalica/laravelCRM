<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'fk_business',
        'fk_user',
        'title',
        'description',
        'stars',
    ];

    // Relaciones
    public function business()
    {
        return $this->belongsTo(Business::class, 'fk_business');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'fk_user');
    }
}
