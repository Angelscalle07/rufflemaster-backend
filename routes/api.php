<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RifaController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\UsuarioController;

Route::post('/usuarios/ping', [UsuarioController::class, 'ping']);
Route::get('/usuarios/activos', [UsuarioController::class, 'activos']);
Route::get('/rifas/{id}/resultados', [RifaController::class, 'resultados']);
Route::post('/rifas/{id}/jugar', [RifaController::class, 'jugar']);
Route::post('/update-wallet', [UsuarioController::class, 'updateWallet']);
Route::get('/rifas', [RifaController::class, 'index']);
Route::post('/rifas', [RifaController::class, 'store']);
Route::put('/rifas/{id}', [RifaController::class, 'update']);
Route::delete('/rifas/{id}', [RifaController::class, 'destroy']);
Route::post('/boletos', [BoletoController::class, 'store']);
Route::get('/boletos/{usuario_id}', [BoletoController::class, 'index']);
Route::delete('/boletos/{id}', [BoletoController::class, 'destroy']);
Route::post('/login', [LoginController::class, 'iniciarSesion']);
Route::post('/register', [RegistroController::class, 'registrar']);
