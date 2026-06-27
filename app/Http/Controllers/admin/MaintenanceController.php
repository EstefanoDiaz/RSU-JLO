<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $maintenances = Maintenance::all();

            return DataTables::of($maintenances)
                ->addColumn('actions', function ($maintenance) {
                    $btnSchedule = '<button class="btn btn-sm btn-info btn-horario mr-1" id="' . $maintenance->id . '" title="Horario"><i class="fas fa-calendar-alt"></i></button>';
                    
                    $btnEdit = '<button class="btn btn-sm btn-warning btn-editar mr-1" id="' . $maintenance->id . '" title="Editar"><i class="fas fa-pen"></i></button>';
                    
                    $btnDelete = '<form action="' . route('admin.maintenance.destroy', $maintenance->id) . '" method="POST" class="frmEliminar" style="display:inline; margin:0; padding:0; border:none;">' 
                        . method_field('DELETE') . csrf_field() . 
                        '<button class="btn btn-sm btn-danger" type="submit" title="Eliminar"><i class="fas fa-trash-alt"></i></button></form>';
                    
                    return $btnSchedule . $btnEdit . $btnDelete;
                })
                ->rawColumns(['actions']) 
                ->make(true);
        }

        return view('admin.maintenances.index');
    }

    public function create()
    {
        return view('admin.maintenances.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|max:150',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ],[
                'name.required' => 'El nombre del mantenimiento es obligatorio.',
                'start_date.required' => 'La fecha de inicio es obligatoria.',
                'end_date.required' => 'La fecha de fin es obligatoria.',
                'end_date.after_or_equal' => 'La fecha de fin no puede ser menor a la fecha de inicio.',
            ]);

            // Validación de solapamiento de fechas
            $overlap = Maintenance::where(function ($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                          ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                          ->orWhere(function ($q) use ($request) {
                              $q->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->end_date);
                          });
                })->exists();

            if ($overlap) {
                return response()->json(['message' => 'Ya existe un mantenimiento programado que se solapa con este rango de fechas.'], 422);
            }

            Maintenance::create($request->all());
            return response()->json(['message' => 'Mantenimiento registrado correctamente'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al guardar: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        return view('admin.maintenances.edit', compact('maintenance'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $maintenance = Maintenance::findOrFail($id);

            $request->validate([
                'name' => 'required|max:150',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ], [
                'name.required' => 'El nombre del mantenimiento es obligatorio.',
                'start_date.required' => 'La fecha de inicio es obligatoria.',
                'end_date.required' => 'La fecha de fin es obligatoria.',
                'end_date.after_or_equal' => 'La fecha de fin no puede ser menor a la fecha de inicio.',
            ]);

            // Validación de solapamiento excluyendo el registro actual
            $overlap = Maintenance::where('id', '!=', $id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                          ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                          ->orWhere(function ($q) use ($request) {
                              $q->where('start_date', '<=', $request->start_date)
                                ->where('end_date', '>=', $request->end_date);
                          });
                })->exists();

            if ($overlap) {
                return response()->json(['message' => 'Las fechas elegidas se cruzan con otra programación activa.'], 422);
            }

            $maintenance->update($request->all());
            return response()->json(['message' => 'Mantenimiento actualizado correctamente'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $maintenance = Maintenance::findOrFail($id);
            $maintenance->delete();
            return response()->json(['message' => 'Mantenimiento eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al eliminar: ' . $th->getMessage()], 500);
        }
    }
}