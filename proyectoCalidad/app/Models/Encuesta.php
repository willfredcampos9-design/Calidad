<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Encuesta extends Model
{
    protected $fillable = [
        'id_usuario',
        'titulo_encuesta',
        'descripcion_encuesta',
        'estado_encuesta',
        'anonima'
    ];

public function preguntas()
{
    return $this->hasMany(Preguntas::class, 'id_encuesta');
}

public function respuestas()
{
    return $this->hasMany(Respuestas::class, 'id_encuesta');
}

public function envios(): HasMany
{
    return $this->hasMany(Envios::class, 'id_encuesta');
}

public function user()
{
    return $this->belongsTo(User::class, 'id_usuario');
}

}