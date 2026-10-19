<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opciones extends Model
{
    protected $fillable = [
        'id_pregunta',
        'opcion',
        'opcion_correcta',
    ];

    public function pregunta()
    {
        return $this->belongsTo(Preguntas::class, 'id_pregunta');
    }

    public function respuestas()
    {
        return $this->hasMany(Respuestas::class, 'id_opcion');
    }
}
