<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MensajeController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\MultaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/broadcasting/auth', function () {
    return Broadcast::auth(request());
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/mensajes', [MensajeController::class, 'index']);
    Route::post('/mensajes', [MensajeController::class, 'store']);

    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::get('/notificaciones/no-leidas', [NotificacionController::class, 'noLeidas']);
    Route::put('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas']);
    Route::put('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida']);
    Route::post('/notificaciones', [NotificacionController::class, 'store']);

    Route::get('/departamentos', [DepartamentoController::class, 'index']);
    Route::post('/departamentos/{departamento}/notificar', [DepartamentoController::class, 'notificar']);
    Route::post('/notificar-todos', [DepartamentoController::class, 'notificarTodos']);

    Route::get('/multas', [MultaController::class, 'index']);
    Route::post('/multas', [MultaController::class, 'store']);
    Route::get('/multas/{multa}', [MultaController::class, 'show']);
    Route::put('/multas/{multa}', [MultaController::class, 'update']);
    Route::delete('/multas/{multa}', [MultaController::class, 'destroy']);
});
