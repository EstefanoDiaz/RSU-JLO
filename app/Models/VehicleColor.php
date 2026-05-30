<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleColor extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = [
        'name',
        'description',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'color_id');
    }
}
