<?php

namespace App\Http\Controllers;

use App\Events\NuevaNotificacion;
use App\Models\Multa;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;

class MultaController extends Controller
{

    public function index()
    {
        $multas = Multa::with('departamento')
            ->latest()
            ->get();

        return response()->json($multas);
    }


    public function store(Request $request)
    {
        $request->validate([
            'departamento_id' => 'required|exists:departamentos,id',
            'motivo'          => 'required|string|max:255',
            'monto'           => 'required|numeric|min:0',
            'estado'          => 'in:pendiente,pagada,cancelada',
            'fecha_limite'    => 'nullable|date',
        ]);

        $multa = Multa::create($request->all());
        $multa->load('departamento');


        $usuarios = User::where('departamento_id', $multa->departamento_id)->get();

        foreach ($usuarios as $usuario) {
            $notificacion = Notificacion::create([
                'user_id'     => $usuario->id,
                'tipo'        => 'multa',
                'titulo'      => 'Nueva multa asignada',
                'descripcion' => $multa->motivo . ' - $' . $multa->monto,
                'url'         => '/multas',
            ]);

            broadcast(new NuevaNotificacion($notificacion));
        }

        return response()->json($multa, 201);
    }


    public function show(Multa $multa)
    {
        return response()->json($multa->load('departamento'));
    }


    public function update(Request $request, Multa $multa)
    {
        $request->validate([
            'departamento_id' => 'exists:departamentos,id',
            'motivo'          => 'string|max:255',
            'monto'           => 'numeric|min:0',
            'estado'          => 'in:pendiente,pagada,cancelada',
            'fecha_limite'    => 'nullable|date',
        ]);

        $multa->update($request->all());

        return response()->json($multa->load('departamento'));
    }


    public function destroy(Multa $multa)
    {
        $multa->delete();

        return response()->json(['message' => 'Multa eliminada']);
    }
}
