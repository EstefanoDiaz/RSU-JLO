<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceScheduleDetail;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class MaintenanceScheduleController extends Controller
{
    private $daysTranslations = [
        'LUNES' => 'Monday', 'MARTES' => 'Tuesday', 'MIÉRCOLES' => 'Wednesday',
        'JUEVES' => 'Thursday', 'VIERNES' => 'Friday', 'SÁBADO' => 'Saturday', 'DOMINGO' => 'Sunday'
    ];

    public function index(Request $request, $maintenanceId)
    {
        $maintenance = Maintenance::findOrFail($maintenanceId);

        if ($request->ajax()) {
            $schedules = MaintenanceSchedule::with(['vehicle', 'user'])->where('maintenance_id', $maintenanceId)->get();

            return DataTables::of($schedules)
                ->addColumn('actions', function ($schedule) {
                    $btnVer = '<button class="btn btn-sm btn-info btn-ver-dias mr-1" id="' . $schedule->id . '" title="Ver días"><i class="fas fa-car-side"></i></button>';
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar-horario mr-1" id="' . $schedule->id . '" title="Editar"><i class="fas fa-pen"></i></button>';
                    $btnDelete = '<form action="' . route('admin.schedules.destroy', $schedule->id) . '" method="POST" class="frmEliminarHorario" style="display:inline;">' 
                        . method_field('DELETE') . csrf_field() . 
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar"><i class="fas fa-trash-alt"></i></button></form>';
                    
                    return $btnVer . $btnEdit . $btnDelete;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.maintenance_schedules.index', compact('maintenance'));
    }

    public function create($maintenanceId)
    {
        $maintenance = Maintenance::findOrFail($maintenanceId);
        $vehicles = Vehicle::all()->pluck('plate', 'id');
        $responsibles = User::all()->pluck('name', 'id'); 
        return view('admin.maintenance_schedules.create', compact('maintenance', 'vehicles', 'responsibles'));
    }

    public function store(Request $request, $maintenanceId)
    {
        try {
            $maintenance = Maintenance::findOrFail($maintenanceId);

            $request->validate([
                'vehicle_id' => 'required',
                'user_id' => 'required',
                'type' => 'required|in:PREVENTIVO,LIMPIEZA,REPARACIÓN',
                'day_of_week' => 'required',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
            ]);

            // Validación de No Solapamiento por vehículo, día y horas
            $overlap = MaintenanceSchedule::where('vehicle_id', $request->vehicle_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('maintenance_id', $maintenanceId)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                          ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                          ->orWhere(function ($q) use ($request) {
                              $q->where('start_time', '<=', $request->start_time)
                                ->where('end_time', '>=', $request->end_time); 
                          });
                })->exists();

            if ($overlap) {
                return response()->json(['message' => 'Conflictos de horario: El vehículo ya tiene una actividad asignada en ese día y rango de horas.'], 422);
            }

            $schedule = MaintenanceSchedule::create(array_merge($request->all(), ['maintenance_id' => $maintenanceId]));

            // Generación automática de fechas detalle
            $this->generateScheduleDays($schedule, $maintenance);

            return response()->json(['message' => 'Horario y días de atención generados con éxito.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function edit($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $maintenance = Maintenance::findOrFail($schedule->maintenance_id);
        $vehicles = Vehicle::all()->pluck('plate', 'id');
        $responsibles = User::all()->pluck('name', 'id');
        return view('admin.maintenance_schedules.edit', compact('schedule', 'maintenance', 'vehicles', 'responsibles'));
    }

    public function update(Request $request, $id)
    {
        try {
            $schedule = MaintenanceSchedule::findOrFail($id);
            $maintenance = Maintenance::findOrFail($schedule->maintenance_id);

            $request->validate([
                'vehicle_id' => 'required',
                'user_id' => 'required',
                'type' => 'required|in:PREVENTIVO,LIMPIEZA,REPARACIÓN',
                'day_of_week' => 'required',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
            ]);

            // Validación de solapamiento para la actualización
            $overlap = MaintenanceSchedule::where('id', '!=', $id)
                ->where('vehicle_id', $request->vehicle_id)
                ->where('day_of_week', $request->day_of_week)
                ->where('maintenance_id', $schedule->maintenance_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                          ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                          ->orWhere(function ($q) use ($request) {
                              $q->where('start_time', '<=', $request->start_time)
                                ->where('end_time', '>=', $request->end_time);
                          });
                })->exists();

            if ($overlap) {
                return response()->json(['message' => 'No se puede actualizar. Se genera un cruce de horarios para el vehículo.'], 422);
            }

            $schedule->update($request->all());

            // Regenerar días
            MaintenanceScheduleDetail::where('m_schedule_id', $id)->delete();
            $this->generateScheduleDays($schedule, $maintenance);

            return response()->json(['message' => 'Horario actualizado y días regenerados.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $schedule->delete();
        return response()->json(['message' => 'Horario y sus días detallados eliminados correctamente.'], 200);
    }

    public function getDays($id)
    {
        $days = MaintenanceScheduleDetail::where('m_schedule_id', $id)
            ->orderBy('date', 'asc')
            ->get(['id', 'date', 'observation', 'image', 'status']);
            
        return response()->json($days);
    }

    public function updateDayDetail(Request $request, $detailId)
    {
        try {
            $detail = MaintenanceScheduleDetail::findOrFail($detailId);

            $request->validate([
                'status' => 'required|in:1,0',
                'observation' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $data = [
                'status' => $request->status,
                'observation' => $request->observation,
            ];

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/details', $filename);
                $data['image'] = 'storage/details/' . $filename;
            }

            $detail->update($data);

            return response()->json(['message' => 'Día de mantenimiento actualizado correctamente.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function generateScheduleDays($schedule, $maintenance)
    {
        $start = Carbon::parse($maintenance->start_date)->startOfDay();
        $end = Carbon::parse($maintenance->end_date)->endOfDay();
        
        $englishDay = $this->daysTranslations[$schedule->day_of_week]; 

        $current = clone $start;

        if ($current->format('l') !== $englishDay) {
            $current->modify("next $englishDay");
        }

        while ($current->greaterThanOrEqualTo($start) && $current->lessThanOrEqualTo($end)) {
            
            MaintenanceScheduleDetail::create([
                'm_schedule_id' => $schedule->id,
                'date'          => $current->format('Y-m-d')
            ]);

            $current->addWeek();
        }
    }
}