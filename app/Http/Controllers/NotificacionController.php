<?php

namespace App\Http\Controllers;

use App\Events\NuevaNotificacion;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{

    public function index()
    {
        $notificaciones = Notificacion::where('user_id', auth()->id())
            ->latest()
            ->take(20)
            ->get();

        return response()->json($notificaciones);
    }


    public function noLeidas()
    {
        $total = Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->count();

        return response()->json(['total' => $total]);
    }


    public function marcarLeida(Notificacion $notificacion)
    {
        if ($notificacion->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $notificacion->update(['leida' => true]);

        return response()->json($notificacion);
    }

    public function marcarTodasLeidas()
    {
        Notificacion::where('user_id', auth()->id())
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['message' => 'Todas marcadas como leídas']);
    }


    public function store(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'tipo'        => 'required|in:mensaje,multa,asamblea,pago_atrasado',
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'url'         => 'nullable|string',
        ]);

        $notificacion = Notificacion::create($request->all());

        broadcast(new NuevaNotificacion($notificacion));

        return response()->json($notificacion, 201);
    }
}
