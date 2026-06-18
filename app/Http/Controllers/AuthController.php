<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
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
        'email' => 'required|email',
    ]);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    if ($status === Password::RESET_LINK_SENT) {
        return response()->json([
            'message' => 'Se envió un correo para restablecer tu contraseña.'
        ]);
    }

    return response()->json([
        'message' => 'No encontramos un usuario con ese correo.'
    ], 404);
}


public function resetPassword(Request $request)
{
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password'       => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();


            $user->tokens()->delete();

            event(new PasswordReset($user));
        }
    );

    if ($status === Password::PASSWORD_RESET) {
        return response()->json([
            'message' => 'Contraseña restablecida correctamente. Por seguridad se cerraron todas las sesiones.'
        ]);
    }

    return response()->json([
        'message' => 'El enlace es inválido o ha expirado.'
    ], 400);
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
