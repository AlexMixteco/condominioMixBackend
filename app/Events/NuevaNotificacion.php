<?php

namespace App\Events;

use App\Models\Notificacion;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NuevaNotificacion implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notificacion $notificacion) {}

    public function broadcastOn(): array
    {

        return [
            new PrivateChannel('notificaciones.' . $this->notificacion->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'nueva.notificacion';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->notificacion->id,
            'tipo'        => $this->notificacion->tipo,
            'titulo'      => $this->notificacion->titulo,
            'descripcion' => $this->notificacion->descripcion,
            'url'         => $this->notificacion->url,
            'leida'       => $this->notificacion->leida,
            'created_at'  => $this->notificacion->created_at,
        ];
    }
}
