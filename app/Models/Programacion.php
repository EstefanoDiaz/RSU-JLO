<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programacion extends Model
{
    use HasFactory;

    protected $table = 'programaciones';

    protected $fillable = [
        'group_id',
        'date',
        'zone_id',
        'schedule_id',
        'vehicle_id',
        'conductor_id',
        'ayudante1_id',
        'ayudante2_id',
        'status',
        'observations',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function group()
    {
        return $this->belongsTo(PersonalGroup::class, 'group_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function conductor()
    {
        return $this->belongsTo(User::class, 'conductor_id');
    }

    public function ayudante1()
    {
        return $this->belongsTo(User::class, 'ayudante1_id');
    }

    public function ayudante2()
    {
        return $this->belongsTo(User::class, 'ayudante2_id');
    }
}
