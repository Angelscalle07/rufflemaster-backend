<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class RegistroController extends Controller
{
    public function registrar(Request $request)
{
    try {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:6',
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'participante',
        ]);

        return response()->json(['mensaje' => 'Usuario registrado con éxito'], 201);
    } catch (\Exception $e) {
        return response()->json([
            'mensaje' => 'Error interno',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
