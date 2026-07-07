<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MantenimientoDetalle extends Model
{
    protected $table = 'mantenimiento_detalles';
    protected $fillable = ['horario_id', 'fecha', 'observacion', 'imagen', 'realizado'];
    protected $casts = ['fecha' => 'date', 'realizado' => 'boolean'];

    public function horario()
    {
        return $this->belongsTo(MantenimientoHorario::class, 'horario_id');
    }
}