<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MensajeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/mensajes', [MensajeController::class, 'index']);
    Route::post('/mensajes', [MensajeController::class, 'store']);
});
