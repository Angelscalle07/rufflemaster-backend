<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class LoginController extends Controller
{
    public function iniciarSesion(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'codigo_mfa' => 'nullable|string'
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return response()->json(['mensaje' => '❌ Usuario no registrado'], 404);
        }

        if (!Hash::check($request->password, $usuario->password)) {
            return response()->json(['mensaje' => '❌ Contraseña incorrecta'], 401);
        }

        if ($usuario->mfa_secret !== null) {

            if (!$request->codigo_mfa) {
                return response()->json([
                    'mensaje' => '🔐 MFA requerido. Por favor ingrese el código TOTP.',
                    'status' => 'MFA_REQUIRED'
                ], 403);
            }

            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($usuario->mfa_secret, $request->codigo_mfa);

            if (!$valid) {
                return response()->json([
                    'mensaje' => '❌ Código MFA incorrecto',
                    'status' => 'MFA_INVALID'
                ], 401);
            }
        }

        $usuario->tokens()->delete();

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'mensaje' => '✅ Inicio de sesión exitoso',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
            ],
            'token' => $token,
        ]);
    }

    public function cerrarSesion(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => '🚪 Sesión cerrada correctamente']);
    }
}
