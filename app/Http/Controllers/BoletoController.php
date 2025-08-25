<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boleto;
use App\Services\BlockchainService;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use App\Models\Usuario;

class BoletoController extends Controller
{
    public function store(Request $request)
    {
    $request->validate([
        'usuario_id' => 'required|exists:usuarios,id',
        'rifa_id' => 'required|exists:rifas,id',
        'cantidad' => 'required|integer|min:1',
    ]);

    $usuario = Usuario::find($request->usuario_id);

    if (!$usuario || !$usuario->wallet_address) {
        return response()->json(['error' => '⚠️ Usuario no tiene wallet asociada'], 400);
    }

    // Guardar boleto en BD
    $boleto = Boleto::create([
        'usuario_id' => $usuario->id,
        'rifa_id' => $request->rifa_id,
        'cantidad' => $request->cantidad,
        'estado' => 'Activo',
        'fecha_compra' => now()
    ]);

    try {
 
        $blockchain = new \App\Services\BlockchainService();
        $result = $blockchain->mintTicket($usuario->wallet_address, $request->rifa_id);


        $boleto->tx_hash = $result['tx'];
        $boleto->token_id = $result['tokenId'];
        $boleto->save();

        return response()->json([
            'mensaje' => '✅ Boleto comprado y NFT minteado',
            'boleto' => $boleto
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => '❌ Error al mintear NFT',
            'detalle' => $e->getMessage()
        ], 500);
    }
}



    public function index($usuario_id)
    {
        $boletos = Boleto::with('rifa')
            ->where('usuario_id', $usuario_id)
            ->get();

        return response()->json($boletos);
    }

    public function destroy($id)
    {
    $boleto = Boleto::find($id);

    if (!$boleto) {
        return response()->json(['error' => 'Boleto no encontrado'], 404);
    }

    $boleto->delete();

    return response()->json(['message' => 'Boleto eliminado correctamente']);
    }
}
