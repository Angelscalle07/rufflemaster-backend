<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rifa;
use App\Models\Boleto;

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

    public function jugar($id)
    {
    $rifa = Rifa::findOrFail($id);

    if ($rifa->estado === 'finalizada') {
        return response()->json(['error' => 'La rifa ya fue jugada'], 400);
    }

    $boletos = Boleto::where('rifa_id', $rifa->id)->get();

    if ($boletos->count() === 0) {
        $rifa->estado = 'finalizada';
        $rifa->save();
        return response()->json(['error' => 'La rifa no tiene boletos'], 400);
    }

    $ganador = $boletos->random();

    $rifa->ganador_boleto_id = $ganador->id;
    $rifa->estado = 'finalizada';
    $rifa->save();

    return response()->json([
        'mensaje' => '🎉 Rifa jugada con éxito',
        'rifa' => $rifa,
        'boleto_ganador' => $ganador,
        'usuario_ganador' => $ganador->usuario_id,
        ]);
    }

    public function resultados()
    {
    $rifas = Rifa::with(['ganadorBoleto.usuario'])->get();

    $data = $rifas->map(function ($rifa) {
        return [
            'id' => $rifa->id,
            'titulo' => $rifa->titulo,
            'fecha_fin' => $rifa->fecha_fin,
            'premio' => $rifa->premio,
            'ganador' => $rifa->ganadorBoleto 
                ? $rifa->ganadorBoleto->usuario->nombre 
                : 'Sin ganador',
        ];
    });

    return response()->json($data, 200);
    }
}
