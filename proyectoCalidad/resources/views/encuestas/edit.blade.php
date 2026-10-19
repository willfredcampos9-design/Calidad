@extends('layouts.app')

@section('title', 'Editar encuesta')

@section('content')
@php
	$preguntasFormulario = old('preguntas', $encuesta->preguntas->map(function ($pregunta) {
		return [
			'id' => $pregunta->id,
			'pregunta' => $pregunta->pregunta,
			'tipo_pregunta' => $pregunta->tipo_pregunta,
			'requerido' => $pregunta->requerido,
			'opciones' => $pregunta->opciones->map(function ($opcion) {
				return [
					'id' => $opcion->id,
					'opcion' => $opcion->opcion,
				];
			})->all(),
		];
	})->all());
@endphp

<style>
	.editar-encuesta-page { min-height: 80vh; padding: 36px 0 56px; background: #f4f7fc; }
	.editar-encuesta-card { max-width: 900px; margin: 0 auto; padding: 32px; background: #fff; border: 1px solid #e5ebf4; border-radius: 18px; box-shadow: 0 8px 28px rgba(30,64,120,.06); }
	.editar-title { color: #172b4d; font-weight: 750; letter-spacing: -.5px; }
	.editar-muted { color: #718096; }
	.seccion-formulario { padding: 22px; border: 1px solid #e5ebf4; border-radius: 14px; background: #fbfcff; }
	.seccion-formulario h2 { color: #243b5a; font-size: 1.1rem; font-weight: 700; margin-bottom: 18px; }
	.editar-encuesta-page .form-control { padding: 11px 13px; border-color: #dbe3ef; border-radius: 10px; }
	.editar-encuesta-page .form-control:focus { border-color: #60a5fa; box-shadow: 0 0 0 .2rem rgba(37,99,235,.12); }
	.editar-encuesta-page .btn-primary { background: #2563eb; border-color: #2563eb; }
	.editar-encuesta-page .btn-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
	.pregunta-card { padding: 22px; margin-bottom: 18px; border: 1px solid #dce6f4; border-left: 4px solid #3b82f6; border-radius: 13px; background: #fff; }
	.pregunta-card legend { float: none; width: auto; color: #1e3a5f; font-size: 1rem; font-weight: 700; }
	.opciones-container { padding: 16px; margin: 16px 0; border-radius: 11px; background: #f4f7fc; }
	.opcion-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 10px; margin-top: 10px; background: #fff; border: 1px solid #e5ebf4; border-radius: 10px; }
	.opcion-row .opcion-texto { flex: 1 1 220px; }
	.btn-rounded { border-radius: 9px; }
	.check-label { display: inline-flex; align-items: center; gap: 8px; color: #475569; }
	.check-label .form-check-input { margin: 0; }
	@media (max-width: 575.98px) { .editar-encuesta-card { padding: 20px; } .seccion-formulario, .pregunta-card { padding: 16px; } }
</style>

<div class="editar-encuesta-page">
	<div class="container">
		<div class="editar-encuesta-card">
			<a href="{{ route('encuestas.show', $encuesta) }}" class="text-decoration-none small">
				← Volver a la encuesta
			</a>

			<div class="mt-4 mb-4">
				<div class="small fw-bold text-primary mb-2">GESTIÓN DE ENCUESTAS</div>
				<h1 class="editar-title h2 mb-2">Editar encuesta</h1>
				<p class="editar-muted mb-0">Actualiza la información general de “{{ $encuesta->titulo_encuesta }}”.</p>
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

			<form action="{{ route('encuestas.update', $encuesta) }}" method="POST">
				@csrf
				@method('PUT')

				<section class="seccion-formulario mb-4">
					<h2><span class="text-primary me-2">01</span> Información general</h2>

					<div class="mb-3">
						<label for="titulo" class="form-label fw-semibold">Título de la encuesta</label>
						<input type="text" id="titulo" name="titulo"
							   class="form-control @error('titulo') is-invalid @enderror"
							   value="{{ old('titulo', $encuesta->titulo_encuesta) }}"
							   maxlength="255" placeholder="Ej. Preferencias de formación" required>
						@error('titulo')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>

					<div>
						<label for="descripcion" class="form-label fw-semibold">
							Descripción <span class="text-secondary fw-normal">(opcional)</span>
						</label>
						<textarea id="descripcion" name="descripcion" rows="4"
								  class="form-control @error('descripcion') is-invalid @enderror"
								  placeholder="Explica brevemente el objetivo de esta encuesta...">{{ old('descripcion', $encuesta->descripcion_encuesta) }}</textarea>
						@error('descripcion')
							<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
				</section>

				<section class="seccion-formulario mb-4">
					<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
						<div>
							<h2 class="mb-1"><span class="text-primary me-2">02</span> Preguntas</h2>
							<p class="editar-muted small mb-0">Edita las preguntas y administra sus opciones de respuesta.</p>
						</div>
						<button type="button" id="agregar-pregunta" class="btn btn-outline-primary btn-rounded">
							＋ Agregar pregunta
						</button>
					</div>

					<div id="preguntas-container">
						@foreach ($preguntasFormulario as $indicePregunta => $datosPregunta)
							@php
								$preguntaPersistida = !empty($datosPregunta['id'])
									? $encuesta->preguntas->firstWhere('id', (int) $datosPregunta['id'])
									: null;
								$preguntaBloqueada = $preguntaPersistida && $preguntaPersistida->respuestas_count > 0;
								$tipoPregunta = $preguntaBloqueada
									? $preguntaPersistida->tipo_pregunta
									: ($datosPregunta['tipo_pregunta'] ?? 'unica');
							@endphp

							<fieldset class="pregunta-card" data-question-card data-question-index="{{ $indicePregunta }}">
								<input type="hidden" data-field="id" name="preguntas[{{ $indicePregunta }}][id]"
								       value="{{ $datosPregunta['id'] ?? '' }}">
								<div class="d-flex justify-content-between align-items-start gap-2 mb-3">
									<legend class="mb-0">Pregunta {{ $loop->iteration }}</legend>
									<button type="button" class="btn btn-sm btn-outline-danger btn-rounded eliminar-pregunta"
									        @disabled($preguntaBloqueada)
									        title="{{ $preguntaBloqueada ? 'No se puede eliminar porque tiene respuestas registradas.' : 'Eliminar pregunta' }}">
										Eliminar pregunta
									</button>
								</div>

									@if ($preguntaBloqueada)
										<p class="small text-warning-emphasis mb-3">Esta pregunta ya tiene respuestas; su tipo y eliminación están protegidos.</p>
									@endif

									<div class="mb-3">
										<label class="form-label fw-semibold">Enunciado</label>
										<input type="text" class="form-control" data-field="pregunta"
										       name="preguntas[{{ $indicePregunta }}][pregunta]"
										       value="{{ $datosPregunta['pregunta'] ?? '' }}"
										       placeholder="Escribe aquí tu pregunta" maxlength="200" required>
									</div>

									<div class="mb-3">
										<label class="form-label fw-semibold">Tipo de pregunta</label>
										<select class="form-select" data-field="tipo_pregunta"
										        @unless ($preguntaBloqueada) name="preguntas[{{ $indicePregunta }}][tipo_pregunta]" @endunless
										        @disabled($preguntaBloqueada) required>
											<option value="unica" @selected($tipoPregunta === 'unica')>Selección única</option>
											<option value="multiple" @selected($tipoPregunta === 'multiple')>Selección múltiple</option>
											<option value="texto" @selected($tipoPregunta === 'texto')>Respuesta abierta</option>
										</select>
										@if ($preguntaBloqueada)
											<input type="hidden" data-field="tipo_pregunta_bloqueada"
											       name="preguntas[{{ $indicePregunta }}][tipo_pregunta]" value="{{ $tipoPregunta }}">
										@endif
									</div>

									<input type="hidden" data-field="requerido-vacio"
									       name="preguntas[{{ $indicePregunta }}][requerido]" value="0">
									<label class="check-label">
										<input type="checkbox" class="form-check-input" data-field="requerido"
										       name="preguntas[{{ $indicePregunta }}][requerido]" value="1"
										       @checked(!empty($datosPregunta['requerido']))>
										<span>Pregunta obligatoria</span>
									</label>

									<div class="opciones-container" @if ($tipoPregunta === 'texto') hidden @endif>
										<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
											<h3 class="h6 fw-bold mb-0">Opciones de respuesta</h3>
											<span class="text-secondary small">Las opciones con respuestas registradas están protegidas.</span>
										</div>
										<div class="lista-opciones">
											@foreach (($datosPregunta['opciones'] ?? []) as $indiceOpcion => $datosOpcion)
												@php
													$opcionPersistida = $preguntaPersistida && !empty($datosOpcion['id'])
														? $preguntaPersistida->opciones->firstWhere('id', (int) $datosOpcion['id'])
														: null;
													$opcionBloqueada = $opcionPersistida && $opcionPersistida->respuestas_count > 0;
													$valorOpcion = $opcionBloqueada ? $opcionPersistida->opcion : ($datosOpcion['opcion'] ?? '');
												@endphp
												<div class="opcion-row" data-option-row data-protected="{{ $opcionBloqueada ? 'true' : 'false' }}">
													<input type="hidden" data-field="id" name="preguntas[{{ $indicePregunta }}][opciones][{{ $indiceOpcion }}][id]"
													       value="{{ $datosOpcion['id'] ?? '' }}">
													<input type="text" class="form-control opcion-texto" data-field="opcion"
													       name="preguntas[{{ $indicePregunta }}][opciones][{{ $indiceOpcion }}][opcion]"
													       value="{{ $valorOpcion }}" placeholder="Escribe una opción"
													       maxlength="200" required @readonly($opcionBloqueada)
													       @disabled($tipoPregunta === 'texto')>
													@if ($opcionBloqueada)
														<span class="badge text-bg-warning">Con respuestas</span>
													@endif
													<button type="button" class="btn btn-sm btn-outline-danger btn-rounded eliminar-opcion"
													        @disabled($opcionBloqueada)
													        title="{{ $opcionBloqueada ? 'No se puede eliminar porque tiene respuestas registradas.' : 'Quitar opción' }}">
														Quitar
													</button>
												</div>
											@endforeach
										</div>
										<button type="button" class="btn btn-sm btn-outline-primary btn-rounded mt-3 agregar-opcion">
											＋ Agregar opción
										</button>
									</div>
							</fieldset>
						@endforeach
					</div>
					<div class="form-text">Las preguntas de selección deben tener al menos una opción.</div>
				</section>

				<div class="d-flex flex-column flex-sm-row justify-content-end gap-2">
					<a href="{{ route('encuestas.show', $encuesta) }}" class="btn btn-light border btn-rounded px-4">
						Cancelar
					</a>
					<button type="submit" class="btn btn-primary btn-rounded px-4 py-2">
						Guardar cambios
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<template id="plantilla-pregunta">
	<fieldset class="pregunta-card" data-question-card>
		<input type="hidden" data-field="id" value="">
		<div class="d-flex justify-content-between align-items-start gap-2 mb-3">
			<legend class="mb-0">Nueva pregunta</legend>
			<button type="button" class="btn btn-sm btn-outline-danger btn-rounded eliminar-pregunta">Eliminar pregunta</button>
		</div>
		<div class="mb-3">
			<label class="form-label fw-semibold">Enunciado</label>
			<input type="text" class="form-control" data-field="pregunta" placeholder="Escribe aquí tu pregunta" maxlength="200" required>
		</div>
		<div class="mb-3">
			<label class="form-label fw-semibold">Tipo de pregunta</label>
			<select class="form-select" data-field="tipo_pregunta" required>
				<option value="unica">Selección única</option>
				<option value="multiple">Selección múltiple</option>
				<option value="texto">Respuesta abierta</option>
			</select>
		</div>
		<input type="hidden" data-field="requerido-vacio" value="0">
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
			<button type="button" class="btn btn-sm btn-outline-primary btn-rounded mt-3 agregar-opcion">＋ Agregar opción</button>
		</div>
	</fieldset>
</template>

<template id="plantilla-opcion">
	<div class="opcion-row" data-option-row data-protected="false">
		<input type="hidden" data-field="id" value="">
		<input type="text" class="form-control opcion-texto" data-field="opcion" placeholder="Escribe una opción" maxlength="200" required>
		<button type="button" class="btn btn-sm btn-outline-danger btn-rounded eliminar-opcion">Quitar</button>
	</div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const contenedor = document.getElementById('preguntas-container');
	const plantillaPregunta = document.getElementById('plantilla-pregunta');
	const plantillaOpcion = document.getElementById('plantilla-opcion');
	let contadorPreguntas = Array.from(contenedor.querySelectorAll('[data-question-index]'))
		.reduce((mayor, pregunta) => Math.max(mayor, Number(pregunta.dataset.questionIndex) + 1), 0);

	function renombrarOpciones(pregunta, numeroPregunta) {
		pregunta.querySelectorAll('[data-option-row]').forEach(function (fila, numeroOpcion) {
			fila.querySelector('[data-field="id"]').name =
				`preguntas[${numeroPregunta}][opciones][${numeroOpcion}][id]`;
			fila.querySelector('[data-field="opcion"]').name =
				`preguntas[${numeroPregunta}][opciones][${numeroOpcion}][opcion]`;
		});
	}

	function renombrarPregunta(pregunta, numeroPregunta) {
		pregunta.dataset.questionIndex = numeroPregunta;
		pregunta.querySelector('[data-field="id"]').name = `preguntas[${numeroPregunta}][id]`;
		pregunta.querySelector('[data-field="pregunta"]').name = `preguntas[${numeroPregunta}][pregunta]`;
		pregunta.querySelector('[data-field="requerido-vacio"]').name =
			`preguntas[${numeroPregunta}][requerido]`;
		pregunta.querySelector('[data-field="requerido"]').name =
			`preguntas[${numeroPregunta}][requerido]`;

		const tipo = pregunta.querySelector('[data-field="tipo_pregunta"]');
		const tipoBloqueado = pregunta.querySelector('[data-field="tipo_pregunta_bloqueada"]');
		if (tipoBloqueado) {
			tipoBloqueado.name = `preguntas[${numeroPregunta}][tipo_pregunta]`;
		} else {
			tipo.name = `preguntas[${numeroPregunta}][tipo_pregunta]`;
		}

		renombrarOpciones(pregunta, numeroPregunta);
	}

	function configurarOpcion(pregunta, fila) {
		fila.querySelector('.eliminar-opcion').addEventListener('click', function () {
			if (fila.dataset.protected === 'true') {
				return;
			}

			fila.remove();
			renombrarOpciones(pregunta, pregunta.dataset.questionIndex);
		});
	}

	function agregarOpcion(pregunta) {
		const fragmento = plantillaOpcion.content.cloneNode(true);
		const fila = fragmento.querySelector('[data-option-row]');
		pregunta.querySelector('.lista-opciones').appendChild(fragmento);
		configurarOpcion(pregunta, fila);
		renombrarOpciones(pregunta, pregunta.dataset.questionIndex);
	}

	function actualizarOpciones(pregunta) {
		const esTexto = pregunta.querySelector('[data-field="tipo_pregunta"]').value === 'texto';
		const bloque = pregunta.querySelector('.opciones-container');
		bloque.hidden = esTexto;
		bloque.querySelector('.agregar-opcion').disabled = esTexto;

		bloque.querySelectorAll('[data-option-row]').forEach(function (fila) {
			fila.querySelectorAll('input').forEach(function (campo) {
				campo.disabled = esTexto;
			});
			fila.querySelector('[data-field="opcion"]').required = !esTexto;
		});
	}

	function configurarPregunta(pregunta) {
		pregunta.querySelector('.agregar-opcion').addEventListener('click', function () {
			agregarOpcion(pregunta);
		});
		pregunta.querySelector('.eliminar-pregunta').addEventListener('click', function () {
			if (!pregunta.querySelector('.eliminar-pregunta').disabled) {
				pregunta.remove();
			}
		});
		pregunta.querySelector('[data-field="tipo_pregunta"]').addEventListener('change', function () {
			actualizarOpciones(pregunta);
		});
		pregunta.querySelectorAll('[data-option-row]').forEach(function (fila) {
			configurarOpcion(pregunta, fila);
		});
		actualizarOpciones(pregunta);
	}

	function agregarPregunta() {
		const numeroPregunta = contadorPreguntas++;
		const fragmento = plantillaPregunta.content.cloneNode(true);
		const pregunta = fragmento.querySelector('[data-question-card]');
		contenedor.appendChild(fragmento);
		renombrarPregunta(pregunta, numeroPregunta);
		configurarPregunta(pregunta);
		agregarOpcion(pregunta);
		agregarOpcion(pregunta);
	}

	contenedor.querySelectorAll('[data-question-card]').forEach(function (pregunta) {
		renombrarPregunta(pregunta, pregunta.dataset.questionIndex);
		configurarPregunta(pregunta);
	});

	document.getElementById('agregar-pregunta').addEventListener('click', agregarPregunta);
});
</script>
@endsection
