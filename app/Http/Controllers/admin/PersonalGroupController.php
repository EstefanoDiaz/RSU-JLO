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
            $groups = PersonalGroup::with(['zone', 'schedule', 'vehicle', 'conductor', 'ayudante1', 'ayudante2'])
                ->select('personal_groups.*');

            return DataTables::of($groups)
                ->addColumn('zona_name', fn($g) => $g->zone->name ?? '-')
                ->addColumn('schedule_name', fn($g) => $g->schedule->name . ' (' . $g->schedule->time_start . ' - ' . $g->schedule->time_end . ')' ?? '-')
                ->addColumn('vehicle_name', fn($g) => ($g->vehicle->name ?? '-') . ' - ' . ($g->vehicle->code ?? ''))
                ->addColumn('conductor_name', fn($g) => $g->conductor->name ?? '-')
                ->addColumn('ayudantes_names', function ($g) {
                    $names = $g->ayudante1->name ?? '-';
                    if ($g->ayudante2) {
                        $names .= '<br><small class="text-muted">' . $g->ayudante2->name . '</small>';
                    }
                    return $names;
                })
                ->addColumn('badge_status', function ($g) {
                    if ($g->status === 'Activo') {
                        return '<span class="badge bg-success text-white px-2 py-1 rounded"><i class="fas fa-check-circle mr-1"></i>Activo</span>';
                    }
                    return '<span class="badge bg-secondary text-white px-2 py-1 rounded"><i class="fas fa-ban mr-1"></i>Inactivo</span>';
                })
                ->addColumn('actions', function ($g) {
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" data-id="' . $g->id . '" title="Editar"><i class="fas fa-pen text-dark"></i></button>';
                    $btnDelete = '<form action="' . route('admin.personal-group.destroy', $g->id) . '" method="POST" class="frmEliminar d-inline" style="margin:0;padding:0;">'
                        . method_field('DELETE') . csrf_field()
                        . '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar"><i class="fas fa-trash-alt text-white"></i></button></form>';
                    return $btnEdit . $btnDelete;
                })
                ->rawColumns(['ayudantes_names', 'badge_status', 'actions'])
                ->make(true);
        }

        return view('admin.personal_groups.index');
    }

    public function create()
    {
        $zones     = Zone::where('status', 'Activo')->orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles  = Vehicle::where('status', 'Activo')->orderBy('name')->get();
        $users     = User::whereHas('contracts', fn($q) => $q->where('active', true))
                         ->orderBy('name')->get();

        return view('admin.personal_groups.template.form', compact('zones', 'schedules', 'vehicles', 'users'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'         => 'required|string|max:100',
                'zone_id'      => 'required|exists:zones,id',
                'schedule_id'  => 'required|exists:schedules,id',
                'vehicle_id'   => 'required|exists:vehicles,id',
                'conductor_id' => 'required|exists:users,id',
                'ayudante1_id' => 'required|exists:users,id',
                'ayudante2_id' => 'nullable|exists:users,id',
            ]);

            PersonalGroup::create($request->only([
                'name', 'zone_id', 'schedule_id', 'vehicle_id',
                'conductor_id', 'ayudante1_id', 'ayudante2_id',
            ]));

            return response()->json(['message' => 'Grupo de personal registrado correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $group     = PersonalGroup::findOrFail($id);
        $zones     = Zone::where('status', 'Activo')->orderBy('name')->get();
        $schedules = Schedule::orderBy('name')->get();
        $vehicles  = Vehicle::where('status', 'Activo')->orderBy('name')->get();
        $users     = User::whereHas('contracts', fn($q) => $q->where('active', true))
                         ->orderBy('name')->get();

        return view('admin.personal_groups.template.form', compact('group', 'zones', 'schedules', 'vehicles', 'users'));
    }

    public function update(Request $request, $id)
    {
        try {
            $group = PersonalGroup::findOrFail($id);
            $request->validate([
                'name'         => 'required|string|max:100',
                'zone_id'      => 'required|exists:zones,id',
                'schedule_id'  => 'required|exists:schedules,id',
                'vehicle_id'   => 'required|exists:vehicles,id',
                'conductor_id' => 'required|exists:users,id',
                'ayudante1_id' => 'required|exists:users,id',
                'ayudante2_id' => 'nullable|exists:users,id',
            ]);

            $group->update($request->only([
                'name', 'zone_id', 'schedule_id', 'vehicle_id',
                'conductor_id', 'ayudante1_id', 'ayudante2_id', 'status',
            ]));

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
