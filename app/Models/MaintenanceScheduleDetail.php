<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceScheduleDetail extends Model
{
    use HasFactory;

    protected $table = 'maintenance_schedule_details';

    protected $fillable = [
        'm_schedule_id', 
        'date', 
        'observation', 
        'image', 
        'status'
    ];
}