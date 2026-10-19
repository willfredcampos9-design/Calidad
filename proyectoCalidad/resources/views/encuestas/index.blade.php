@extends('layouts.app')

@section('title', 'Mis encuestas')

@section('content')
<style>

    .encuestas-page { min-height: 80vh; padding: 38px 0 56px; background: #f4f7fc; }

    .encuestas-title { color: #172b4d; font-weight: 750; letter-spacing: -.6px; }

    .encuestas-muted { color: #718096; }

    .encuesta-card { height: 100%; border: 1px solid #e5ebf4; border-radius: 17px; background: #fff; transition: transform .18s ease, box-shadow .18s ease; }

    .encuesta-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(30, 64, 120, .09); }

    .encuesta-icon { display: inline-flex; align-items: center; justify-content: center; width: 46px; height: 46px; border-radius: 13px; color: #245fe7; background: #eaf1ff; font-size: 23px; flex-shrink: 0; }

    .encuesta-card-title { color: #1e293b; font-weight: 700; overflow-wrap: anywhere; }

    .encuesta-description { color: #718096; min-height: 48px; overflow-wrap: anywhere; white-space: pre-line; }

    .encuesta-actions .btn, .encuesta-actions form { flex: 0 0 auto; }

    .btn-encuesta { color: #fff; background: #2563eb; border-color: #2563eb; border-radius: 9px; font-weight: 600; }

    .btn-encuesta:hover { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }

    .btn-soft { color: #245fe7; background: #edf3ff; border-color: #edf3ff; }

    .btn-soft:hover { color: #1d4ed8; background: #dfeaff; border-color: #dfeaff; }

    .encuesta-empty { padding: 54px 22px; border: 1px dashed #cbd5e1; border-radius: 17px; background: #fff; text-align: center; }

    .encuesta-search { max-width: 320px; }

    @media (max-width: 575.98px) { .encuestas-page { pad
    ding-top: 25px; } .encuesta-actions .btn { flex-grow: 1; } }
</style>

<div class="encuestas-page">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <div class="small fw-bold text-primary mb-2">PANEL DE ADMINISTRACIÓN</div>
                <h1 class="encuestas-title h2 mb-2">Mis encuestas</h1>
                <p class="encuestas-muted mb-0">Crea, administra y consulta los resultados de tus encuestas.</p>
            </div>
            <a href="{{ route('encuestas.create') }}" class="btn btn-encuesta px-4 py-2">
                <span aria-hidden="true">＋</span> Crear encuesta
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-3" role="alert">
                <strong>No se pudo completar la operación.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold text-dark">Encuestas registradas</span>
                <span class="badge rounded-pill text-bg-primary">{{ $encuestas->count() }}</span>
            </div>
            @if ($encuestas->isNotEmpty())
                <div class="encuesta-search">
                    <input id="buscar-encuesta" type="search" class="form-control rounded-3"
                           placeholder="Buscar por título..." aria-label="Buscar encuesta">
                </div>
            @endif
        </div>

        @if ($encuestas->isNotEmpty())
            <div class="row g-4" id="lista-encuestas">
                @foreach ($encuestas as $encuesta)
                    <div class="col-12 col-md-6 col-xl-4 encuesta-col">
                        <article class="encuesta-card p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div class="encuesta-icon" aria-hidden="true">▤</div>
                                @if ($encuesta->estado_encuesta)
                                    <span class="badge rounded-pill text-bg-success px-3 py-2">Activa</span>
                                @else
                                    <span class="badge rounded-pill text-bg-secondary px-3 py-2">Inactiva</span>
                                @endif
                            </div>

                            <h2 class="encuesta-card-title h5 mb-2 encuesta-title-search">
                                {{ $encuesta->titulo_encuesta }}
                            </h2>
                            <p class="encuesta-description mb-3">
                                {{ $encuesta->descripcion_encuesta ?: 'Esta encuesta no tiene descripción.' }}
                            </p>

                            @if ($encuesta->created_at)
                                <p class="small text-secondary mb-3">
                                    <span aria-hidden="true">◷</span>
                                    Creada el {{ $encuesta->created_at->format('d/m/Y') }}
                                </p>
                            @endif

                            <hr class="border-light my-3">

                            <div class="encuesta-actions d-flex flex-wrap gap-2">
                                <a href="{{ route('encuestas.show', $encuesta->id) }}"
                                   class="btn btn-encuesta btn-sm px-3">Ver encuesta</a>
                                <a href="{{ route('encuestas.edit', $encuesta->id) }}"
                                   class="btn btn-soft btn-sm px-3">Editar</a>
                                <a href="{{ route('encuestas.resultados', $encuesta->id) }}"
                                   class="btn btn-outline-primary btn-sm px-3">Resultados</a>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <form action="{{ route('encuestas.estado', $encuesta->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded-3">
                                        {{ $encuesta->estado_encuesta ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <form action="{{ route('encuestas.destroy', $encuesta->id) }}" method="POST"
                                      onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta encuesta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @else
            <div class="encuesta-empty">
                <div class="encuesta-icon mx-auto mb-3" aria-hidden="true">▤</div>
                <h2 class="h5 fw-bold mb-2">Todavía no hay encuestas</h2>
                <p class="encuestas-muted mb-4">Crea tu primera encuesta para empezar a recopilar respuestas.</p>
                <a href="{{ route('encuestas.create') }}" class="btn btn-encuesta px-4 py-2">
                    Crear mi primera encuesta
                </a>
            </div>
        @endif

        @if ($encuestas->isNotEmpty())
            <p id="sin-resultados-busqueda" class="text-secondary text-center py-4 d-none">
                No se encontraron encuestas con ese título.
            </p>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buscador = document.getElementById('buscar-encuesta');
        if (!buscador) return;

        buscador.addEventListener('input', function () {
            const termino = this.value.trim().toLocaleLowerCase();
            const tarjetas = document.querySelectorAll('.encuesta-col');
            let visibles = 0;

            tarjetas.forEach(function (tarjeta) {
                const titulo = tarjeta.querySelector('.encuesta-title-search').textContent.toLocaleLowerCase();
                const coincide = titulo.includes(termino);
                tarjeta.classList.toggle('d-none', !coincide);
                if (coincide) visibles++;
            });

            document.getElementById('sin-resultados-busqueda')
                ?.classList.toggle('d-none', visibles !== 0);
        });
    });
</script>
@endsection
