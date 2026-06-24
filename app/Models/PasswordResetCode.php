<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $table = 'password_reset_codes';

    protected $fillable = [
        'email',
        'codigo',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function estaExpirado(): bool
    {
        return $this->expires_at->isPast();
    }
}
