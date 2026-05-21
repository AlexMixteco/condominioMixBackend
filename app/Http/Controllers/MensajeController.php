<?php

namespace App\Http\Controllers;

use App\Events\EnviarMensaje;
use App\Models\Mensaje;
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

        return response()->json($mensaje, 201);
    }
}
