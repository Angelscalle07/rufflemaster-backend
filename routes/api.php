<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;

Route::post('/register', [RegistroController::class, 'registrar']);
