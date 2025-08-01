<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function iniciarSesion(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
        return response()->json(['mensaje' => '❌ Usuario no registrado'], 404);
        }

        if (!Hash::check($request->password, $usuario->password)) {
        return response()->json(['mensaje' => '❌ Contraseña incorrecta'], 401);
        }

        return response()->json([
            'mensaje' => '✅ Inicio de sesión exitoso',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
            ],
        ]);
    }
}
