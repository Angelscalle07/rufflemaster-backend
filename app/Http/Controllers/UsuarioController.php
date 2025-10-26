<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

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
    
    public function show($id)
    {
    $usuario = Usuario::findOrFail($id);

    return response()->json($usuario, 200);
    }

    public function updatePerfil(Request $request, $id)
    {
    try {
        \Log::info('Payload recibido:', $request->all());

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'email'  => 'required|email|unique:usuarios,email,' . $usuario->id,
            'password_actual' => 'nullable|string',
            'password'        => 'nullable|string|min:3|confirmed',
        ]);

        if ($request->filled('password_actual') && $request->filled('password')) {
            if (!\Hash::check($request->password_actual, $usuario->password)) {
                return response()->json(['error' => 'La contraseña actual es incorrecta'], 422);
            }
            $usuario->password = \Hash::make($request->password);
        }

        $usuario->nombre = $request->nombre;
        $usuario->email  = $request->email;
        $usuario->save();

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'usuario' => [
                'id'     => $usuario->id,
                'nombre' => $usuario->nombre,
                'email'  => $usuario->email,
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Error en updatePerfil: '.$e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
