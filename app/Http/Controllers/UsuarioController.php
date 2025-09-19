<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    public function updateWallet(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'wallet_address' => 'required|string'
        ]);

        $user = Usuario::find($request->usuario_id);
        $user->wallet_address = $request->wallet_address;
        $user->save();

        return response()->json([
            'mensaje' => '✅ Wallet asociada correctamente',
            'user' => $user
        ]);
    }

    private static $activos = [];

    public function ping(Request $request)
    {
        $usuarioId = $request->usuario_id;
        self::$activos[$usuarioId] = now()->timestamp;

        return response()->json(['status' => 'ok']);
    }

    public function activos()
    {
    
    $usuarios = \App\Models\Usuario::where('rol', 'participante')->get();

    return response()->json($usuarios);
    }



}
