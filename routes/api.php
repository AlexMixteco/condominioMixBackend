<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MensajeController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\MultaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\UsuarioController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/broadcasting/auth', function () {
    return Broadcast::auth(request());
})->middleware('auth:sanctum');


Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return response()->json(['message' => 'Enlace inválido'], 403);
    }

    if (!$request->hasValidSignature()) {
        return response()->json(['message' => 'Enlace expirado'], 403);
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return response()->json(['message' => 'Correo verificado correctamente']);
})->middleware('signed')->name('verification.verify');

Route::post('/email/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response()->json(['message' => 'Correo de verificación reenviado']);
})->middleware(['auth:sanctum', 'throttle:6,1']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/mensajes', [MensajeController::class, 'index']);
    Route::post('/mensajes', [MensajeController::class, 'store']);

    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::get('/notificaciones/no-leidas', [NotificacionController::class, 'noLeidas']);
    Route::put('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodasLeidas']);
    Route::put('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida']);

    Route::get('/departamentos', [DepartamentoController::class, 'index']);
});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/notificaciones', [NotificacionController::class, 'store']);
    Route::post('/departamentos/{departamento}/notificar', [DepartamentoController::class, 'notificar']);
    Route::post('/notificar-todos', [DepartamentoController::class, 'notificarTodos']);

    Route::get('/multas', [MultaController::class, 'index']);
    Route::post('/multas', [MultaController::class, 'store']);
    Route::get('/multas/{multa}', [MultaController::class, 'show']);
    Route::put('/multas/{multa}', [MultaController::class, 'update']);
    Route::delete('/multas/{multa}', [MultaController::class, 'destroy']);


    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show']);
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy']);
});
