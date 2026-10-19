@extends('layouts.app')

@section('title', $encuesta->titulo_encuesta)

@section('content')
<style>
    .detalle-encuesta-page { min-height: 80vh; padding: 38px 0 56px; background: #f4f7fc; }
    .detalle-encuesta-title { color: #172b4d; font-weight: 750; overflow-wrap: anywhere; }
    .detalle-encuesta-muted { color: #718096; }
    .detalle-panel { border: 1px solid #e5ebf4; border-radius: 14px; background: #fff; }
    .detalle-pregunta { height: 100%; border: 1px solid #dce6f4; border-left: 4px solid #3b82f6; border-radius: 13px; background: #fff; }
    .detalle-opcion { overflow-wrap: anywhere; }
    .btn-encuesta { color: #fff; background: #2563eb; border-color: #2563eb; border-radius: 9px; font-weight: 600; }
    .btn-encuesta:hover { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }
    .btn-soft { color: #245fe7; background: #edf3ff; border-color: #edf3ff; border-radius: 9px; }
    .btn-soft:hover { color: #1d4ed8; background: #dfeaff; border-color: #dfeaff; }
</style>

<div class="detalle-encuesta-page">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
            <div class="flex-grow-1">
                <a href="{{ route('encuestas.index') }}" class="text-decoration-none small">← Volver a mis encuestas</a>
                <div class="small fw-bold text-primary mt-4 mb-2">DETALLES DE LA ENCUESTA</div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h1 class="detalle-encuesta-title h2 mb-0">{{ $encuesta->titulo_encuesta }}</h1>
                    @if ($encuesta->estado_encuesta)
                        <span class="badge rounded-pill text-bg-success px-3 py-2">Activa</span>
                    @else
                        <span class="badge rounded-pill text-bg-secondary px-3 py-2">Inactiva</span>
                    @endif
                </div>
                <p class="detalle-encuesta-muted mb-0">
                    {{ $encuesta->descripcion_encuesta ?: 'Esta encuesta no tiene descripción.' }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('encuestas.edit', $encuesta) }}" class="btn btn-soft px-3">Editar</a>
                <a href="{{ route('encuestas.resultados', $encuesta) }}" class="btn btn-encuesta px-3">Ver resultados</a>
            </div>
        </div>

        <section class="detalle-panel p-4 mb-4">
            <h2 class="h6 fw-bold text-dark mb-3">Información</h2>
            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <div class="small text-secondary mb-1">Creada por</div>
                    <div class="fw-semibold text-dark">{{ $encuesta->user?->name ?? 'Usuario no disponible' }}</div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="small text-secondary mb-1">Fecha de creación</div>
                    <div class="fw-semibold text-dark">
                        {{ $encuesta->created_at?->format('d/m/Y') ?? 'No disponible' }}
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="small text-secondary mb-1">Privacidad</div>
                    <div class="fw-semibold text-dark">{{ $encuesta->anonima ? 'Anónima' : 'Identificada' }}</div>
                </div>
                <div class="col-12 col-sm-6">
                    <div class="small text-secondary mb-1">Preguntas</div>
                    <div class="fw-semibold text-dark">{{ $encuesta->preguntas->count() }}</div>
                </div>
            </div>
        </section>

        <div class="d-flex align-items-center gap-2 mb-3">
            <h2 class="h5 fw-bold text-dark mb-0">Preguntas</h2>
            <span class="badge rounded-pill text-bg-primary">{{ $encuesta->preguntas->count() }}</span>
        </div>

        @if ($encuesta->preguntas->isNotEmpty())
            <div class="row g-3">
                @foreach ($encuesta->preguntas as $pregunta)
                    <div class="col-12">
                        <article class="detalle-pregunta p-4">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2 mb-3">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="badge rounded-pill text-bg-primary mt-1">{{ $loop->iteration }}</span>
                                    <h3 class="h6 fw-bold text-dark mb-0">{{ $pregunta->pregunta }}</h3>
                                </div>
                                <div class="d-flex flex-wrap gap-2 ms-sm-auto">
                                    <span class="badge text-bg-light border text-secondary">
                                        {{ ['unica' => 'Selección única', 'multiple' => 'Selección múltiple', 'texto' => 'Respuesta abierta'][$pregunta->tipo_pregunta] ?? $pregunta->tipo_pregunta }}
                                    </span>
                                    <span class="badge {{ $pregunta->requerido ? 'text-bg-primary' : 'text-bg-secondary' }}">
                                        {{ $pregunta->requerido ? 'Obligatoria' : 'Opcional' }}
                                    </span>
                                </div>
                            </div>

                            @if ($pregunta->opciones->isNotEmpty())
                                <div class="list-group list-group-flush">
                                    @foreach ($pregunta->opciones as $opcion)
                                        <div class="list-group-item detalle-opcion d-flex justify-content-between align-items-center gap-3 px-0 py-2">
                                            <span>{{ $opcion->opcion }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif ($pregunta->tipo_pregunta === 'texto')
                                <p class="small text-secondary mb-0">La respuesta se recibe como texto libre.</p>
                            @else
                                <p class="small text-secondary mb-0">Esta pregunta todavía no tiene opciones.</p>
                            @endif
                        </article>
                    </div>
                @endforeach
            </div>
        @else
            <div class="detalle-panel text-center py-5 px-3">
                <h3 class="h6 fw-bold text-dark mb-2">Esta encuesta todavía no tiene preguntas</h3>
                <p class="detalle-encuesta-muted small mb-3">Agrega preguntas para completar la encuesta.</p>
                <a href="{{ route('encuestas.edit', $encuesta) }}" class="btn btn-encuesta px-4">Editar encuesta</a>
            </div>
        @endif
    </div>
</div>

@endsection