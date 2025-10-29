<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogFraudeController extends Controller
{
    public function index()
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            return response()->json(['error' => 'No se encontró el archivo de logs'], 404);
        }

        $contenido = File::get($path);

        $lineas = collect(explode("\n", $contenido))
            ->filter(function ($linea) {
                return str_contains($linea, '⚠️ Posible fraude detectado')
                    || str_contains($linea, '✅ Transacción normal')
                    || str_contains($linea, '❌ Error al conectar con el modelo antifraude');
            })
            ->reverse()
            ->values();

        return response()->json($lineas);
    }
}
