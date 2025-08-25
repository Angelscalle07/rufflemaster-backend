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
}
