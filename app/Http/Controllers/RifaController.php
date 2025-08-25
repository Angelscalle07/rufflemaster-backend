<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rifa;

class RifaController extends Controller
{
    public function index()
    {
        return response()->json(Rifa::all(), 200);
    }

    public function activas()
    {
        return response()->json(
            Rifa::where('estado', 'activa')->get(),
            200
        );
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'premio' => 'required|string|max:255',
            'creada_por' => 'required|integer|exists:usuarios,id'
        ]);

        $rifa = Rifa::create($request->all());

        return response()->json([
            'mensaje' => '✅ Rifa creada con éxito',
            'rifa' => $rifa
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $rifa = Rifa::findOrFail($id);

        $rifa->update($request->all());

        return response()->json(['mensaje' => 'Rifa actualizada', 'data' => $rifa]);
    }

    public function destroy($id)
    {
        Rifa::destroy($id);
        return response()->json(['mensaje' => 'Rifa eliminada']);
    }
}
