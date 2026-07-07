<?php
// ── app/Models/Mantenimiento.php ──────────────────────────────
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $table = 'mantenimientos';
    protected $fillable = ['nombre', 'fecha_inicio', 'fecha_fin'];
    protected $casts = ['fecha_inicio' => 'date', 'fecha_fin' => 'date'];

    public function horarios()
    {
        return $this->hasMany(MantenimientoHorario::class, 'mantenimiento_id');
    }
}