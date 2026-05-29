<?php

namespace App\Http\Controllers;

use App\Events\NuevaNotificacion;
use App\Models\Departamento;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{

    public function index()
    {
        $departamentos = Departamento::with('usuarios')->get();
        return response()->json($departamentos);
    }


    public function notificar(Request $request, Departamento $departamento)
    {
        $request->validate([
            'tipo'        => 'required|in:mensaje,multa,asamblea,pago_atrasado',
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'url'         => 'nullable|string',
        ]);


        $usuarios = User::where('departamento_id', $departamento->id)->get();

        foreach ($usuarios as $usuario) {
            $notificacion = Notificacion::create([
                'user_id'     => $usuario->id,
                'tipo'        => $request->tipo,
                'titulo'      => $request->titulo,
                'descripcion' => $request->descripcion,
                'url'         => $request->url,
            ]);

            broadcast(new NuevaNotificacion($notificacion));
        }

        return response()->json(['message' => 'Notificación enviada al departamento']);
    }


    public function notificarTodos(Request $request)
    {
        $request->validate([
            'tipo'        => 'required|in:multa,asamblea,pago_atrasado',
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'url'         => 'nullable|string',
        ]);

        $usuarios = User::where('rol', 'residente')->get();

        foreach ($usuarios as $usuario) {
            $notificacion = Notificacion::create([
                'user_id'     => $usuario->id,
                'tipo'        => $request->tipo,
                'titulo'      => $request->titulo,
                'descripcion' => $request->descripcion,
                'url'         => $request->url,
            ]);

            broadcast(new NuevaNotificacion($notificacion));
        }

        return response()->json(['message' => 'Notificación enviada a todos los residentes']);
    }
}
