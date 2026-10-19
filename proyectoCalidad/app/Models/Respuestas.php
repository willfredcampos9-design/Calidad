<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respuestas extends Model
{
    protected $fillable = [
        'id_envio',
        'id_pregunta',
        'id_opcion',
        'respuesta',
    ];

    public function envio()
    {
        return $this->belongsTo(Envios::class, 'id_envio');
    }

    public function pregunta()
    {
        return $this->belongsTo(Preguntas::class, 'id_pregunta');
    }

    public function opcion()
    {
        return $this->belongsTo(Opciones::class, 'id_opcion');
    }
}
