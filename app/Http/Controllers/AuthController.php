<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

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

    $token = $user->createToken('auth_token')->plainTextToken;

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


    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
