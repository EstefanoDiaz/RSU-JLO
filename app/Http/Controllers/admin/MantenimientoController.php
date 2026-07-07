<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Mantenimiento;
use App\Models\MantenimientoHorario;
use App\Models\MantenimientoDetalle;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MantenimientoController extends Controller
{
    const DIA_MAP = [
        'Lunes'     => Carbon::MONDAY,
        'Martes'    => Carbon::TUESDAY,
        'Miércoles' => Carbon::WEDNESDAY,
        'Jueves'    => Carbon::THURSDAY,
        'Viernes'   => Carbon::FRIDAY,
        'Sábado'    => Carbon::SATURDAY,
        'Domingo'   => Carbon::SUNDAY,
    ];

    // ── INDEX ──────────────────────────────────────────────────
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Mantenimiento::withCount('horarios')->select('mantenimientos.*');

            return DataTables::of($query->orderByDesc('fecha_inicio'))
                ->addColumn('fecha_inicio_fmt', fn($m) => $m->fecha_inicio->format('d/m/Y'))
                ->addColumn('fecha_fin_fmt',    fn($m) => $m->fecha_fin->format('d/m/Y'))
                ->addColumn('actions', function ($m) {
                    $btnHorario = '<button class="btn btn-sm btn-info btn-horarios mr-1" data-id="'.$m->id.'" data-nombre="'.e($m->nombre).'" title="Horarios">'
                                . '<i class="fas fa-clock text-white"></i></button>';
                    $btnEdit    = '<button class="btn btn-sm btn-warning btn-editar-mant mr-1" data-id="'.$m->id.'" title="Editar">'
                                . '<i class="fas fa-pen text-dark"></i></button>';
                    $btnDel     = '<button class="btn btn-sm btn-danger btn-eliminar-mant" data-id="'.$m->id.'" data-horarios="'.$m->horarios_count.'" title="Eliminar">'
                                . '<i class="fas fa-trash-alt text-white"></i></button>';
                    return $btnHorario . $btnEdit . $btnDel;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.mantenimientos.index');
    }

    // ── STORE mantenimiento ────────────────────────────────────
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre'       => 'required|string|max:150',
                'fecha_inicio' => 'required|date',
                'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            ]);

            // Validar solapamiento de fechas con otros mantenimientos
            $solapado = Mantenimiento::where(function ($q) use ($request) {
                $q->where('fecha_inicio', '<=', $request->fecha_fin)
                  ->where('fecha_fin', '>=', $request->fecha_inicio);
            })->exists();

            if ($solapado) {
                return response()->json(['message' => 'Las fechas se solapan con otro mantenimiento existente.'], 422);
            }

            Mantenimiento::create($request->only('nombre', 'fecha_inicio', 'fecha_fin'));

            return response()->json(['message' => 'Mantenimiento registrado correctamente.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    // ── EDIT mantenimiento (devuelve JSON para poblar el form) ─
    public function edit($id)
    {
        $m = Mantenimiento::findOrFail($id);
        return response()->json([
            'id'           => $m->id,
            'nombre'       => $m->nombre,
            'fecha_inicio' => $m->fecha_inicio->format('Y-m-d'),
            'fecha_fin'    => $m->fecha_fin->format('Y-m-d'),
        ]);
    }

    // ── UPDATE mantenimiento ───────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $m = Mantenimiento::findOrFail($id);
            $request->validate([
                'nombre'       => 'required|string|max:150',
                'fecha_inicio' => 'required|date',
                'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            ]);

            $solapado = Mantenimiento::where('id', '!=', $id)
                ->where('fecha_inicio', '<=', $request->fecha_fin)
                ->where('fecha_fin', '>=', $request->fecha_inicio)
                ->exists();

            if ($solapado) {
                return response()->json(['message' => 'Las fechas se solapan con otro mantenimiento existente.'], 422);
            }

            $m->update($request->only('nombre', 'fecha_inicio', 'fecha_fin'));
            return response()->json(['message' => 'Mantenimiento actualizado correctamente.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    // ── DESTROY mantenimiento ──────────────────────────────────
    public function destroy($id)
    {
        try {
            $m = Mantenimiento::withCount('horarios')->findOrFail($id);
            if ($m->horarios_count > 0) {
                return response()->json(['message' => 'No se puede eliminar: el mantenimiento tiene horarios registrados.'], 422);
            }
            $m->delete();
            return response()->json(['message' => 'Mantenimiento eliminado correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // HORARIOS
    // ──────────────────────────────────────────────────────────

    // ── Listar horarios de un mantenimiento ───────────────────
    public function horarios($mantenimientoId)
{
    $m = Mantenimiento::findOrFail($mantenimientoId);

    $horarios = MantenimientoHorario::with(['vehicle', 'responsable'])
        ->where('mantenimiento_id', $mantenimientoId)
        ->orderBy('dia_semana')
        ->orderBy('hora_inicio')
        ->get()
        ->map(fn($h) => [
            'id'             => $h->id,

  
            'vehicle_id'     => $h->vehicle_id,
            'responsable_id' => $h->responsable_id,


            'dia_semana'     => $h->dia_semana,
            'vehicle'        => optional($h->vehicle)->name . ' — ' . optional($h->vehicle)->code,
            'responsable'    => optional($h->responsable)->name,
            'tipo'           => $h->tipo,
            'hora_inicio'    => $h->hora_inicio,
            'hora_fin'       => $h->hora_fin,
        ]);

    return response()->json([
        'mantenimiento' => [
            'id'     => $m->id,
            'nombre' => $m->nombre,
        ],
        'horarios' => $horarios,
    ]);
}

    // ── STORE horario ─────────────────────────────────────────
    public function storeHorario(Request $request, $mantenimientoId)
    {
        try {
            $m = Mantenimiento::findOrFail($mantenimientoId);

            $request->validate([
                'vehicle_id'     => 'required|exists:vehicles,id',
                'responsable_id' => 'required|exists:users,id',
                'tipo'           => 'required|in:Preventivo,Limpieza,Reparación',
                'dia_semana'     => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
                'hora_inicio'    => 'required|date_format:H:i',
                'hora_fin'       => 'required|date_format:H:i|after:hora_inicio',
            ], [
                'vehicle_id.required'      => 'El vehículo es obligatorio.',
                'vehicle_id.exists'        => 'El vehículo seleccionado no es válido.',

                'responsable_id.required'  => 'El responsable es obligatorio.',
                'responsable_id.exists'    => 'El responsable seleccionado no es válido.',

                'tipo.required'            => 'El tipo es obligatorio.',
                'tipo.in'                  => 'El tipo debe ser Preventivo, Limpieza o Reparación.',

                'dia_semana.required'      => 'El día de la semana es obligatorio.',
                'dia_semana.in'            => 'El día seleccionado no es válido.',

                'hora_inicio.required'     => 'La hora de inicio es obligatoria.',
                'hora_inicio.date_format'  => 'El formato de la hora de inicio no es válido.',

                'hora_fin.required'        => 'La hora de fin es obligatoria.',
                'hora_fin.date_format'     => 'El formato de la hora de fin no es válido.',
                'hora_fin.after'           => 'La hora de fin debe ser posterior a la hora de inicio.',
            ]);

            // Validar solapamiento de vehículo
            $solapadoVehiculo = MantenimientoHorario::where('mantenimiento_id', $mantenimientoId)
                ->where('vehicle_id', $request->vehicle_id)
                ->where('dia_semana', $request->dia_semana)
                ->where('hora_inicio', '<', $request->hora_fin)
                ->where('hora_fin', '>', $request->hora_inicio)
                ->exists();

            if ($solapadoVehiculo) {
                return response()->json([
                    'message' => 'El vehículo ya tiene un horario de mantenimiento en ese día y rango horario.',
                ], 422);
            }

            // Validar solapamiento de responsable
            $solapadoResponsable = MantenimientoHorario::where('mantenimiento_id', $mantenimientoId)
                ->where('responsable_id', $request->responsable_id)
                ->where('dia_semana', $request->dia_semana)
                ->where('hora_inicio', '<', $request->hora_fin)
                ->where('hora_fin', '>', $request->hora_inicio)
                ->exists();

            if ($solapadoResponsable) {
                return response()->json([
                    'message' => 'El responsable ya tiene un horario asignado en ese día y rango horario.',
                ], 422);
            }

            DB::transaction(function () use ($request, $m, $mantenimientoId) {

                $horario = MantenimientoHorario::create([
                    'mantenimiento_id' => $mantenimientoId,
                    'vehicle_id'       => $request->vehicle_id,
                    'responsable_id'   => $request->responsable_id,
                    'tipo'             => $request->tipo,
                    'dia_semana'       => $request->dia_semana,
                    'hora_inicio'      => $request->hora_inicio,
                    'hora_fin'         => $request->hora_fin,
                ]);

                $this->generarDetalles($horario, $m);
            });

            return response()->json([
                'message' => 'Horario registrado y días generados correctamente.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors())))
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── UPDATE horario ────────────────────────────────────────
    public function updateHorario(Request $request, $horarioId)
    {
        try {

            $horario = MantenimientoHorario::with('mantenimiento')->findOrFail($horarioId);
            $m = $horario->mantenimiento;

            // Normalizar horas (acepta HH:MM o HH:MM:SS)
            $request->merge([
                'hora_inicio' => substr((string)$request->hora_inicio, 0, 5),
                'hora_fin'    => substr((string)$request->hora_fin, 0, 5),
            ]);

            $request->validate([
                'vehicle_id'     => 'required|exists:vehicles,id',
                'responsable_id' => 'required|exists:users,id',
                'tipo'           => 'required|in:Preventivo,Limpieza,Reparación',
                'dia_semana'     => 'required|in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo',
                'hora_inicio'    => 'required|date_format:H:i',
                'hora_fin'       => 'required|date_format:H:i|after:hora_inicio',
            ], [
                'vehicle_id.required'      => 'El vehículo es obligatorio.',
                'vehicle_id.exists'        => 'El vehículo seleccionado no es válido.',

                'responsable_id.required'  => 'El responsable es obligatorio.',
                'responsable_id.exists'    => 'El responsable seleccionado no es válido.',

                'tipo.required'            => 'El tipo es obligatorio.',
                'tipo.in'                  => 'El tipo debe ser Preventivo, Limpieza o Reparación.',

                'dia_semana.required'      => 'El día de la semana es obligatorio.',
                'dia_semana.in'            => 'El día seleccionado no es válido.',

                'hora_inicio.required'     => 'La hora de inicio es obligatoria.',
                'hora_inicio.date_format'  => 'El formato de la hora de inicio no es válido.',

                'hora_fin.required'        => 'La hora de fin es obligatoria.',
                'hora_fin.date_format'     => 'El formato de la hora de fin no es válido.',
                'hora_fin.after'           => 'La hora de fin debe ser posterior a la hora de inicio.',
            ]);

            // Validar solapamiento de vehículo
            $solapadoVehiculo = MantenimientoHorario::where('mantenimiento_id', $m->id)
                ->where('id', '!=', $horarioId)
                ->where('vehicle_id', $request->vehicle_id)
                ->where('dia_semana', $request->dia_semana)
                ->where('hora_inicio', '<', $request->hora_fin)
                ->where('hora_fin', '>', $request->hora_inicio)
                ->exists();

            if ($solapadoVehiculo) {
                return response()->json([
                    'message' => 'El vehículo ya tiene un horario de mantenimiento en ese día y rango horario.',
                ], 422);
            }

            // Validar solapamiento de responsable
            $solapadoResponsable = MantenimientoHorario::where('mantenimiento_id', $m->id)
                ->where('id', '!=', $horarioId)
                ->where('responsable_id', $request->responsable_id)
                ->where('dia_semana', $request->dia_semana)
                ->where('hora_inicio', '<', $request->hora_fin)
                ->where('hora_fin', '>', $request->hora_inicio)
                ->exists();

            if ($solapadoResponsable) {
                return response()->json([
                    'message' => 'El responsable ya tiene un horario asignado en ese día y rango horario.',
                ], 422);
            }

            DB::transaction(function () use ($request, $horario, $m) {

                // Eliminar detalles anteriores
                $horario->detalles()->delete();

                // Actualizar horario
                $horario->update([
                    'vehicle_id'     => $request->vehicle_id,
                    'responsable_id' => $request->responsable_id,
                    'tipo'           => $request->tipo,
                    'dia_semana'     => $request->dia_semana,
                    'hora_inicio'    => $request->hora_inicio,
                    'hora_fin'       => $request->hora_fin,
                ]);

                // Regenerar detalles
                $this->generarDetalles($horario, $m);
            });

            return response()->json([
                'message' => 'Horario actualizado correctamente.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'message' => implode(' ', array_merge(...array_values($e->errors())))
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ── DESTROY horario ───────────────────────────────────────
    public function destroyHorario($horarioId)
    {
        try {
            $horario = MantenimientoHorario::findOrFail($horarioId);

            // Eliminar imágenes de storage antes de borrar detalles
            foreach ($horario->detalles as $detalle) {
                if ($detalle->imagen) {
                    Storage::disk('public')->delete($detalle->imagen);
                }
            }

            $horario->delete(); // cascade elimina detalles
            return response()->json(['message' => 'Horario y días generados eliminados correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // DETALLES
    // ──────────────────────────────────────────────────────────

    // ── Listar detalles de un horario ─────────────────────────
    public function detalles($horarioId)
    {
        $horario  = MantenimientoHorario::with(['vehicle', 'mantenimiento'])->findOrFail($horarioId);
        $detalles = MantenimientoDetalle::where('horario_id', $horarioId)
            ->orderBy('fecha')
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'fecha'       => $d->fecha->format('d/m/Y'),
                'observacion' => $d->observacion,
                'imagen'      => $d->imagen ? asset('storage/'.$d->imagen) : null,
                'realizado'   => $d->realizado,
            ]);

        return response()->json([
            'horario' => [
                'id'          => $horario->id,
                'dia_semana'  => $horario->dia_semana,
                'vehicle'     => optional($horario->vehicle)->name . ' — ' . optional($horario->vehicle)->code,
                'mantenimiento' => optional($horario->mantenimiento)->nombre,
            ],
            'detalles' => $detalles,
        ]);
    }

    // ── UPDATE detalle (observacion, imagen, realizado) ────────
    public function updateDetalle(Request $request, $detalleId)
    {
        try {
            $detalle = MantenimientoDetalle::findOrFail($detalleId);
            $request->validate([
                'observacion' => 'nullable|string|max:500',
                'realizado'   => 'nullable|boolean',
                'imagen'      => 'nullable|image|max:2048',
            ]);

            $data = [
                'observacion' => $request->observacion,
                'realizado'   => $request->boolean('realizado'),
            ];

            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior si existe
                if ($detalle->imagen) {
                    Storage::disk('public')->delete($detalle->imagen);
                }
                $data['imagen'] = $request->file('imagen')->store('mantenimientos', 'public');
            }

            $detalle->update($data);
            return response()->json(['message' => 'Detalle actualizado correctamente.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => implode(' ', array_merge(...array_values($e->errors())))], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────

    /**
     * Genera los registros de detalle para un horario dado,
     * buscando todas las fechas del mantenimiento que caigan en el día de la semana indicado.
     */
    private function generarDetalles(MantenimientoHorario $horario, Mantenimiento $m): void
    {
        $diaNum  = self::DIA_MAP[$horario->dia_semana] ?? null;
        if ($diaNum === null) return;

        $current = Carbon::parse($m->fecha_inicio)->startOfDay();
        $end     = Carbon::parse($m->fecha_fin)->startOfDay();

        while ($current->lte($end)) {
            if ($current->dayOfWeek === $diaNum) {
                MantenimientoDetalle::create([
                    'horario_id' => $horario->id,
                    'fecha'      => $current->toDateString(),
                    'realizado'  => false,
                ]);
            }
            $current->addDay();
        }
    }

    // ── Datos para formulario de horario ──────────────────────
    public function formData()
    {
        $vehicles    = Vehicle::where('status', 'Activo')->orderBy('name')->get(['id', 'name', 'code']);
        $mecanicos   = User::whereHas('usertype', fn($q) => $q->whereIn('name', ['Mecánico', 'Mecanico', 'mecanico']))
                          ->whereHas('contracts', fn($q) => $q->where('active', true))
                          ->orderBy('name')->get(['id', 'name']);

        return response()->json(compact('vehicles', 'mecanicos'));
    }
}