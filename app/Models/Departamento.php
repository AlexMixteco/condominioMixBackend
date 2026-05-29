<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $fillable = [
        'depa',
        'moroso',
    ];

    protected $casts = [
        'moroso' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
