<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalGroup;
use App\Models\Zone;
use App\Models\Schedule;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PersonalGroupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $groups = PersonalGroup::with(['schedule', 'zone', 'vehicle', 'conductor', 'assistant1', 'assistant2'])
                ->select('personal_groups.*');

            return DataTables::of($groups)
                ->addColumn('schedule_name', fn($g) => $g->schedule->name ?? '-')
                ->addColumn('zone_name',     fn($g) => $g->zone->name ?? '-')
                ->addColumn('vehicle_plate', fn($g) => $g->vehicle ? ($g->vehicle->plate ?? $g->vehicle->id) : '-')
                ->addColumn('conductor_name', fn($g) => $g->conductor->name ?? '-')
                ->addColumn('assistants', function($g) {
                    $a1 = $g->assistant1->name ?? null;
                    $a2 = $g->assistant2->name ?? null;
                    $parts = array_filter([$a1, $a2]);
                    return $parts ? implode('<br>', $parts) : '<i class="text-muted">-</i>';
                })
                ->addColumn('days_badges', function($g) {
                    $labels = ['lun' => 'L', 'mar' => 'M', 'mie' => 'X', 'jue' => 'J', 'vie' => 'V', 'sab' => 'S', 'dom' => 'D'];
                    $days = $g->work_days ?? [];
                    $html = '';
                    foreach ($labels as $key => $label) {
                        $active = in_array($key, $days);
                        $html .= $active
                            ? '<span class="badge badge-primary mr-1">' . $label . '</span>'
                            : '<span class="badge badge-light border mr-1 text-muted">' . $label . '</span>';
                    }
                    return $html;
                })
                ->addColumn('badge_status', fn($g) => $g->status === 'Activo'
                    ? '<span class="badge bg-success text-white px-2 py-1 rounded font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Activo</span>'
                    : '<span class="badge bg-secondary text-white px-2 py-1 rounded font-weight-bold"><i class="fas fa-times-circle mr-1"></i>Inactivo</span>'
                )
                ->addColumn('actions', function($g) {
                    $edit = '<button class="btn btn-sm btn-warning btn-editar mr-1" data-id="' . $g->id . '" title="Editar"><i class="fas fa-pen text-dark"></i></button>';
                    $delete = '<form action="' . route('admin.personalgroup.destroy', $g->id) . '" method="POST" class="frmEliminar d-inline">'
                        . method_field('DELETE') . csrf_field()
                        . '<button class="btn btn-sm btn-secondary" type="submit" title="Eliminar"><i class="fas fa-trash-alt text-white"></i></button></form>';
                    return $edit . $delete;
                })
                ->rawColumns(['assistants', 'days_badges', 'badge_status', 'actions'])
                ->make(true);
        }

        return view('admin.personalgroups.index');
    }

    public function create()
    {
        $schedules = Schedule::all();
        $zones     = Zone::where('status', 'Activo')->get();
        $vehicles  = Vehicle::all();
        $users     = User::all();
        return view('admin.personalgroups.template.form', compact('schedules', 'zones', 'vehicles', 'users'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'         => 'required|string|max:100',
                'schedule_id'  => 'required|exists:schedules,id',
                'zone_id'      => 'required|exists:zones,id',
                'vehicle_id'   => 'required|exists:vehicles,id',
                'conductor_id' => 'required|exists:users,id',
                'work_days'    => 'required|array|min:1',
            ]);

            PersonalGroup::create([
                'name'          => $request->name,
                'schedule_id'   => $request->schedule_id,
                'zone_id'       => $request->zone_id,
                'vehicle_id'    => $request->vehicle_id,
                'conductor_id'  => $request->conductor_id,
                'assistant1_id' => $request->assistant1_id ?: null,
                'assistant2_id' => $request->assistant2_id ?: null,
                'work_days'     => $request->work_days,
                'status'        => $request->status ?? 'Activo',
            ]);

            return response()->json(['message' => 'Grupo de personal registrado correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $group     = PersonalGroup::findOrFail($id);
        $schedules = Schedule::all();
        $zones     = Zone::where('status', 'Activo')->get();
        $vehicles  = Vehicle::all();
        $users     = User::all();
        return view('admin.personalgroups.template.form', compact('group', 'schedules', 'zones', 'vehicles', 'users'));
    }

    public function update(Request $request, $id)
    {
        try {
            $group = PersonalGroup::findOrFail($id);
            $group->update([
                'name'          => $request->name,
                'schedule_id'   => $request->schedule_id,
                'zone_id'       => $request->zone_id,
                'vehicle_id'    => $request->vehicle_id,
                'conductor_id'  => $request->conductor_id,
                'assistant1_id' => $request->assistant1_id ?: null,
                'assistant2_id' => $request->assistant2_id ?: null,
                'work_days'     => $request->work_days,
                'status'        => $request->status ?? 'Activo',
            ]);
            return response()->json(['message' => 'Grupo actualizado correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            PersonalGroup::findOrFail($id)->delete();
            return response()->json(['message' => 'Grupo eliminado correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function getGroupData($id)
    {
        $group = PersonalGroup::with(['zone', 'schedule', 'vehicle', 'conductor', 'ayudante1', 'ayudante2'])
            ->findOrFail($id);

        return response()->json([
            'id'           => $group->id,
            'name'         => $group->name,
            'zone'         => ['id' => $group->zone_id,     'name' => $group->zone->name ?? ''],
            'schedule'     => ['id' => $group->schedule_id, 'name' => $group->schedule->name ?? '', 'time_start' => $group->schedule->time_start ?? '', 'time_end' => $group->schedule->time_end ?? ''],
            'vehicle'      => ['id' => $group->vehicle_id,  'name' => ($group->vehicle->name ?? '') . ' - ' . ($group->vehicle->code ?? '')],
            'conductor'    => ['id' => $group->conductor_id, 'name' => $group->conductor->name ?? ''],
            'ayudante1'    => ['id' => $group->ayudante1_id, 'name' => $group->ayudante1->name ?? ''],
            'ayudante2'    => $group->ayudante2_id ? ['id' => $group->ayudante2_id, 'name' => $group->ayudante2->name ?? ''] : null,
        ]);
    }
}
