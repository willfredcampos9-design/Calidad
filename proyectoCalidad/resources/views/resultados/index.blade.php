@extends('layouts.app')

@section('title', 'Resultados de encuestas')

@section('content')
    <div class="container-fluid py-4 px-3 px-md-4" style="max-width: 1440px;">
        <div class="mb-4">
            <p class="small text-primary fw-semibold text-uppercase mb-2">Informes</p>
            <h1 class="h3 fw-bold text-dark mb-2">Resultados de encuestas</h1>
            <p class="text-secondary mb-0">Consulta las respuestas recibidas en tus encuestas.</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <h2 class="h6 fw-bold mb-1">Encuestas disponibles</h2>
                <p class="small text-secondary mb-0">Selecciona una encuesta para revisar sus resultados.</p>
            </div>

            <div class="card-body p-4">
                @if ($encuestas->isEmpty())
                    <div class="text-center py-5">
                        <h3 class="h6 fw-bold text-dark mb-2">No hay encuestas disponibles</h3>
                        <p class="text-secondary small mb-0">Cuando haya encuestas con resultados, aparecerán aquí.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($encuestas as $encuesta)
                            <a href="{{ route('encuestas.resultados', $encuesta) }}"
                               class="list-group-item list-group-item-action px-0 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-dark">{{ $encuesta->titulo_encuesta }}</div>
                                        <div class="small text-secondary mt-1">
                                            {{ \Illuminate\Support\Str::limit($encuesta->descripcion_encuesta ?? 'Sin descripción', 120) }}
                                        </div>
                                    </div>
                                    <span class="btn btn-sm btn-outline-primary">Ver resultados</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection