<?php

namespace App\Http\Controllers;

use App\Events\EnviarMensaje;
use App\Events\NuevaNotificacion;
use App\Models\Mensaje;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    public function index()
    {
        $mensajes = Mensaje::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json($mensajes);
    }

    public function store(Request $request)
{
    $request->validate([
        'contenido' => 'required|string|max:1000',
    ]);

    $mensaje = Mensaje::create([
        'user_id'   => auth()->id(),
        'contenido' => $request->contenido,
    ]);

    $mensaje->load('user');

    broadcast(new EnviarMensaje($mensaje))->toOthers();

    // Siempre notificar a todos los demás
    $otrosUsuarios = User::where('id', '!=', auth()->id())->get();

    foreach ($otrosUsuarios as $usuario) {
        $notificacion = Notificacion::create([
            'user_id'     => $usuario->id,
            'tipo'        => 'mensaje',
            'titulo'      => 'Nuevo mensaje de ' . auth()->user()->name,
            'descripcion' => $mensaje->contenido,
            'url'         => '/chat',
        ]);

        broadcast(new NuevaNotificacion($notificacion));
    }

    return response()->json($mensaje, 201);
}
}
