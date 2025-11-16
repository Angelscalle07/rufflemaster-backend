<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use App\Models\Usuario;

class MFAController extends Controller
{
    public function generarSecret(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = Usuario::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'ERROR', 'mensaje' => 'Usuario no encontrado'], 404);
        }

        $google2fa = new Google2FA();

        if (!$user->mfa_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->mfa_secret = $secret;
            $user->save();

            return response()->json([
                'status' => 'OK',
                'secret' => $secret,
                'mensaje' => 'Clave generada. Agrégala en Google Authenticator.'
            ]);
        } else {
            return response()->json([
                'status' => 'OK',
                'secret' => $user->mfa_secret,
                'mensaje' => 'MFA ya está activado. Usa la clave existente.'
            ]);
        }
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string'
        ]);

        $user = Usuario::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'ERROR', 'mensaje' => 'Usuario no encontrado'], 404);
        }

        if (!$user->mfa_secret) {
            return response()->json(['status' => 'ERROR', 'mensaje' => 'MFA no activado'], 400);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->mfa_secret, $request->codigo);

        if ($valid) {
            return response()->json(['status' => 'OK', 'mensaje' => 'Código MFA correcto']);
        }

        return response()->json(['status' => 'ERROR', 'mensaje' => 'Código MFA incorrecto'], 401);
    }
}

