@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4 px-3 px-md-4" style="max-width: 1440px;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="h4 fw-bold text-dark mb-1">Panel principal</h2>
                <p class="text-secondary mb-0">Resumen general de la plataforma de encuestas</p>
            </div>
            <a href="{{ route('encuestas.create') }}" class="btn btn-primary">＋ Crear encuesta</a>
        </div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        <div class="mb-4">
            <h1 class="h5 fw-bold text-dark mb-1">
                ¡Hola, {{ Auth::user()->name ?? 'bienvenido' }}!
            </h1>
            <p class="text-secondary mb-0">Aquí tienes un resumen de tus encuestas y respuestas.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-secondary small fw-semibold mb-2">Total de encuestas</p>
                                <div class="display-6 fw-bold text-dark">{{ $totalEncuestas ?? 0 }}</div>
                            </div>
                            <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                                <span class="fs-4">📋</span>
                            </div>
                        </div>
                        <a href="{{ route('encuestas.index') }}" class="small text-decoration-none mt-3 d-inline-block">
                            Ver mis encuestas →
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-secondary small fw-semibold mb-2">Encuestas activas</p>
                                <div class="display-6 fw-bold text-success">{{ $encuestasActivas ?? 0 }}</div>
                            </div>
                            <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success">
                                <span class="fs-4">✓</span>
                            </div>
                        </div>
                        <span class="small text-secondary mt-3 d-inline-block">Disponibles para responder</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-secondary small fw-semibold mb-2">Encuestas inactivas</p>
                                <div class="display-6 fw-bold text-secondary">{{ $encuestasInactivas ?? 0 }}</div>
                            </div>
                            <div class="rounded-3 p-3 bg-secondary bg-opacity-10 text-secondary">
                                <span class="fs-4">Ⅱ</span>
                            </div>
                        </div>
                        <span class="small text-secondary mt-3 d-inline-block">No disponibles actualmente</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-secondary small fw-semibold mb-2">Respuestas recibidas</p>
                                <div class="display-6 fw-bold text-info">{{ $totalRespuestas ?? 0 }}</div>
                            </div>
                            <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info">
                                <span class="fs-4">💬</span>
                            </div>
                        </div>
                        <span class="small text-secondary mt-3 d-inline-block">Total de participaciones</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1">Encuestas recientes</h3>
                                <p class="small text-secondary mb-0">Accede rápidamente a las encuestas que has creado.</p>
                            </div>
                            <a href="{{ route('encuestas.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if (isset($encuestasRecientes) && $encuestasRecientes->isNotEmpty())
                            <div class="list-group list-group-flush">
                                @foreach ($encuestasRecientes as $encuesta)
                                    <div class="list-group-item px-0 py-3">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold text-dark">
                                                    {{ $encuesta->titulo_encuesta }}
                                                </div>
                                                <div class="small text-secondary mt-1">
                                                    {{ \Illuminate\Support\Str::limit($encuesta->descripcion_encuesta ?? 'Sin descripción', 100) }}
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($encuesta->estado_encuesta)
                                                    <span class="badge rounded-pill text-bg-success">Activa</span>
                                                @else
                                                    <span class="badge rounded-pill text-bg-secondary">Inactiva</span>
                                                @endif
                                                <a href="{{ route('encuestas.show', $encuesta->id) }}"
                                                   class="btn btn-sm btn-outline-primary">Abrir</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 64px; height: 64px;">
                                    <span class="fs-2">📋</span>
                                </div>
                                <h4 class="h6 fw-bold">Aún no hay encuestas para mostrar</h4>
                                <p class="text-secondary small mb-3">Crea tu primera encuesta para verla aquí.</p>
                                <a href="{{ route('encuestas.create') }}" class="btn btn-primary">Crear primera encuesta</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 px-4 pt-4">
                        <h3 class="h6 fw-bold mb-1">Accesos rápidos</h3>
                        <p class="small text-secondary mb-0">Atajos para las tareas más comunes.</p>
                    </div>
                    <div class="card-body p-4 d-grid gap-2">
                        <a href="{{ route('encuestas.create') }}" class="btn btn-primary text-start py-2">
                            ＋  Crear una encuesta
                        </a>
                        <a href="{{ route('encuestas.index') }}" class="btn btn-outline-primary text-start py-2">
                            ☷  Administrar encuestas
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary">
                                <span class="fs-4">💡</span>
                            </div>
                            <div>
                                <h3 class="h6 fw-bold">Consejo</h3>
                                <p class="small text-secondary mb-0">
                                    Antes de publicar una encuesta, revisa que las preguntas sean claras y que las opciones de respuesta estén completas.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p class="small text-secondary text-center mt-4 mb-0">
            Plataforma de encuestas · Panel de administración
        </p>
    </div>
@endsection
