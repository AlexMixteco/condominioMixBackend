<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Models\PasswordResetCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;

class AuthController extends Controller
{

public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => 'residente',
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Usuario registrado. Revisa tu correo para verificar tu cuenta.'
        ], 201);
    }

    public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::with('departamento')->where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Credenciales incorrectas'
        ], 401);
    }

    if (!$user->hasVerifiedEmail()) {
        return response()->json([
            'message' => 'Debes verificar tu correo antes de iniciar sesión.'
        ], 403);
    }


    $dispositivo = $request->input('dispositivo', $request->userAgent() ?? 'Dispositivo desconocido');

    $token = $user->createToken($dispositivo)->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'           => $user->id,
            'name'         => $user->name,
            'email'        => $user->email,
            'rol'          => $user->rol,
            'departamento' => $user->departamento,
        ]
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }


    public function sesiones(Request $request)
    {
        $tokenActual = $request->user()->currentAccessToken()->id;

        $tokens = $request->user()->tokens()->get()->map(function ($token) use ($tokenActual) {
            return [
                'id'          => $token->id,
                'dispositivo' => $token->name,
                'creado_en'   => $token->created_at,
                'ultimo_uso'  => $token->last_used_at,
                'actual'      => $token->id === $tokenActual,
            ];
        });

        return response()->json($tokens);
    }



public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);


    PasswordResetCode::where('email', $request->email)->delete();


    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);


    PasswordResetCode::create([
        'email'      => $request->email,
        'codigo'     => $codigo,
        'expires_at' => now()->addMinutes(15),
    ]);


    Mail::send([], [], function (Message $message) use ($request, $codigo) {
        $message->to($request->email)
            ->subject('Código de verificación — Condominios Mixteco')
            ->html("
                <div style='font-family: sans-serif; max-width: 400px; margin: 0 auto;'>
                    <h2 style='color: #f97316;'>Restablecer contraseña</h2>
                    <p>Tu código de verificación es:</p>
                    <div style='background: #f1f5f9; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0;'>
                        <span style='font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #1e293b;'>
                            {$codigo}
                        </span>
                    </div>
                    <p style='color: #64748b; font-size: 14px;'>Este código expira en 15 minutos.</p>
                    <p style='color: #64748b; font-size: 14px;'>Si no solicitaste esto, ignora este correo.</p>
                </div>
            ");
    });

    return response()->json([
        'message' => 'Se envió un código de 6 dígitos a tu correo.'
    ]);
}


public function resetPassword(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'codigo'   => 'required|string|size:6',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $resetCode = PasswordResetCode::where('email', $request->email)
        ->where('codigo', $request->codigo)
        ->latest()
        ->first();

    if (!$resetCode) {
        return response()->json([
            'message' => 'El código es incorrecto.'
        ], 400);
    }

    if ($resetCode->estaExpirado()) {
        $resetCode->delete();
        return response()->json([
            'message' => 'El código ha expirado. Solicita uno nuevo.'
        ], 400);
    }

    $user = User::where('email', $request->email)->first();

    $user->update([
        'password' => Hash::make($request->password),
    ]);


    $user->tokens()->delete();


    $resetCode->delete();

    return response()->json([
        'message' => 'Contraseña restablecida correctamente.'
    ]);
}

    public function cerrarSesion(Request $request, $id)
    {
        $request->user()->tokens()->where('id', $id)->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function cerrarTodasSesiones(Request $request)
    {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Todas las sesiones cerradas']);
        }


    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password_nuevo'  => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->password_actual, $request->user()->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta'
            ], 403);
        }

        $request->user()->update([
            'password' => Hash::make($request->password_nuevo),
        ]);


        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Contraseña actualizada. Por seguridad se cerraron todas las sesiones.'
        ]);
    }


    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
