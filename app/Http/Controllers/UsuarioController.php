<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{

   public function index()
{
    $usuarios = User::with('departamento')
        ->latest()
        ->get()
        ->map(function ($usuario) {
            return [
                'id'                => $usuario->id,
                'name'              => $usuario->name,
                'email'             => $usuario->email,
                'email_verified_at' => $usuario->email_verified_at,
                'rol'               => $usuario->rol,
                'departamento_id'   => $usuario->departamento_id,
                'departamento'      => $usuario->departamento,
                'created_at'        => $usuario->created_at,
            ];
        });

    return response()->json($usuarios);
}


    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8',
            'rol'             => 'required|in:admin,residente',
            'departamento_id' => 'nullable|exists:departamentos,id',
        ]);

        $usuario = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'rol'             => $request->rol,
            'departamento_id' => $request->departamento_id,
        ]);


        event(new Registered($usuario));

        return response()->json($usuario->load('departamento'), 201);
    }


    public function show(User $usuario)
    {
        return response()->json($usuario->load('departamento'));
    }


    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'            => 'string|max:255',
            'email'           => 'email|unique:users,email,' . $usuario->id,
            'password'        => 'nullable|string|min:8',
            'rol'             => 'in:admin,residente',
            'departamento_id' => 'nullable|exists:departamentos,id',
        ]);

        $datos = $request->only(['name', 'email', 'rol', 'departamento_id']);

        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }

        $usuario->update($datos);

        return response()->json($usuario->load('departamento'));
    }


    public function destroy(User $usuario)
    {
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado']);
    }
}
