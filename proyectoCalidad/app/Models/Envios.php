<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Envios extends Model
{
    protected $fillable = [
        'id_encuesta',
        'id_usuario',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'id_encuesta');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuestas::class, 'id_envio');
    }
}
