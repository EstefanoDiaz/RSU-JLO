<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Programacion;
use App\Models\ProgramacionCambio;
use App\Models\User;
use App\Models\Zone;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\Cambios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', Carbon::today()->toDateString());
        $scheduleId = $request->get('schedule_id');

        $zones = Zone::whereRaw('UPPER(status) = ?', ['ACTIVO'])->orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();

        $query = Programacion::with([
            'zone',
            'schedule',
            'vehicle',
            'conductor',
            'ayudantes' => fn($q) => $q->orderByPivot('order'),
            'group',
        ])
            ->where('fecha', $fecha)
            ->whereIn('status', ['Programado', 'Reprogramado']);

        if ($scheduleId) {
            $query->where('schedule_id', $scheduleId);
        }

        $programaciones = $query->get();

        $asistenciasDia = DB::table('attendances')
            ->where('date', $fecha)
            ->where('type', 'Entrada')
            ->where('status', 'Presente')
            ->pluck('user_id')
            ->toArray();

        $programaciones = $programaciones->map(function ($prog) use ($asistenciasDia) {
            $faltantes = [];

            if ($prog->conductor_id && !in_array($prog->conductor_id, $asistenciasDia)) {
                $faltantes[] = [
                    'id' => $prog->conductor_id,
                    'nombre' => optional($prog->conductor)->name ?? '—',
                    'rol' => 'conductor',
                    'slot' => 'conductor',
                ];
            }

            foreach ($prog->ayudantes as $i => $ayudante) {
                if (!in_array($ayudante->id, $asistenciasDia)) {
                    $faltantes[] = [
                        'id' => $ayudante->id,
                        'nombre' => $ayudante->name,
                        'rol' => 'ayudante',
                        'slot' => 'ayudante_' . $i,
                        'order' => $ayudante->pivot->order ?? $i,
                    ];
                }
            }

            $totalPersonal = 1 + $prog->ayudantes->count();
            $presentes = $totalPersonal - count($faltantes);
            $prog->faltantes = $faltantes;
            $prog->presentes = $presentes;
            $prog->completa = count($faltantes) === 0;

            return $prog;
        });

        $totalProgramaciones = $programaciones->count();
        $totalCompletas = $programaciones->where('completa', true)->count();
        $totalIncompletas = $programaciones->where('completa', false)->count();
        $totalPersonalFaltante = $programaciones->sum(fn($p) => count($p->faltantes));

        // Motivos por categoría
        $motivosTurno = Cambios::whereIn('id', [1, 2, 3, 4, 5])->orderBy('id')->get(['id', 'name']);
        $motivosVehiculo = Cambios::whereIn('id', [6, 7, 8, 9, 5])->orderBy('id')->get(['id', 'name']);
        $motivosPersonal = Cambios::whereIn('id', [10, 11, 12, 13, 5])->orderBy('id')->get(['id', 'name']);

        return view('admin.dashboard.index', compact(
            'programaciones',
            'fecha',
            'scheduleId',
            'schedules',
            'zones',
            'totalProgramaciones',
            'totalCompletas',
            'totalIncompletas',
            'totalPersonalFaltante',
            'motivosTurno',
            'motivosVehiculo',
            'motivosPersonal'
        ));
    }

    // ──────────────────────────────────────────────────────────
    // DETALLE — JSON para el modal
    // ──────────────────────────────────────────────────────────
    public function detalle($id)
    {
        $prog = Programacion::with([
            'schedule',
            'vehicle',
            'conductor',
            'ayudantes' => fn($q) => $q->orderByPivot('order'),
        ])->findOrFail($id);

        $fecha = $prog->fecha->toDateString();

        $asistenciasDia = DB::table('attendances')
            ->where('date', $fecha)
            ->where('type', 'Entrada')
            ->where('status', 'Presente')
            ->pluck('user_id')
            ->toArray();

        $personal = [];

        $personal[] = [
            'user_id' => $prog->conductor_id,
            'nombre' => optional($prog->conductor)->name ?? '—',
            'rol' => 'conductor',
            'slot' => 'conductor',
            'presente' => in_array($prog->conductor_id, $asistenciasDia),
            'order' => 0,
        ];

        foreach ($prog->ayudantes as $i => $ay) {
            $personal[] = [
                'user_id' => $ay->id,
                'nombre' => $ay->name,
                'rol' => 'ayudante',
                'slot' => 'ayudante_' . $i,
                'presente' => in_array($ay->id, $asistenciasDia),
                'order' => $ay->pivot->order ?? $i,
            ];
        }

        // Turnos disponibles (todos)
        $turnos = Schedule::orderBy('name')->get(['id', 'name', 'time_start', 'time_end']);

        // Vehículos disponibles ese día (sin programación activa ese día, excluyendo esta)
        $vehiculosOcupados = Programacion::where('fecha', $fecha)
            ->where('id', '!=', $id)
            ->whereIn('status', ['Programado', 'Reprogramado'])
            ->pluck('vehicle_id')
            ->filter()
            ->toArray();

        $vehiculos = Vehicle::where('status', 'Activo')
            ->whereNotIn('id', $vehiculosOcupados)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'occupant_capacity']);

        return response()->json([
            'id' => $prog->id,
            'fecha' => $prog->fecha->format('d/m/Y'),
            'fecha_iso' => $fecha,
            'schedule_id' => $prog->schedule_id,
            'schedule_nombre' => optional($prog->schedule)->name,
            'schedule_info' => optional($prog->schedule)->name . ' (' . optional($prog->schedule)->time_start . ' - ' . optional($prog->schedule)->time_end . ')',
            'vehicle_id' => $prog->vehicle_id,
            'vehicle_info' => optional($prog->vehicle)->code . ' — ' . optional($prog->vehicle)->name,
            'personal' => $personal,
            'turnos' => $turnos,
            'vehiculos' => $vehiculos,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // PERSONAL DISPONIBLE
    // ──────────────────────────────────────────────────────────
    public function personalDisponible(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'rol' => 'required|in:conductor,ayudante',
            'programacion_id' => 'required|exists:programaciones,id',
            'q' => 'nullable|string',
        ]);

        $fecha = $request->fecha;
        $rol = $request->rol;
        $programacionId = $request->programacion_id;
        $q = $request->get('q', '');

        $prog = Programacion::with('ayudantes')->findOrFail($programacionId);
        $excluir = array_filter(array_merge(
            [$prog->conductor_id],
            $prog->ayudantes->pluck('id')->toArray()
        ));

        $ocupadosConductores = Programacion::where('fecha', $fecha)
            ->where('id', '!=', $programacionId)
            ->where('status', '!=', 'Cancelado')
            ->pluck('conductor_id')
            ->toArray();

        $ocupadosAyudantes = DB::table('programacion_ayudante')
            ->join('programaciones', 'programaciones.id', '=', 'programacion_ayudante.programacion_id')
            ->where('programaciones.fecha', $fecha)
            ->where('programaciones.id', '!=', $programacionId)
            ->where('programaciones.status', '!=', 'Cancelado')
            ->pluck('programacion_ayudante.user_id')
            ->toArray();

        $ocupados = array_unique(array_merge($ocupadosConductores, $ocupadosAyudantes));
        $tipoNombre = $rol === 'conductor' ? ['Conductor', 'conductor'] : ['Ayudante', 'ayudante'];

        $usuarios = User::query()
            ->whereHas('usertype', fn($q2) => $q2->whereIn('name', $tipoNombre))
            ->whereHas(
                'contracts',
                fn($q2) => $q2
                    ->where('active', true)
                    ->where('start_date', '<=', $fecha)
                    ->where(fn($q3) => $q3->whereNull('end_date')->orWhere('end_date', '>=', $fecha))
            )
            ->whereDoesntHave(
                'vacations',
                fn($q2) => $q2
                    ->whereRaw('UPPER(status) = ?', ['APROBADA'])
                    ->where('start_date', '<=', $fecha)
                    ->where('end_date', '>=', $fecha)
            )
            ->whereNotIn('id', $excluir)
            ->whereNotIn('id', $ocupados)
            ->when($q, fn($q2) => $q2->where(
                fn($q3) => $q3
                    ->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('dni', 'LIKE', "%{$q}%")
            ))
            ->limit(15)
            ->get(['id', 'name', 'dni']);

        return response()->json($usuarios);
    }

    // ──────────────────────────────────────────────────────────
    // CAMBIAR TURNO
    // ──────────────────────────────────────────────────────────
    public function cambiarTurno(Request $request, $id)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'motivo_id' => 'nullable|exists:cambios,id',
            'motivo_detalle' => 'nullable|string|max:500',
        ]);

        try {
            $prog = Programacion::with('schedule')->findOrFail($id);

            if (!in_array($prog->status, ['Programado', 'Reprogramado'])) {
                return response()->json(['message' => 'No se puede modificar una programación Finalizada o Cancelada.'], 422);
            }

            $nuevoScheduleId = (int) $request->schedule_id;

            if ($prog->schedule_id === $nuevoScheduleId) {
                return response()->json(['message' => 'El turno seleccionado es el mismo que el actual.'], 422);
            }

            $motivoTexto = $this->buildMotivo($request->motivo_id, $request->motivo_detalle, 'Cambio de turno desde dashboard');
            $authId = Auth::id();
            $nuevoSchedule = Schedule::findOrFail($nuevoScheduleId);

            DB::transaction(function () use ($prog, $nuevoSchedule, $authId, $motivoTexto) {
                ProgramacionCambio::create([
                    'programacion_id' => $prog->id,
                    'user_id' => $authId,
                    'campo' => 'turno',
                    'valor_anterior' => optional($prog->schedule)->name . ' (' . optional($prog->schedule)->time_start . ' - ' . optional($prog->schedule)->time_end . ')',
                    'valor_nuevo' => $nuevoSchedule->name . ' (' . $nuevoSchedule->time_start . ' - ' . $nuevoSchedule->time_end . ')',
                    'motivo' => $motivoTexto,
                ]);

                $prog->update([
                    'schedule_id' => $nuevoSchedule->id,
                    'status' => 'Reprogramado',
                ]);
            });

            return response()->json(['message' => 'Turno cambiado correctamente.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // CAMBIAR VEHÍCULO
    // ──────────────────────────────────────────────────────────
    public function cambiarVehiculo(Request $request, $id)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'motivo_id' => 'nullable|exists:cambios,id',
            'motivo_detalle' => 'nullable|string|max:500',
        ]);

        try {
            $prog = Programacion::with('vehicle')->findOrFail($id);

            if (!in_array($prog->status, ['Programado', 'Reprogramado'])) {
                return response()->json(['message' => 'No se puede modificar una programación Finalizada o Cancelada.'], 422);
            }

            $nuevoVehicleId = (int) $request->vehicle_id;

            if ($prog->vehicle_id === $nuevoVehicleId) {
                return response()->json(['message' => 'El vehículo seleccionado es el mismo que el actual.'], 422);
            }

            $motivoTexto = $this->buildMotivo($request->motivo_id, $request->motivo_detalle, 'Cambio de vehículo desde dashboard');
            $authId = Auth::id();
            $nuevoVehicle = Vehicle::findOrFail($nuevoVehicleId);

            DB::transaction(function () use ($prog, $nuevoVehicle, $authId, $motivoTexto) {
                ProgramacionCambio::create([
                    'programacion_id' => $prog->id,
                    'user_id' => $authId,
                    'campo' => 'vehiculo',
                    'valor_anterior' => optional($prog->vehicle)->code . ' — ' . optional($prog->vehicle)->name,
                    'valor_nuevo' => $nuevoVehicle->code . ' — ' . $nuevoVehicle->name,
                    'motivo' => $motivoTexto,
                ]);

                $prog->update([
                    'vehicle_id' => $nuevoVehicle->id,
                    'status' => 'Reprogramado',
                ]);
            });

            return response()->json(['message' => 'Vehículo cambiado correctamente.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // REEMPLAZAR PERSONAL
    // ──────────────────────────────────────────────────────────
    public function reemplazar(Request $request, $id)
    {
        $request->validate([
            'slot' => 'required|string',
            'nuevo_user_id' => 'required|exists:users,id',
            'motivo_id' => 'nullable|exists:cambios,id',
            'motivo_detalle' => 'nullable|string|max:500',
        ]);

        try {
            $prog = Programacion::with('conductor', 'ayudantes')->findOrFail($id);

            if (!in_array($prog->status, ['Programado', 'Reprogramado'])) {
                return response()->json(['message' => 'No se puede modificar una programación Finalizada o Cancelada.'], 422);
            }

            $slot = $request->slot;
            $nuevoUserId = (int) $request->nuevo_user_id;
            $nuevoUser = User::findOrFail($nuevoUserId);
            $authId = Auth::id();
            $motivoTexto = $this->buildMotivo($request->motivo_id, $request->motivo_detalle, 'Reemplazo desde dashboard');

            DB::transaction(function () use ($prog, $slot, $nuevoUser, $authId, $motivoTexto) {
                if ($slot === 'conductor') {
                    ProgramacionCambio::create([
                        'programacion_id' => $prog->id,
                        'user_id' => $authId,
                        'campo' => 'conductor',
                        'valor_anterior' => optional($prog->conductor)->name ?? '—',
                        'valor_nuevo' => $nuevoUser->name,
                        'motivo' => $motivoTexto,
                    ]);

                    $prog->update([
                        'conductor_id' => $nuevoUser->id,
                        'status' => 'Reprogramado',
                    ]);

                } else {
                    preg_match('/ayudante_(\d+)/', $slot, $matches);
                    $slotIndex = isset($matches[1]) ? (int) $matches[1] : 0;

                    $ayudanteActual = $prog->ayudantes
                        ->sortBy('pivot.order')
                        ->values()
                        ->get($slotIndex);

                    $order = $ayudanteActual ? ($ayudanteActual->pivot->order ?? $slotIndex) : $slotIndex;
                    $oldAyudantes = $prog->ayudantes->sortBy('pivot.order')->pluck('name')->values()->toArray();
                    $newAyudantes = $oldAyudantes;
                    $newAyudantes[$slotIndex] = $nuevoUser->name;

                    ProgramacionCambio::create([
                        'programacion_id' => $prog->id,
                        'user_id' => $authId,
                        'campo' => 'ayudantes',
                        'valor_anterior' => implode(', ', $oldAyudantes),
                        'valor_nuevo' => implode(', ', $newAyudantes),
                        'motivo' => $motivoTexto,
                    ]);

                    if ($ayudanteActual) {
                        $prog->ayudantes()->detach($ayudanteActual->id);
                    }
                    $prog->ayudantes()->attach($nuevoUser->id, ['order' => $order]);
                    $prog->update(['status' => 'Reprogramado']);
                }
            });

            return response()->json(['message' => 'Personal reemplazado correctamente.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────
    // HELPER — construir texto de motivo
    // ──────────────────────────────────────────────────────────
    private function buildMotivo(?int $motivoId, ?string $detalle, string $default): string
    {
        $texto = '';
        if ($motivoId) {
            $cambio = Cambios::find($motivoId);
            $texto = optional($cambio)->name ?? '';
        }
        if ($detalle) {
            $texto .= ($texto ? ' — ' : '') . $detalle;
        }
        return $texto ?: $default;
    }


    public function verificarAsistencia(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fecha' => 'required|date',
        ]);

        $presente = DB::table('attendances')
            ->where('user_id', $request->user_id)
            ->where('date', $request->fecha)
            ->where('type', 'Entrada')
            ->where('status', 'Presente')
            ->exists();

        return response()->json(['presente' => $presente]);
    }
}