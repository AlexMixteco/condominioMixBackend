<?php

namespace App\Events;

use App\Models\Mensaje;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnviarMensaje implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Mensaje $mensaje) {}

    public function broadcastOn(): array
    {
        return [new Channel('chat.condominio')];
    }

    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->mensaje->id,
            'contenido'  => $this->mensaje->contenido,
            'user'       => [
                'id'   => $this->mensaje->user->id,
                'name' => $this->mensaje->user->name,
            ],
            'created_at' => $this->mensaje->created_at,
        ];
    }
}

