<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EncuestaController extends Controller
{
    // Mostrar encuestas
    public function index(Request $request)
    {
        $query = Encuesta::query()->latest();
        if (!$request->user()->isAdministrador()) {
            $query->where('id_usuario', $request->user()->id);
        }
        $encuestas = $query->get();

        return view('encuestas.index', compact('encuestas'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        return view('encuestas.create');
    }

    // Guardar una encuesta
    public function store(Request $request)
    {
        
        $data = $request->validate([
            'titulo_encuesta' => 'required|string|max:255',
            'descripcion_encuesta' => 'nullable|string',
            'estado_encuesta' => 'sometimes|boolean',
            'anonima' => 'sometimes|boolean',

            'preguntas' => 'required|array|min:1',
            'preguntas.*.pregunta' => 'required|string|max:200',
            'preguntas.*.tipo_pregunta' => 'required|in:unica,multiple,texto',
            'preguntas.*.requerido' => 'sometimes|boolean',

            'preguntas.*.opciones' => 'sometimes|array',
            'preguntas.*.opciones.*.opcion' => 'required|string|max:200',
        ]);

        foreach ($data['preguntas'] as $pregunta) {
            if (
                $pregunta['tipo_pregunta'] !== 'texto'
                && empty($pregunta['opciones'])
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'preguntas' =>
                            'Cada pregunta de selección debe tener al menos una opción.',
                    ]);
            }
        }

        $encuesta = DB::transaction(function () use ($data, $request) {
            $encuesta = Encuesta::create([
                'id_usuario' => $request->user()->id,
                'titulo_encuesta' => $data['titulo_encuesta'],
                'descripcion_encuesta' => $data['descripcion_encuesta'] ?? null,
                'estado_encuesta' => $data['estado_encuesta'] ?? false,
                'anonima' => $data['anonima'] ?? false,
            ]);

            foreach ($data['preguntas'] as $datosPregunta) {
                $pregunta = $encuesta->preguntas()->create([
                    'pregunta' => $datosPregunta['pregunta'],
                    'tipo_pregunta' => $datosPregunta['tipo_pregunta'],
                    'requirido' => $datosPregunta['requirido'] ?? false,
                ]);

                foreach ($datosPregunta['opciones'] ?? [] as $datosOpcion) {
                    $pregunta->opciones()->create([
                        'opcion' => $datosOpcion['opcion'],
                    ]);
                }
            }

            return $encuesta;
        });

        return redirect()
            ->route('encuestas.show', $encuesta->id)
            ->with('success', 'Encuesta creada correctamente.');

    }

    // Mostrar una encuesta y sus preguntas
    public function show(Encuesta $encuesta)
    {
        $this->authorizeSurvey($encuesta);
        $encuesta->load('user','preguntas.opciones'); // Cargar preguntas y opciones relacionadas

        return view('encuestas.show', compact('encuesta'));
    }

    // Mostrar formulario de edición
    public function edit(Encuesta $encuesta)
    {
        $this->authorizeSurvey($encuesta);
        $encuesta->load(['preguntas' => function ($query) {
            $query->withCount('respuestas')
                ->with(['opciones' => function ($query) {
                    $query->withCount('respuestas');
                }]);
        }]);

        return view('encuestas.edit', compact('encuesta'));
    }

    // Actualizar una encuesta
    public function update(Request $request, Encuesta $encuesta)
    {
        $this->authorizeSurvey($encuesta);
        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'preguntas' => 'required|array|min:1',
            'preguntas.*.id' => 'nullable|integer',
            'preguntas.*.pregunta' => 'required|string|max:200',
            'preguntas.*.tipo_pregunta' => 'required|in:unica,multiple,texto',
            'preguntas.*.requerido' => 'sometimes|boolean',
            'preguntas.*.opciones' => 'sometimes|array',
            'preguntas.*.opciones.*.id' => 'nullable|integer',
            'preguntas.*.opciones.*.opcion' => 'required|string|max:200',
        ]);

        $preguntasExistentes = $encuesta->preguntas()
            ->withCount('respuestas')
            ->with(['opciones' => function ($query) {
                $query->withCount('respuestas');
            }])
            ->get()
            ->keyBy('id');

        $idsPreguntas = [];
        $idsOpciones = [];

        foreach ($data['preguntas'] as $datosPregunta) {
            $idPregunta = $datosPregunta['id'] ?? null;

            if ($idPregunta !== null) {
                if (isset($idsPreguntas[$idPregunta]) || !$preguntasExistentes->has($idPregunta)) {
                    throw ValidationException::withMessages([
                        'preguntas' => 'Una de las preguntas enviadas no pertenece a esta encuesta.',
                    ]);
                }

                $idsPreguntas[$idPregunta] = true;
            }

            if (($datosPregunta['tipo_pregunta'] ?? null) !== 'texto'
                && empty($datosPregunta['opciones'])) {
                throw ValidationException::withMessages([
                    'preguntas' => 'Cada pregunta de selección debe tener al menos una opción.',
                ]);
            }

            $opcionesDePregunta = $idPregunta
                ? $preguntasExistentes->get($idPregunta)->opciones->keyBy('id')
                : collect();

            foreach ($datosPregunta['opciones'] ?? [] as $datosOpcion) {
                $idOpcion = $datosOpcion['id'] ?? null;

                if ($idOpcion === null) {
                    continue;
                }

                if (isset($idsOpciones[$idOpcion]) || !$opcionesDePregunta->has($idOpcion)) {
                    throw ValidationException::withMessages([
                        'preguntas' => 'Una de las opciones enviadas no pertenece a su pregunta.',
                    ]);
                }

                $idsOpciones[$idOpcion] = true;
            }
        }

        DB::transaction(function () use ($data, $encuesta, $preguntasExistentes) {
            $encuesta->update([
                'titulo_encuesta' => $data['titulo'],
                'descripcion_encuesta' => $data['descripcion'] ?? null,
            ]);

            $idsEnviados = collect($data['preguntas'])
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($preguntasExistentes as $preguntaExistente) {
                if (in_array((int) $preguntaExistente->id, $idsEnviados, true)) {
                    continue;
                }

                if ($preguntaExistente->respuestas_count > 0) {
                    continue;
                }

                $preguntaExistente->opciones()->delete();
                $preguntaExistente->delete();
            }

            foreach ($data['preguntas'] as $datosPregunta) {
                $pregunta = !empty($datosPregunta['id'])
                    ? $preguntasExistentes->get($datosPregunta['id'])
                    : $encuesta->preguntas()->make();

                $tieneRespuestas = $pregunta->exists && $pregunta->respuestas_count > 0;

                $pregunta->pregunta = $datosPregunta['pregunta'];
                if (!$tieneRespuestas) {
                    $pregunta->tipo_pregunta = $datosPregunta['tipo_pregunta'];
                }
                $pregunta->requerido = $datosPregunta['requerido'] ?? false;
                $pregunta->save();

                $opcionesExistentes = $pregunta->exists
                    ? $pregunta->opciones()->withCount('respuestas')->get()->keyBy('id')
                    : collect();
                $idsOpcionesEnviadas = [];

                foreach ($datosPregunta['opciones'] ?? [] as $datosOpcion) {
                    if (!empty($datosOpcion['id'])) {
                        $opcion = $opcionesExistentes->get($datosOpcion['id']);
                        $idsOpcionesEnviadas[] = (int) $opcion->id;

                        if ($opcion->respuestas_count > 0) {
                            continue;
                        }
                    } else {
                        $opcion = $pregunta->opciones()->make();
                    }

                    $opcion->opcion = $datosOpcion['opcion'];
                    $opcion->save();
                }

                foreach ($opcionesExistentes as $opcionExistente) {
                    if ($opcionExistente->respuestas_count === 0
                        && !in_array((int) $opcionExistente->id, $idsOpcionesEnviadas, true)) {
                        $opcionExistente->delete();
                    }
                }
            }
        });

        return redirect()
            ->route('encuestas.show', $encuesta)
            ->with('success', 'Encuesta actualizada correctamente.');
    }

    // Eliminar una encuesta
    public function destroy(Encuesta $encuesta)
    {
        $this->authorizeSurvey($encuesta);
        $encuesta->delete();

        return redirect()
            ->route('encuestas.index')
            ->with('success', 'Encuesta eliminada correctamente.');
    }

    public function cambiarEstado(Encuesta $encuesta)
    {
    $this->authorizeSurvey($encuesta);
    $encuesta->estado_encuesta = !$encuesta->estado_encuesta;
    $encuesta->save();

    return redirect()->back()->with(
        'success',
        'Estado de la encuesta actualizado correctamente.'
    );
    }

    private function authorizeSurvey(Encuesta $encuesta): void
    {
        $user = request()->user();

        abort_unless(
            $user && ($user->isAdministrador() || (int) $encuesta->id_usuario === (int) $user->id),
            403,
            'No tienes permiso para gestionar esta encuesta.'
        );
    }
}
