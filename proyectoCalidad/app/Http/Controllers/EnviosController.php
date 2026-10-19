<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Encuesta;
use App\Models\Envios;
use App\Models\Preguntas;
use App\Models\Opciones;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Respuestas;

class EnviosController extends Controller
{
    public function index()
    {
        $encuestas = Encuesta::all();

        return view ('envios.index', compact('encuestas'));
    }

    public function create(Encuesta $encuesta)
    {

        $encuesta->load('preguntas.opciones');

        return view('envios.create', compact('encuesta'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_encuesta' => 'required|integer|exists:encuestas,id',
            'respuestas' => 'required|array',
        ]);

        $encuesta = Encuesta::with('preguntas.opciones')
            ->findOrFail($data['id_encuesta']);

        if (!$encuesta->estado_encuesta) {
            abort(403, 'Esta encuesta no está activa.');
        }

        $usuarioId = $request->user()->id;

        // Evitar que el usuario responda dos veces.
        $yaRespondio = Envios::where('id_encuesta', $encuesta->id)
            ->where('id_usuario', $usuarioId)
            ->exists();

        if ($yaRespondio) {
            return back()->withErrors([
                'encuesta' => 'Ya has respondido esta encuesta.',
            ]);
        }

        $respuestas = $data['respuestas'];

        // Validar y guardar todo dentro de una transacción.
        DB::transaction(function () use (
            $encuesta,
            $usuarioId,
            $respuestas
        ) {
            // Comprobar nuevamente antes de crear el envío.
            $yaRespondio = Envios::where('id_encuesta', $encuesta->id)
                ->where('id_usuario', $usuarioId)
                ->exists();

            if ($yaRespondio) {
                throw ValidationException::withMessages([
                    'encuesta' => 'Ya has respondido esta encuesta.',
                ]);
            }

            $envio = Envios::create([
                'id_encuesta' => $encuesta->id,
                'id_usuario' => $usuarioId,
            ]);

            foreach ($encuesta->preguntas as $pregunta) {
                $valor = $respuestas[$pregunta->id] ?? null;

                // Si la pregunta es obligatoria, debe responderse.
                if ($pregunta->requirido && (
                    $valor === null ||
                    $valor === '' ||
                    $valor === []
                )) {
                    throw ValidationException::withMessages([
                        "respuestas.{$pregunta->id}" =>
                            'Debes responder: ' . $pregunta->pregunta,
                    ]);
                }

                // Si no respondieron una pregunta opcional, continuar.
                if (
                    $valor === null ||
                    $valor === '' ||
                    $valor === []
                ) {
                    continue;
                }

                // Pregunta con opciones: permite una o varias selecciones.
                if ($pregunta->opciones->isNotEmpty()) {
                    $seleccionadas = is_array($valor)
                        ? $valor
                        : [$valor];

                    $seleccionadas = array_values(
                        array_unique($seleccionadas)
                    );

                    // Verificar que todas las opciones pertenezcan
                    // a la pregunta que se está respondiendo.
                    $opcionesValidas = $pregunta->opciones
                        ->whereIn('id', $seleccionadas)
                        ->pluck('id')
                        ->map(fn ($id) => (string) $id)
                        ->all();

                    $idsSolicitados = array_map(
                        'strval',
                        $seleccionadas
                    );

                    if (
                        count($opcionesValidas) !== count($idsSolicitados) ||
                        count(array_diff($idsSolicitados, $opcionesValidas)) > 0
                    ) {
                        throw ValidationException::withMessages([
                            "respuestas.{$pregunta->id}" =>
                                'Una de las opciones seleccionadas no es válida.',
                        ]);
                    }

                    // Guardar una fila por cada opción seleccionada.
                    foreach ($seleccionadas as $idOpcion) {
                        Respuestas::create([
                            'id_envio' => $envio->id,
                            'id_pregunta' => $pregunta->id,
                            'id_opcion' => $idOpcion,
                            'respuesta' => null,
                        ]);
                    }
                } else {
                    // Pregunta de texto libre.
                    if (!is_string($valor)) {
                        throw ValidationException::withMessages([
                            "respuestas.{$pregunta->id}" =>
                                'La respuesta debe ser texto.',
                        ]);
                    }

                    Respuestas::create([
                        'id_envio' => $envio->id,
                        'id_pregunta' => $pregunta->id,
                        'id_opcion' => null,
                        'respuesta' => $valor,
                    ]);
                }
            }
        });

        return redirect()
            ->route('envios.index')
            ->with('success', 'Encuesta respondida correctamente.');
    }

    public function show($id)
    {
        $encuesta = Encuesta::findOrFail($id);
        abort_unless(
            auth()->user()->isAdministrador()
                || (int) $encuesta->id_usuario === (int) auth()->id(),
            403,
            'No tienes permiso para consultar estos resultados.'
        );

        $resultados = Respuestas::with('opcion.pregunta.encuesta')
            ->whereHas('opcion.pregunta.encuesta', function ($query) use ($id) {
                $query->where('id', $id);
            })
            ->get();

        return view('resultados.show', compact('resultados'));
    }

    public function responder(Encuesta $encuesta)
    {
        if (!$encuesta->estado_encuesta) {
            abort(403, 'Esta encuesta no está activa.');
        }

        $encuesta->load('preguntas.opciones');

        return view('envios.create', compact('encuesta'));
    }

    public function resultados(Encuesta $encuesta)
    {
        abort_unless(
            auth()->user()->isAdministrador()
                || (int) $encuesta->id_usuario === (int) auth()->id(),
            403,
            'No tienes permiso para ver estos resultados.'
        );

        $preguntas = $encuesta->preguntas()
            ->with('opciones')
            ->get();

        foreach ($preguntas as $pregunta) {
            // Participantes que respondieron esta pregunta
            // mediante alguna de sus opciones.
            $totalParticipantes = Respuestas::where(
                'id_pregunta',
                $pregunta->id
            )
                ->whereNotNull('id_opcion')
                ->distinct()
                ->count('id_envio');

            foreach ($pregunta->opciones as $opcion) {
                $votos = Respuestas::where(
                    'id_pregunta',
                    $pregunta->id
                )
                    ->where('id_opcion', $opcion->id)
                    ->distinct()
                    ->count('id_envio');

                $opcion->votos = $votos;

                $opcion->porcentaje = $totalParticipantes > 0
                    ? round(($votos / $totalParticipantes) * 100, 1)
                    : 0;
            }

            // Respuestas de texto, sin mostrar la identidad
            // de quien respondió.
            $pregunta->respuestasTexto = Respuestas::where(
                'id_pregunta',
                $pregunta->id
            )
                ->whereNotNull('respuesta')
                ->where('respuesta', '!=', '')
                ->pluck('respuesta');
        }

        $envios = $encuesta->anonima
            ? collect()
            : $encuesta->envios()
                ->with('user:id,name,email')
                ->latest()
                ->get();

        return view('resultados.encuesta', compact(
            'encuesta',
            'preguntas',
            'envios'
        ));
    }
}