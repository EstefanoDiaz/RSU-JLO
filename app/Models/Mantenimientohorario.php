<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MantenimientoHorario extends Model
{
    protected $table = 'mantenimiento_horarios';
    protected $fillable = [
        'mantenimiento_id', 'vehicle_id', 'responsable_id',
        'tipo', 'dia_semana', 'hora_inicio', 'hora_fin',
    ];

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function detalles()
    {
        return $this->hasMany(MantenimientoDetalle::class, 'horario_id');
    }
}