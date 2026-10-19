<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Preguntas extends Model
{
    protected $fillable = [
        'id_encuesta',
        'pregunta',
        'tipo_pregunta',
        'requerido',
    ];

    public function encuesta()
    {
        return $this->belongsTo(Encuesta::class, 'id_encuesta');
    }

    public function opciones()
    {
        return $this->hasMany(Opciones::class, 'id_pregunta');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuestas::class, 'id_pregunta');
    }
}
