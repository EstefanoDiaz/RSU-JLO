<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::withCount('brandmodels')->get();

        if ($request->ajax()) {
            return DataTables::of($brands)
                ->addColumn('logo', function ($brand) {
                    $url = $brand->logo
                        ? asset($brand->logo)
                        : 'https://placehold.co/50x50?text=Sin+Logo';
                    return '<img src="' . $url . '" width="50px" class="img-thumbnail rounded">';
                })
                ->addColumn('actions', function ($brand) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $brand->id . '">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="' . route('admin.brand.destroy', $brand->id) . '" method="POST"
                            class="frmEliminar d-inline">' . method_field('DELETE') . csrf_field() . '
                            <button class="btn btn-sm btn-danger" type="submit">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>';
                })
                ->rawColumns(['logo', 'actions'])
                ->make(true);
        }

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:brands,name',
            ]);

            $logo = null;
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/brand_logos');
                $logo = Storage::url($path);
            }

            Brand::create([
                'name'        => $request->name,
                'description' => $request->description,
                'logo'        => $logo,
            ]);

            return response()->json(['message' => 'Marca registrada correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => 'Validación: ' . $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $brand = Brand::findOrFail($id);
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $brand = Brand::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:brands,name,' . $id,
            ]);

            $logo = $brand->logo;
            if ($request->hasFile('logo')) {
                // Eliminar logo anterior
                if ($brand->logo) {
                    $filePath = str_replace('/storage', 'public', $brand->logo);
                    if (Storage::exists($filePath)) {
                        Storage::delete($filePath);
                    }
                }
                $path = $request->file('logo')->store('public/brand_logos');
                $logo = Storage::url($path);
            }

            $brand->update([
                'name'        => $request->name,
                'description' => $request->description,
                'logo'        => $logo,
            ]);

            return response()->json(['message' => 'Marca actualizada correctamente'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = implode(' | ', $e->validator->errors()->all());
            return response()->json(['message' => 'Validación: ' . $errors], 422);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $brand = Brand::withCount('brandmodels')->findOrFail($id);

            if ($brand->brandmodels_count > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar: la marca tiene ' . $brand->brandmodels_count . ' modelo(s) asociado(s).'
                ], 422);
            }

            if ($brand->logo) {
                $filePath = str_replace('/storage', 'public', $brand->logo);
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
            }

            $brand->delete();
            return response()->json(['message' => 'Marca eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }
}