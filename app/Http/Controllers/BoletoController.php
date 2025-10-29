<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boleto;
use App\Models\Rifa; 
use App\Services\BlockchainService;
use App\Models\Resultado; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;
use Web3\Contract;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use App\Models\Usuario;
use Illuminate\Support\Facades\Http;

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

        try {
            $response = Http::post('http://127.0.0.1:5001/analizar', [
                'transacciones' => [
                    $request->cantidad,
                    rand(10, 100), 
                    rand(5, 50),
                    rand(20, 200)
                ]
            ]);

            $resultado = $response->json();
            $fraudeDetectado = $resultado['resultado']['anomalia'] ?? false;

            if ($fraudeDetectado) {
                \Log::warning('⚠️ Posible fraude detectado', [
                    'usuario_id' => $request->usuario_id,
                    'rifa_id' => $request->rifa_id,
                    'detalles' => $resultado
                ]);
            } else {
                \Log::info('✅ Transacción normal', [
                    'usuario_id' => $request->usuario_id,
                    'rifa_id' => $request->rifa_id,
                    'detalles' => $resultado
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('❌ Error al conectar con el modelo antifraude: ' . $e->getMessage());
        }

        try {
            $boleto = Boleto::create([
                'usuario_id' => $usuario->id,
                'rifa_id' => $request->rifa_id,
                'cantidad' => $request->cantidad,
                'estado' => 'Activo',
                'fecha_compra' => now()
            ]);

            $blockchain = new \App\Services\BlockchainService();
            $result = $blockchain->mintTicket($usuario->wallet_address, $request->rifa_id);

            $boleto->tx_hash = $result['tx'];
            $boleto->token_id = $result['tokenId'];
            $boleto->save();

            return response()->json([
                'mensaje' => $fraudeDetectado
                    ? '⚠️ Compra realizada, pero se detectó una posible anomalía'
                    : '✅ Boleto comprado y NFT minteado con éxito',
                'boleto' => $boleto,
                'fraude' => $fraudeDetectado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => '❌ Error al mintear NFT',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function play(Request $request, $rifa_id)
    {
        $user = $request->user();
        if ($user) {
            $isAdmin = (isset($user->is_admin) && $user->is_admin) || (isset($user->role) && $user->role === 'admin');
            if (! $isAdmin) {
                return response()->json(['mensaje' => 'Forbidden'], 403);
            }
        } else {
            $secret = $request->header('X-ADMIN-SECRET');
            if (! $secret || $secret !== env('ADMIN_SECRET')) {
                return response()->json(['mensaje' => 'Forbidden'], 403);
            }
        }

        $rifa = Rifa::find($rifa_id);
        if (! $rifa) {
            return response()->json(['mensaje' => 'Rifa no encontrada'], 404);
        }

        $boletos = Boleto::where('rifa_id', $rifa_id)->where('estado', 'Activo')->get();
        if ($boletos->isEmpty()) {
            return response()->json(['mensaje' => 'No hay boletos activos para esta rifa'], 400);
        }

        $ganador = $boletos->random();
        if (! $ganador || ! $ganador instanceof Boleto) {
            return response()->json(['mensaje' => 'Error interno al seleccionar ganador'], 500);
        }

        DB::transaction(function () use ($rifa_id, $ganador, $rifa) {
            Resultado::create([
                'rifa_id' => $rifa_id,
                'boleto_ganador_id' => $ganador->id,
                'publicado_en' => now(),
            ]);

            Boleto::where('rifa_id', $rifa_id)->update(['estado' => 'Finalizado']);

            if (array_key_exists('estado', $rifa->getAttributes())) {
                $rifa->update(['estado' => 'Finalizado']);
            }
        });

        return response()->json([
            'mensaje' => '✅ Resultado generado',
            'ganador' => [
                'boleto_id' => $ganador->id,
                'usuario_id' => $ganador->usuario_id,
            ]
        ], 201);
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
    $boleto = Boleto::findOrFail($id);

    try {
        $blockchain = new \App\Services\BlockchainService();
        $result = $blockchain->burnTicket($boleto->token_id);

        $boleto->delete();

        return response()->json([
            'mensaje' => '✅ Boleto cancelado y NFT eliminado',
            'tx' => $result['tx']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => '❌ Error al eliminar NFT',
            'detalle' => $e->getMessage()
        ], 500);
        }
    }
}
