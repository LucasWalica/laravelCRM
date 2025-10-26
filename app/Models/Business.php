<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'coordinates',
        'logo',
        'images',
        'description',
        'schedule',
        'address',
        'aforo'
    ];

    protected $casts = [
        'schedule' => 'array',
        'images' => 'array',
    ];

    // relaciones
    public function owner() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function services() {
        return $this->hasMany(BusinessService::class);
    }
}
