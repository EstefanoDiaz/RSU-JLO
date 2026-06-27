<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    use HasFactory;

    protected $table = 'maintenance_schedules';

    protected $fillable = ['maintenance_id', 'vehicle_id', 'user_id', 'type', 'day_of_week', 'start_time', 'end_time'];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    
    public function user() { return $this->belongsTo(User::class); }

    public function details() { return $this->hasMany(MaintenanceScheduleDetail::class, 'm_schedule_id'); }
}