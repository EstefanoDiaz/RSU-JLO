<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalGroup extends Model
{
    protected $table = 'personal_groups';

    protected $fillable = [
        'name', 'schedule_id', 'zone_id', 'vehicle_id',
        'conductor_id', 'assistant1_id', 'assistant2_id',
        'work_days', 'status',
    ];

    protected $casts = [
        'work_days' => 'array',
    ];

    public function schedule()   { return $this->belongsTo(Schedule::class); }
    public function zone()       { return $this->belongsTo(Zone::class); }
    public function vehicle()    { return $this->belongsTo(Vehicle::class); }
    public function conductor()  { return $this->belongsTo(User::class, 'conductor_id'); }
    public function assistant1() { return $this->belongsTo(User::class, 'assistant1_id'); }
    public function assistant2() { return $this->belongsTo(User::class, 'assistant2_id'); }
}