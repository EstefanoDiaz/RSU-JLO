<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use App\Models\User;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Traemos las vacaciones cargando la relación de los usuarios
            $vacations = Vacation::with('user')->select('vacations.*');

            return DataTables::of($vacations)
                ->addColumn('dni', function ($vacation) {
                    return $vacation->user->dni ?? 'Sin DNI';
                })
                ->addColumn('employee_name', function ($vacation) {
                    return $vacation->user->name;
                })
                ->addColumn('available_days', function ($vacation) {
                    // Pintamos los días restantes guardados al momento
                    return '<span class="badge badge-light border px-2.5 py-1.5 font-weight-bold shadow-sm" style="color: #071D38;">' . $vacation->user_available_days_at_moment . ' días</span>';
                })
                ->addColumn('badge_status', function ($vacation) {
                    if ($vacation->status === 'PENDIENTE') {
                        return '<span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-clock mr-1"></i>PENDIENTE</span>';
                    } elseif ($vacation->status === 'APROBADA') {
                        return '<span class="badge bg-success text-white px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-check-circle mr-1"></i>APROBADA</span>';
                    } else {
                        return '<span class="badge bg-danger text-white px-2.5 py-1.5 rounded-xl font-weight-bold shadow-sm"><i class="fas fa-times-circle mr-1"></i>RECHAZADA</span>';
                    }
                })
                ->addColumn('actions', function ($vacation) {
                    $btnApprove = ''; $btnReject = ''; $btnEdit = '';

                    if ($vacation->status === 'PENDIENTE') {
                        $btnApprove = '<button class="btn btn-sm btn-success btn-aprobar mr-1" id="' . $vacation->id . '" title="Aprobar"><i class="fas fa-check text-white"></i></button>';
                        $btnReject = '<button class="btn btn-sm btn-danger btn-rechazar mr-1" id="' . $vacation->id . '" title="Rechazar"><i class="fas fa-ban text-white"></i></button>';
                        $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $vacation->id . '" title="Editar"><i class="fas fa-pen text-dark"></i></button>';
                    }

                    $btnDelete = '<form action="' . route('admin.vacation.destroy', $vacation->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0;">' 
                        . method_field('DELETE') . csrf_field() . 
                        '<button class="btn btn-sm btn-secondary" type="submit" title="Eliminar"><i class="fas fa-trash-alt text-white"></i></button></form>';
                    
                    return $btnApprove . $btnReject . $btnEdit . $btnDelete;
                })
                ->rawColumns(['available_days', 'badge_status', 'actions'])
                ->make(true);
        }

        return view('admin.vacations.index');
    }

    public function create()
    {
        // 🎯 RÚBRICA: Traemos solo los usuarios que tengan un contrato activo y que sean 'Nombrado' o 'Permanente'
        $users = User::whereHas('contracts', function($query) {
            $query->where('active', true)
                  ->whereIn('type', ['Nombrado', 'Permanente']);
        })->get();

        return view('admin.vacations.create', compact('users'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'user_id'    => 'required',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $user = User::findOrFail($request->user_id);
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            
            // 🎯 REGLA 1: Validación estricta del rango de fechas permitido (Año/Mes actual hasta Año Siguiente)
            $primerDiaMesActual = Carbon::now()->startOfMonth();
            $ultimoDiaAnioSiguiente = Carbon::now()->addYear()->endOfYear();

            if ($start->lt($primerDiaMesActual) || $end->gt($ultimoDiaAnioSiguiente)) {
                return response()->json([
                    'message' => 'Rango de fechas no permitido. Solo puede seleccionar vacaciones desde ' . 
                                 $primerDiaMesActual->format('d/m/Y') . ' hasta el ' . $ultimoDiaAnioSiguiente->format('d/m/Y') . '.'
                ], 422);
            }

            // Calculamos con precisión los días que está intentando solicitar en esta transacción
            $daysRequested = $start->diffInDays($end) + 1;

            // 🎯 REGLA 2: Control dinámico de la Bolsa de 30 Días de Ley
            // Sumamos todos los días que el usuario ya consumió o tiene pendientes en este periodo
            $diasConsumidos = Vacation::where('user_id', $user->id)
                ->whereIn('status', ['PENDIENTE', 'APROBADA'])
                ->sum('days');

            $maximoPermitido = 30; // Los 30 días estipulados por ley
            $diasRestantesReales = $maximoPermitido - $diasConsumidos;

            // Si ya consumió sus 30 días por completo
            if ($diasRestantesReales <= 0) {
                return response()->json(['message' => 'El usuario ya cumplió con sus 30 días de vacaciones acumuladas en el periodo actual. No puede solicitar más.'], 422);
            }

            // Si está intentando colocar más días de los que le quedan disponibles
            if ($daysRequested > $diasRestantesReales) {
                return response()->json([
                    'message' => 'Exceso de días. El usuario ya ha solicitado ' . $diasConsumidos . ' días anteriormente, por lo que únicamente le resta un saldo de ' . $diasRestantesReales . ' día(s) disponible(s). Su selección actual es de ' . $daysRequested . ' días.'
                ], 422);
            }

            // REGLA 3: Evitar colisión o cruce de calendarios entre solicitudes del mismo usuario
            $coincidencias = Vacation::where('user_id', $user->id)
                ->whereIn('status', ['PENDIENTE', 'APROBADA'])
                ->where(function($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                          ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
                })->count();

            if ($coincidencias > 0) {
                return response()->json(['message' => 'Las fechas seleccionadas colisionan con otra solicitud activa en el sistema.'], 422);
            }

            // Al estar aprobadas todas las reglas de negocio, registramos la solicitud de forma limpia
            Vacation::create([
                'user_id'       => $request->user_id,
                'request_date'  => Carbon::now()->toDateString(),
                'start_date'    => $request->start_date,
                'end_date'      => $request->end_date,
                'days'          => $daysRequested,
                'status'        => 'PENDIENTE',
                // El indicador "Días R." mostrará los días que le quedarán al usuario una vez que esta sea procesada
                'user_available_days_at_moment' => $diasRestantesReales, 
                'notes'         => $request->notes
            ]);

            return response()->json(['message' => 'Solicitud de vacaciones procesada correctamente en estado PENDIENTE.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en el servidor: ' . $th->getMessage()], 500);
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status !== 'PENDIENTE') {
                return response()->json(['message' => 'Esta solicitud ya no está pendiente.'], 422);
            }

            // 🎯 RÚBRICA: Al aprobar la solicitud, la cantidad de días se restará de sus días disponibles
            $vacation->user_available_days_at_moment -= $vacation->days;
            $vacation->status = 'APROBADA';
            $vacation->save();

            DB::commit();
            return response()->json(['message' => 'Solicitud aprobada de forma conforme. Días restados del saldo anual.'], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status !== 'PENDIENTE') {
                return response()->json(['message' => 'Esta solicitud ya no está pendiente.'], 422);
            }

            // 🎯 RÚBRICA: Al rechazar la solicitud, la cantidad de días solicitados NO se restará
            $vacation->status = 'RECHAZADA';
            $vacation->save();

            return response()->json(['message' => 'Solicitud rechazada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $vacation = Vacation::findOrFail($id);
        $users = User::whereHas('contracts', function($query) {
            $query->where('active', true)->whereIn('type', ['Nombrado', 'Permanente']);
        })->get();
        return view('admin.vacations.edit', compact('vacation', 'users'));
    }

    public function update(Request $request, $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status !== 'PENDIENTE') {
                return response()->json(['message' => 'Solo se pueden modificar solicitudes en estado PENDIENTE.'], 422);
            }

            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            
            // Re-validamos rango en la edición
            $primerDiaMesActual = Carbon::now()->startOfMonth();
            $ultimoDiaAnioSiguiente = Carbon::now()->addYear()->endOfYear();

            if ($start->lt($primerDiaMesActual) || $end->gt($ultimoDiaAnioSiguiente)) {
                return response()->json(['message' => 'Rango fuera de límite para la edición.'], 422);
            }

            $daysRequested = $start->diffInDays($end) + 1;

            // Para recalcular en la edición, sumamos el consumo omitiendo la solicitud actual que se está modificando
            $diasConsumidosOtros = Vacation::where('user_id', $vacation->user_id)
                ->where('id', '!=', $vacation->id)
                ->whereIn('status', ['PENDIENTE', 'APROBADA'])
                ->sum('days');

            $diasRestantesReales = 30 - $diasConsumidosOtros;

            if ($daysRequested > $diasRestantesReales) {
                return response()->json(['message' => 'La actualización excede la bolsa de días de ley. Saldo disponible: ' . $diasRestantesReales . ' días.'], 422);
            }

            $vacation->update([
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'days'       => $daysRequested,
                'notes'      => $request->notes
            ]);

            return response()->json(['message' => 'Solicitud de vacaciones actualizada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            // 🎯 RÚBRICA: Al eliminar la solicitud, esta desaparecerá del listado
            $vacation->delete();
            return response()->json(['message' => 'Solicitud removida del sistema de forma permanente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }
}