@extends('layouts.app')

@section('title', 'Responder encuestas')

@section('content')
<div class="container py-4 py-lg-5" style="max-width: 1200px;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary mb-2">Zona de participación</span>
            <h1 class="h2 fw-bold text-dark mb-2">Encuestas disponibles</h1>
            <p class="text-secondary mb-0">Consulta las encuestas y comparte tu opinión.</p>
        </div>

        <div class="text-secondary small">
            Encuestas registradas: <span class="fw-bold text-dark">{{ $encuestas->count() }}</span>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-md-4">
            <label for="buscarEncuesta" class="form-label small fw-semibold text-secondary">Buscar encuesta</label>
            <div class="input-group">
                <span class="input-group-text bg-white">⌕</span>
                <input
                    type="search"
                    class="form-control"
                    id="buscarEncuesta"
                    placeholder="Buscar por título o descripción..."
                    autocomplete="off"
                >
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if ($errors->has('encuesta'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ $errors->first('encuesta') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row g-3" id="listaEncuestas">
        @forelse ($encuestas as $encuesta)
            <div
                class="col-12 col-md-6 col-xl-4 tarjeta-encuesta"
                data-texto="{{ \Illuminate\Support\Str::lower($encuesta->titulo_encuesta . ' ' . ($encuesta->descripcion_encuesta ?? '')) }}"
            >
                <article class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width: 48px; height: 48px; background: #eaf2ff; color: #0d6efd;">
                                <span class="fs-4" aria-hidden="true">▤</span>
                            </div>

                            @if ($encuesta->estado_encuesta)
                                <span class="badge rounded-pill text-bg-success">Activa</span>
                            @else
                                <span class="badge rounded-pill text-bg-secondary">Inactiva</span>
                            @endif
                        </div>

                        <h2 class="h5 fw-bold text-dark mb-2">{{ $encuesta->titulo_encuesta }}</h2>
                        <p class="text-secondary mb-4 flex-grow-1">
                            {{ filled($encuesta->descripcion_encuesta) ? $encuesta->descripcion_encuesta : 'Esta encuesta no tiene una descripción.' }}
                        </p>

                        <div class="border-top pt-3 mt-auto">
                            @if ($encuesta->estado_encuesta)
                                <a href="{{ route('envios.responder', $encuesta->id) }}"
                                   class="btn btn-primary w-100">
                                    Responder encuesta <span aria-hidden="true">→</span>
                                </a>
                            @else
                                <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                    No disponible
                                </button>
                                <p class="small text-secondary text-center mt-2 mb-0">
                                    Esta encuesta no está disponible para responder.
                                </p>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5 px-4">
                        <div class="display-5 mb-3" aria-hidden="true">📝</div>
                        <h2 class="h5 fw-bold">No hay encuestas registradas</h2>
                        <p class="text-secondary mb-0">Cuando haya encuestas disponibles, aparecerán aquí.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div id="sinResultadosBusqueda" class="card border-0 shadow-sm rounded-4 mt-3 d-none">
        <div class="card-body text-center py-5">
            <h2 class="h6 fw-bold">No encontramos coincidencias</h2>
            <p class="text-secondary mb-0">Prueba con otro título o palabra clave.</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buscador = document.getElementById('buscarEncuesta');
        const tarjetas = Array.from(document.querySelectorAll('.tarjeta-encuesta'));
        const mensajeVacio = document.getElementById('sinResultadosBusqueda');

        if (!buscador) return;

        buscador.addEventListener('input', function () {
            const termino = this.value.trim().toLocaleLowerCase();
            let visibles = 0;

            tarjetas.forEach(function (tarjeta) {
                const texto = tarjeta.dataset.texto || '';
                const coincide = texto.includes(termino);
                tarjeta.classList.toggle('d-none', !coincide);
                if (coincide) visibles++;
            });

            if (mensajeVacio) {
                mensajeVacio.classList.toggle('d-none', visibles > 0 || tarjetas.length === 0);
            }
        });
    });
</script>
@endsection
