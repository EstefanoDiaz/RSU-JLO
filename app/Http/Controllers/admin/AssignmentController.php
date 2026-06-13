<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\PersonalGroup;
use App\Models\User;
use App\Models\Vacation;
use App\Models\Contract;
use App\Models\Zone;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Assignment::with(['zone', 'schedule', 'vehicle', 'conductor', 'ayudante1', 'ayudante2', 'group'])
                ->select('assignments.*');

            if ($request->filled('start_date')) {
                $query->where('date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('date', '<=', $request->end_date);
            }
            if ($request->filled('zone_id')) {
                $query->where('zone_id', $request->zone_id);
            }
            if ($request->filled('schedule_id')) {
                $query->where('schedule_id', $request->schedule_id);
            }

            return DataTables::of($query)
                ->addColumn('fecha_fmt', fn($a) => Carbon::parse($a->date)->format('d/m/Y'))
                ->addColumn('badge_status', function ($a) {
                    if ($a->status === 'Finalizado') {
                        return '<span class="badge bg-success text-white px-2 py-1 rounded"><i class="fas fa-check-circle mr-1"></i>Finalizado</span>';
                    }
                    return '<span class="badge bg-primary text-white px-2 py-1 rounded"><i class="fas fa-calendar-check mr-1"></i>Programado</span>';
                })
                ->addColumn('zona_name', fn($a) => $a->zone->name ?? '-')
                ->addColumn('turno_name', function ($a) {
                    if (!$a->schedule) return '-';
                    return '<span class="badge badge-light border px-2 py-1">' . $a->schedule->name . '</span>';
                })
                ->addColumn('vehicle_name', function ($a) {
                    if (!$a->vehicle) return '-';
                    return '<strong>' . $a->vehicle->name . '</strong><br><small class="text-muted">' . $a->vehicle->code . '</small>';
                })
                ->addColumn('conductor_name', fn($a) => $a->conductor->name ?? '-')
                ->addColumn('ayudantes_names', function ($a) {
                    $names = '<small>' . ($a->ayudante1->name ?? '-') . '</small>';
                    if ($a->ayudante2) {
                        $names .= '<br><small class="text-muted">' . $a->ayudante2->name . '</small>';
                    }
                    return $names;
                })
                ->addColumn('actions', function ($a) {
                    $btnView   = '<button class="btn btn-sm btn-info btn-ver mr-1" data-id="' . $a->id . '" title="Ver Detalle"><i class="fas fa-eye text-white"></i> Ver</button>';
                    $btnEdit   = '<button class="btn btn-sm btn-warning btn-editar mr-1" data-id="' . $a->id . '" title="Editar"><i class="fas fa-pen text-dark"></i></button>';
                    $btnFinish = '';
                    if ($a->status === 'Programado') {
                        $btnFinish = '<button class="btn btn-sm btn-success btn-finalizar mr-1" data-id="' . $a->id . '" title="Finalizar"><i class="fas fa-flag-checkered text-white"></i></button>';
                    }
                    $btnDelete = '<form action="' . route('admin.assignment.destroy', $a->id) . '" method="POST" class="frmEliminar d-inline" style="margin:0;padding:0;">'
                        . method_field('DELETE') . csrf_field()
                        . '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar"><i class="fas fa-trash-alt text-white"></i></button></form>';
                    return $btnView . $btnEdit . $btnFinish . $btnDelete;
                })
                ->rawColumns(['badge_status', 'turno_name', 'vehicle_name', 'ayudantes_names', 'actions'])
                ->make(true);
        }

        $zones     = Zone::where('status', 'Activo')->orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $groups    = PersonalGroup::where('status', 'Activo')->orderBy('name')->get();

        return view('admin.assignments.index', compact('zones', 'schedules', 'groups'));
    }

    public function create()
    {
        $groups = PersonalGroup::where('status', 'Activo')->orderBy('name')->get();
        $users  = User::whereHas('contracts', fn($q) => $q->where('active', true))->orderBy('name')->get();
        return view('admin.assignments.template.form', compact('groups', 'users'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'group_id'     => 'required|exists:personal_groups,id',
                'start_date'   => 'required|date',
                'end_date'     => 'required|date|after_or_equal:start_date',
                'conductor_id' => 'required|exists:users,id',
                'ayudante1_id' => 'required|exists:users,id',
                'ayudante2_id' => 'nullable|exists:users,id',
                'work_days'    => 'required|array|min:1',
                'work_days.*'  => 'in:0,1,2,3,4,5,6',
            ]);

            $group    = PersonalGroup::findOrFail($request->group_id);
            $start    = Carbon::parse($request->start_date);
            $end      = Carbon::parse($request->end_date);
            $workDays = array_map('intval', $request->work_days);

            DB::beginTransaction();

            $current = $start->copy();
            $created = 0;
            while ($current->lte($end)) {
                if (in_array($current->dayOfWeek, $workDays)) {
                    $exists = Assignment::where('date', $current->toDateString())
                        ->where('vehicle_id', $group->vehicle_id)
                        ->exists();

                    if (!$exists) {
                        Assignment::create([
                            'group_id'     => $group->id,
                            'date'         => $current->toDateString(),
                            'zone_id'      => $group->zone_id,
                            'schedule_id'  => $group->schedule_id,
                            'vehicle_id'   => $group->vehicle_id,
                            'conductor_id' => $request->conductor_id,
                            'ayudante1_id' => $request->ayudante1_id,
                            'ayudante2_id' => $request->ayudante2_id,
                            'status'       => 'Programado',
                            'observations' => $request->observations,
                        ]);
                        $created++;
                    }
                }
                $current->addDay();
            }

            DB::commit();
            return response()->json(['message' => "Asignación guardada correctamente. Se generaron {$created} registro(s)."], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $assignment = Assignment::with(['zone', 'schedule', 'vehicle', 'conductor', 'ayudante1', 'ayudante2', 'group'])->findOrFail($id);
        return response()->json($assignment);
    }

    public function edit($id)
    {
        $assignment = Assignment::with(['zone', 'schedule', 'vehicle', 'conductor', 'ayudante1', 'ayudante2'])->findOrFail($id);
        $users      = User::whereHas('contracts', fn($q) => $q->where('active', true))->orderBy('name')->get();
        return view('admin.assignments.template.form_edit', compact('assignment', 'users'));
    }

    public function update(Request $request, $id)
    {
        try {
            $assignment = Assignment::findOrFail($id);
            $request->validate([
                'conductor_id' => 'required|exists:users,id',
                'ayudante1_id' => 'required|exists:users,id',
                'ayudante2_id' => 'nullable|exists:users,id',
                'status'       => 'required|in:Programado,Finalizado',
            ]);

            $assignment->update($request->only(['conductor_id', 'ayudante1_id', 'ayudante2_id', 'status', 'observations']));
            return response()->json(['message' => 'Asignación actualizada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Assignment::findOrFail($id)->delete();
            return response()->json(['message' => 'Asignación eliminada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function finalize($id)
    {
        try {
            $assignment = Assignment::findOrFail($id);
            if ($assignment->status !== 'Programado') {
                return response()->json(['message' => 'La asignación ya fue finalizada.'], 422);
            }
            $assignment->update(['status' => 'Finalizado']);
            return response()->json(['message' => 'Asignación finalizada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function validateAvailability(Request $request)
    {
        $request->validate([
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'conductor_id' => 'required|exists:users,id',
            'ayudante1_id' => 'required|exists:users,id',
            'ayudante2_id' => 'nullable|exists:users,id',
        ]);

        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $peopleIds = array_filter([
            'conductor' => $request->conductor_id,
            'ayudante1' => $request->ayudante1_id,
            'ayudante2' => $request->ayudante2_id,
        ]);

        $errors      = [];
        $suggestions = [];

        foreach ($peopleIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $vacation = Vacation::where('user_id', $userId)
                ->where('status', 'APROBADA')
                ->where('start_date', '<=', $endDate)
                ->where('end_date', '>=', $startDate)
                ->first();

            if ($vacation) {
                $affectedDays = $this->countOverlapDays($startDate, $endDate, $vacation->start_date, $vacation->end_date);
                $errors[] = "{$user->name}: Tiene vacaciones aprobadas para la fecha {$vacation->start_date} (afecta {$startDate}, " . implode(', ', array_slice($this->getAffectedDates($startDate, $endDate, $vacation->start_date, $vacation->end_date), 0, 3)) . " y {$affectedDays} días más)";

                $replacement = $this->findReplacement($userId, array_values($peopleIds), $startDate, $endDate);
                if ($replacement) {
                    $suggestions[] = "Sugerencia: Reemplazar a {$user->name} con {$replacement->name}";
                }
            }

            $hasContract = Contract::where('user_id', $userId)
                ->where('active', true)
                ->where('start_date', '<=', $startDate)
                ->where(function ($q) use ($endDate) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $endDate);
                })
                ->exists();

            if (!$hasContract) {
                $affectedDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
                $errors[] = "{$user->name}: Empleado no disponible por razones de contrato (afecta {$startDate}, y {$affectedDays} días más)";

                $replacement = $this->findReplacement($userId, array_values($peopleIds), $startDate, $endDate);
                if ($replacement && !in_array("Sugerencia: Reemplazar a {$user->name} con {$replacement->name}", $suggestions)) {
                    $suggestions[] = "Sugerencia: Reemplazar a {$user->name} con {$replacement->name}";
                }
            }
        }

        if (count($errors) > 0) {
            return response()->json([
                'status'      => 'error',
                'errors'      => $errors,
                'suggestions' => $suggestions,
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    private function findReplacement($excludeUserId, array $alreadyAssigned, $startDate, $endDate): ?User
    {
        $vacationConflicts = Vacation::where('status', 'APROBADA')
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->pluck('user_id');

        return User::whereHas('contracts', fn($q) => $q->where('active', true)
                ->where('start_date', '<=', $startDate)
                ->where(fn($q2) => $q2->whereNull('end_date')->orWhere('end_date', '>=', $endDate)))
            ->whereNotIn('id', $vacationConflicts)
            ->whereNotIn('id', $alreadyAssigned)
            ->where('id', '!=', $excludeUserId)
            ->first();
    }

    private function countOverlapDays($s1, $e1, $s2, $e2): int
    {
        return count($this->getAffectedDates($s1, $e1, $s2, $e2));
    }

    private function getAffectedDates($s1, $e1, $s2, $e2): array
    {
        $overlapStart = max($s1, $s2);
        $overlapEnd   = min($e1, $e2);
        $dates        = [];
        $current      = Carbon::parse($overlapStart);
        $end          = Carbon::parse($overlapEnd);
        while ($current->lte($end)) {
            $dates[] = $current->format('d/m/Y');
            $current->addDay();
        }
        return $dates;
    }
}
