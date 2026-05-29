<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'descripcion',
        'url',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
