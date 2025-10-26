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
                'password' => 'required|string|min:3',
                'rol' => 'required|in:admin,participante',
            ]);

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'rol' => $request->rol,
            ]);
            $token = $usuario->createToken('auth_token')->plainTextToken;

            return response()->json([
                'mensaje' => '✅ Usuario registrado con éxito',
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'rol' => $usuario->rol,
                ],
                'token' => $token
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => '❌ Error de validación',
                'errores' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => '⚠️ Error interno del servidor',
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
            ], 500);
        }
    }
}

