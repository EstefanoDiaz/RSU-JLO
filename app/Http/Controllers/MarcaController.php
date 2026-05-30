<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    /**
     * Display a listing of the resource.f
     */
    public function index()
    {
        $marcas = Marca::orderBy('nombre')->get();

        return view('marcas.index', compact('marcas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|max:100',
            'imagen' => 'nullable|image'
        ]);

        $rutaImagen = null;

        if($request->hasFile('imagen')){
            $rutaImagen = $request->file('imagen')
                ->store('marcas','public');
        }

        Marca::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'imagen' => $rutaImagen
        ]);

        return back()->with('success','Marca registrada');
    }

    /**
     * Display the specified resource.
     */
    public function show(Marca $marca)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Marca $marca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Marca $marca)
    {
        $request->validate([
            'nombre' => 'required|max:100'
        ]);

        $datos = [
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ];

        if ($request->hasFile('imagen')) {

            $ruta = $request->file('imagen')
                ->store('marcas', 'public');

            $datos['imagen'] = $ruta;
        }

        $marca->update($datos);

        return redirect()
            ->route('marcas.index')
            ->with('success', 'Marca actualizada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Marca $marca)
    {
        $marca->delete();

        return back();
    }
}
