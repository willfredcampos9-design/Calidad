@extends('layouts.app')

@section('title', $encuesta->titulo_encuesta)

@section('content')
<div class="container py-4 py-lg-5" style="max-width: 960px;">
    <div class="mb-4">
        <a href="{{ route('envios.index') }}" class="btn btn-link text-decoration-none px-0 mb-3">
            <span aria-hidden="true">←</span> Volver a encuestas disponibles
        </a>

        <div class="rounded-4 p-4 p-md-5 text-white shadow-sm"
             style="background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);">
            <span class="badge rounded-pill bg-white text-primary mb-3">Formulario de encuesta</span>
            <h1 class="h2 fw-bold mb-2">{{ $encuesta->titulo_encuesta }}</h1>
            @if (filled($encuesta->descripcion_encuesta))
                <p class="mb-3 text-white-50">{{ $encuesta->descripcion_encuesta }}</p>
            @else
                <p class="mb-3 text-white-50">Lee cada pregunta y selecciona o escribe tu respuesta.</p>
            @endif
            <div class="d-flex flex-wrap gap-3 small text-white-50">
                <span>▤ {{ $encuesta->preguntas->count() }} {{ $encuesta->preguntas->count() === 1 ? 'pregunta' : 'preguntas' }}</span>
                <span>• Los campos con <span class="text-white fw-bold">*</span> son obligatorios</span>
            </div>
        </div>
    </div>

    @if ($errors->has('encuesta'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first('encuesta') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if ($errors->any() && !$errors->has('encuesta'))
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold mb-1">Revisa tus respuestas</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($encuesta->preguntas->isNotEmpty())
        <form action="{{ route('envios.store') }}" method="POST">
            @csrf
            <input type="hidden" name="id_encuesta" value="{{ $encuesta->id }}">

            @foreach ($encuesta->preguntas as $indice => $pregunta)
                @php
                    $valorAnterior = old('respuestas.' . $pregunta->id);
                    $esObligatoria = (bool) $pregunta->requirido;
                    $errorPregunta = $errors->first('respuestas.' . $pregunta->id);
                @endphp

                <section class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-4 p-md-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width: 42px; height: 42px; background: #eaf2ff; color: #0d6efd;">
                                <span class="fw-bold">{{ $indice + 1 }}</span>
                            </div>

                            <div class="flex-grow-1">
                                <h2 class="h6 fw-bold text-dark mb-2">
                                    {{ $pregunta->pregunta }}
                                    @if ($esObligatoria)
                                        <span class="text-danger" aria-label="Obligatoria">*</span>
                                    @endif
                                </h2>

                                @if ($pregunta->tipo_pregunta === 'multiple')
                                    <p class="small text-secondary mb-3">Puedes seleccionar varias opciones.</p>
                                    <div class="d-grid gap-2">
                                        @foreach ($pregunta->opciones as $opcion)
                                            <label class="border rounded-3 p-3 d-flex align-items-center gap-2"
                                                   style="cursor: pointer;">
                                                <input
                                                    class="form-check-input mt-0"
                                                    type="checkbox"
                                                    name="respuestas[{{ $pregunta->id }}][]"
                                                    value="{{ $opcion->id }}"
                                                    @checked(is_array($valorAnterior) && in_array((string) $opcion->id, array_map('strval', $valorAnterior), true))
                                                >
                                                <span>{{ $opcion->opcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($pregunta->tipo_pregunta === 'unica')
                                    <p class="small text-secondary mb-3">Selecciona una sola opción.</p>
                                    <div class="d-grid gap-2">
                                        @foreach ($pregunta->opciones as $opcion)
                                            <label class="border rounded-3 p-3 d-flex align-items-center gap-2"
                                                   style="cursor: pointer;">
                                                <input
                                                    class="form-check-input mt-0"
                                                    type="radio"
                                                    name="respuestas[{{ $pregunta->id }}]"
                                                    value="{{ $opcion->id }}"
                                                    @checked((string) $valorAnterior === (string) $opcion->id)
                                                >
                                                <span>{{ $opcion->opcion }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif ($pregunta->tipo_pregunta === 'texto')
                                    <label for="respuesta-{{ $pregunta->id }}" class="form-label small text-secondary">
                                        Escribe tu respuesta
                                    </label>
                                    <textarea
                                        class="form-control @if ($errorPregunta) is-invalid @endif"
                                        id="respuesta-{{ $pregunta->id }}"
                                        name="respuestas[{{ $pregunta->id }}]"
                                        rows="4"
                                        placeholder="Escribe aquí..."
                                        @if ($esObligatoria) required @endif
                                    >{{ is_string($valorAnterior) ? $valorAnterior : '' }}</textarea>
                                @else
                                    <div class="alert alert-warning small mb-0">
                                        Esta pregunta tiene un tipo de respuesta no reconocido.
                                    </div>
                                @endif

                                @if ($errorPregunta)
                                    <div class="text-danger small mt-2">{{ $errorPregunta }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>
            @endforeach

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                    <p class="small text-secondary mb-0">
                        Antes de enviar, revisa que tus respuestas estén completas.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <a href="{{ route('envios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            Enviar respuestas <span aria-hidden="true">→</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5 px-4">
                <div class="display-5 mb-3" aria-hidden="true">📝</div>
                <h2 class="h5 fw-bold">Esta encuesta todavía no tiene preguntas</h2>
                <p class="text-secondary mb-3">Vuelve a la lista y selecciona otra encuesta disponible.</p>
                <a href="{{ route('envios.index') }}" class="btn btn-primary">Volver a encuestas</a>
            </div>
        </div>
    @endif
</div>
@endsection
