@extends('layouts.app')

@section('title', 'Crear encuesta')

@section('content')
<style>
    .crear-encuesta-page { min-height: 80vh; padding: 36px 0 56px; background: #f4f7fc; }
    .crear-encuesta-card { max-width: 900px; margin: 0 auto; padding: 32px; background: #fff; border: 1px solid #e5ebf4; border-radius: 18px; box-shadow: 0 8px 28px rgba(30,64,120,.06); }
    .crear-title { color: #172b4d; font-weight: 750; letter-spacing: -.5px; }
    .crear-muted { color: #718096; }
    .seccion-formulario { padding: 22px; border: 1px solid #e5ebf4; border-radius: 14px; background: #fbfcff; margin-bottom: 24px; }
    .seccion-formulario h2 { color: #243b5a; font-size: 1.1rem; font-weight: 700; margin-bottom: 18px; }
    .crear-encuesta-page .form-control, .crear-encuesta-page .form-select { padding: 11px 13px; border-color: #dbe3ef; border-radius: 10px; }
    .crear-encuesta-page .form-control:focus, .crear-encuesta-page .form-select:focus { border-color: #60a5fa; box-shadow: 0 0 0 .2rem rgba(37,99,235,.12); }
    .pregunta-card { padding: 22px; margin-bottom: 18px; border: 1px solid #dce6f4; border-left: 4px solid #3b82f6; border-radius: 13px; background: #fff; }
    .pregunta-card legend { float: none; width: auto; margin-bottom: 16px; color: #1e3a5f; font-size: 1rem; font-weight: 700; }
    .opciones-container { padding: 16px; margin: 16px 0; border-radius: 11px; background: #f4f7fc; }
    .opcion-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px; margin-top: 10px; background: #fff; border: 1px solid #e5ebf4; border-radius: 10px; }
    .opcion-row .opcion-texto { flex: 1 1 220px; }
    .btn-primary { background: #2563eb; border-color: #2563eb; }
    .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
    .btn-rounded { border-radius: 9px; }
    .check-label { display: inline-flex; align-items: center; gap: 8px; color: #475569; }
    .check-label .form-check-input { margin: 0; }
    @media (max-width: 575.98px) { .crear-encuesta-card { padding: 20px; } .seccion-formulario { padding: 16px; } .pregunta-card { padding: 16px; } }
</style>

<div class="crear-encuesta-page">
    <div class="container">
        <div class="crear-encuesta-card">
            <a href="{{ route('encuestas.index') }}" class="text-decoration-none small">
                ← Volver a mis encuestas
            </a>

            <div class="mt-4 mb-4">
                <div class="small fw-bold text-primary mb-2">CREADOR DE ENCUESTAS</div>
                <h1 class="crear-title h2 mb-2">Crear encuesta</h1>
                <p class="crear-muted mb-0">Define la información general y agrega las preguntas que necesites.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger rounded-3" role="alert">
                    <strong>Revisa los siguientes datos:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('encuestas.store') }}" method="POST">
                @csrf

                <section class="seccion-formulario">
                    <h2><span class="text-primary me-2">01</span> Información general</h2>

                    <div class="mb-3">
                        <label for="titulo_encuesta" class="form-label fw-semibold">Título de la encuesta</label>
                        <input type="text" id="titulo_encuesta" name="titulo_encuesta"
                               class="form-control @error('titulo_encuesta') is-invalid @enderror"
                               value="{{ old('titulo_encuesta') }}" maxlength="255"
                               placeholder="Ej. Preferencias de formación" required>
                        @error('titulo_encuesta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="descripcion_encuesta" class="form-label fw-semibold">
                            Descripción <span class="text-secondary fw-normal">(opcional)</span>
                        </label>
                        <textarea id="descripcion_encuesta" name="descripcion_encuesta" rows="3"
                                  class="form-control @error('descripcion_encuesta') is-invalid @enderror"
                                  placeholder="Explica brevemente el objetivo de esta encuesta...">{{ old('descripcion_encuesta') }}</textarea>
                        @error('descripcion_encuesta')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <label class="check-label">
                        <input class="form-check-input" type="checkbox" name="anonima" value="1"
                               {{ old('anonima') ? 'checked' : '' }}>
                        <span>Esta encuesta será anónima</span>
                    </label>
                    <div class="form-text">Las respuestas se gestionarán según la configuración de anonimato de tu aplicación.</div>
                </section>

                <section class="seccion-formulario">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                        <div>
                            <h2 class="mb-1"><span class="text-primary me-2">02</span> Preguntas</h2>
                            <p class="crear-muted small mb-0">Agrega preguntas y define el tipo de respuesta.</p>
                        </div>
                        <button type="button" id="agregar-pregunta" class="btn btn-outline-primary btn-rounded">
                            ＋ Agregar pregunta
                        </button>
                    </div>

                    <div id="preguntas-container"></div>
                    <div class="form-text">Las preguntas de selección deben incluir al menos una opción.</div>
                </section>

                <div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
                    <a href="{{ route('encuestas.index') }}" class="btn btn-light border btn-rounded px-4">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-rounded px-4 py-2">
                        Guardar encuesta completa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="plantilla-pregunta">
    <fieldset class="pregunta-card">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <legend class="mb-0">Pregunta</legend>
            <button type="button" class="btn btn-sm btn-outline-danger btn-rounded eliminar-pregunta">
                Eliminar pregunta
            </button>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Enunciado</label>
            <input type="text" class="form-control" data-field="pregunta"
                   placeholder="Escribe aquí tu pregunta" maxlength="200" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Tipo de pregunta</label>
            <select class="form-select" data-field="tipo_pregunta" required>
                <option value="unica">Selección única</option>
                <option value="multiple">Selección múltiple</option>
                <option value="texto">Respuesta abierta</option>
            </select>
        </div>

        <label class="check-label">
            <input type="checkbox" class="form-check-input" data-field="requerido" value="1" checked>
            <span>Pregunta obligatoria</span>
        </label>

        <div class="opciones-container">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <h3 class="h6 fw-bold mb-0">Opciones de respuesta</h3>
                <span class="text-secondary small">Agrega las respuestas posibles</span>
            </div>
            <div class="lista-opciones"></div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded mt-3 agregar-opcion">
                ＋ Agregar opción
            </button>
        </div>
    </fieldset>
</template>

<template id="plantilla-opcion">
    <div class="opcion-row">
        <input type="text" class="form-control opcion-texto" data-field="opcion"
               placeholder="Escribe una opción" maxlength="200" required>
        <button type="button" class="btn btn-sm btn-outline-danger btn-rounded eliminar-opcion">
            Quitar
        </button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const contenedor = document.getElementById('preguntas-container');
    const plantillaPregunta = document.getElementById('plantilla-pregunta');
    const plantillaOpcion = document.getElementById('plantilla-opcion');
    let contadorPreguntas = 0;

    function agregarOpcion(pregunta, numeroPregunta) {
        const lista = pregunta.querySelector('.lista-opciones');
        const numeroOpcion = lista.querySelectorAll('.opcion-row').length;
        const fragmento = plantillaOpcion.content.cloneNode(true);
        const opcion = fragmento.querySelector('.opcion-row');

        const campoOpcion = opcion.querySelector('[data-field="opcion"]');
        campoOpcion.name = `preguntas[${numeroPregunta}][opciones][${numeroOpcion}][opcion]`;

        opcion.querySelector('.eliminar-opcion').addEventListener('click', function () {
            opcion.remove();
            // Renombrar las opciones restantes para mantener índices consecutivos.
            lista.querySelectorAll('.opcion-row').forEach(function (fila, indice) {
                fila.querySelector('[data-field="opcion"]').name =
                    `preguntas[${numeroPregunta}][opciones][${indice}][opcion]`;
            });
        });

        lista.appendChild(fragmento);
    }

    function agregarPregunta() {
        const numero = contadorPreguntas++;
        const fragmento = plantillaPregunta.content.cloneNode(true);
        const pregunta = fragmento.querySelector('.pregunta-card');

        pregunta.querySelector('[data-field="pregunta"]').name =
            `preguntas[${numero}][pregunta]`;
        pregunta.querySelector('[data-field="tipo_pregunta"]').name =
            `preguntas[${numero}][tipo_pregunta]`;
        pregunta.querySelector('[data-field="requerido"]').name =
            `preguntas[${numero}][requerido]`;

        pregunta.querySelector('.agregar-opcion').addEventListener('click', function () {
            agregarOpcion(pregunta, numero);
        });
        pregunta.querySelector('.eliminar-pregunta').addEventListener('click', function () {
            pregunta.remove();
        });

        const tipo = pregunta.querySelector('[data-field="tipo_pregunta"]');

        function actualizarOpciones() {
            const esTexto = tipo.value === 'texto';
            const bloque = pregunta.querySelector('.opciones-container');
            bloque.hidden = esTexto;

            bloque.querySelectorAll('input').forEach(function (input) {
                input.disabled = esTexto;
                if (input.dataset.field === 'opcion') {
                    input.required = !esTexto;
                }
            });
        }

        tipo.addEventListener('change', actualizarOpciones);
        contenedor.appendChild(fragmento);
        actualizarOpciones();

        if (tipo.value !== 'texto') {
            agregarOpcion(pregunta, numero);
            agregarOpcion(pregunta, numero);
        }
    }

    document.getElementById('agregar-pregunta').addEventListener('click', agregarPregunta);
    agregarPregunta();
});
</script>
@endsection
