<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Multa extends Model
{
    protected $table = 'multas';

    protected $fillable = [
        'departamento_id',
        'motivo',
        'monto',
        'estado',
        'fecha_limite',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
        'monto'        => 'decimal:2',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}
