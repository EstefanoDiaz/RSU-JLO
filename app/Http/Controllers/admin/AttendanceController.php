<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user', 'schedule');

        // Filtros
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('search_employee')) {
            $search = $request->search_employee;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('dni', 'like', "%$search%");
            });
        }

        $attendances = $query->get();

        if ($request->ajax() && !$request->filled('start_date') && !$request->filled('end_date') && !$request->filled('search_employee')) {
            $attendances = Attendance::with('user', 'schedule')->get();
        }

        if ($request->ajax()) {
            return DataTables::of($attendances)
                ->addColumn('dni', function ($a) {
                    return $a->user ? $a->user->dni : '-';
                })
                ->addColumn('employee', function ($a) {
                    return $a->user ? $a->user->name : '-';
                })
                ->addColumn('datetime', function ($a) {
                    return $a->date->format('d/m/Y') . '<br><small class="text-muted">' . $a->time . '</small>';
                })
                ->addColumn('type', function ($a) {
                    $color = $a->type === 'Entrada' ? 'success' : 'info';
                    return '<span class="badge badge-' . $color . '">' . $a->type . '</span>';
                })
                ->addColumn('status', function ($a) {
                    $color = $a->status === 'Presente' ? 'success' : 'danger';
                    return '<span class="badge badge-' . $color . '">' . $a->status . '</span>';
                })
                ->addColumn('schedule_name', function ($a) {
                    return $a->schedule ? $a->schedule->name : '-';
                })
                ->addColumn('actions', function ($a) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $a->id . '" title="Editar">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="' . route('admin.attendance.destroy', $a->id) . '" method="POST"
                            class="frmEliminar d-inline">' . method_field('DELETE') . csrf_field() . '
                            <button class="btn btn-sm btn-danger" type="submit" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>';
                })
                ->rawColumns(['datetime', 'type', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.attendances.index');
    }

    public function create()
    {
        $users     = User::select('id', 'name', 'dni')->orderBy('name')->get();
        $schedules = Schedule::orderBy('time_start')->get();
        $now       = Carbon::now();
        $currentSchedule = $this->detectSchedule($now->format('H:i'));

        return view('admin.attendances.create', compact('users', 'schedules', 'currentSchedule', 'now'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id'     => 'required|exists:users,id',
                'date'        => 'required|date',
                'time'        => 'required',
                'schedule_id' => 'required|exists:schedules,id',
                'type'        => 'required|in:Entrada,Salida',
                'status'      => 'required|in:Presente,Ausente',
            ]);

            // Validar que el primer registro del turno sea Entrada
            $existeEntrada = Attendance::where('user_id', $request->user_id)
                ->whereDate('date', $request->date)
                ->where('schedule_id', $request->schedule_id)
                ->where('type', 'Entrada')
                ->exists();

            if (!$existeEntrada && $request->type === 'Salida') {
                return response()->json([
                    'message' => 'El primer registro del turno debe ser una ENTRADA.'
                ], 422);
            }

            Attendance::create([
                'user_id'     => $request->user_id,
                'date'        => $request->date,
                'time'        => $request->time,
                'schedule_id' => $request->schedule_id,
                'type'        => $request->type,
                'status'      => $request->status,
                'notes'       => $request->notes,
            ]);

            return response()->json(['message' => 'Asistencia registrada correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $attendance = Attendance::findOrFail($id);
        $users      = User::select('id', 'name', 'dni')->orderBy('name')->get();
        $schedules  = Schedule::orderBy('time_start')->get();

        return view('admin.attendances.edit', compact('attendance', 'users', 'schedules'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            $request->validate([
                'user_id'     => 'required|exists:users,id',
                'date'        => 'required|date',
                'time'        => 'required',
                'schedule_id' => 'required|exists:schedules,id',
                'type'        => 'required|in:Entrada,Salida',
                'status'      => 'required|in:Presente,Ausente',
            ]);

            $attendance->update([
                'user_id'     => $request->user_id,
                'date'        => $request->date,
                'time'        => $request->time,
                'schedule_id' => $request->schedule_id,
                'type'        => $request->type,
                'status'      => $request->status,
                'notes'       => $request->notes,
            ]);

            return response()->json(['message' => 'Asistencia actualizada correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            Attendance::findOrFail($id)->delete();
            return response()->json(['message' => 'Asistencia eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    // Detectar turno según hora actual
    public function getScheduleByTime(Request $request)
    {
        $time     = $request->time ?? Carbon::now()->format('H:i');
        $schedule = $this->detectSchedule($time);
        return response()->json($schedule);
    }

    // Detectar tipo (Entrada/Salida) según registros del día
    public function getAttendanceType(Request $request)
{
    $userId     = $request->user_id;
    $date       = $request->date ?? Carbon::now()->toDateString();
    $scheduleId = $request->schedule_id;

    // Buscar si ya tiene entrada en este turno y día
    $existeEntrada = Attendance::where('user_id', $userId)
        ->whereDate('date', $date)
        ->where('schedule_id', $scheduleId)
        ->where('type', 'Entrada')
        ->exists();

    // Si ya tiene entrada → es Salida, si no → es Entrada
    $type = $existeEntrada ? 'Salida' : 'Entrada';

    // Registros del día para mostrar en el card
    $registros = Attendance::where('user_id', $userId)
        ->whereDate('date', $date)
        ->with('schedule')
        ->orderBy('time')
        ->get()
        ->map(function ($r) {
            return [
                'id'       => $r->id,
                'type'     => $r->type,
                'time'     => $r->time,
                'schedule' => $r->schedule ? $r->schedule->name : '-',
                'status'   => $r->status,
            ];
        });

    return response()->json([
        'type'      => $type,
        'registros' => $registros,
        'mensaje'   => $type === 'Entrada'
            ? 'Primer registro del turno - debe ser ENTRADA'
            : 'Ya tiene una entrada registrada - se registrará como SALIDA',
    ]);
}

    // Info del empleado
    public function getUserInfo(Request $request)
    {
        $user = User::find($request->user_id);
        if (!$user) return response()->json(null);

        return response()->json([
            'name'  => $user->name,
            'dni'   => $user->dni,
            'email' => $user->email,
            'phone' => $user->phone ?? 'No registrado',
        ]);
    }

    private function detectSchedule(string $time): ?Schedule
    {
        return Schedule::all()->first(function ($s) use ($time) {
            $start = $s->time_start;
            $end   = $s->time_end;
            if ($start <= $end) {
                return $time >= $start && $time <= $end;
            }
            return $time >= $start || $time <= $end;
        });
    }
}