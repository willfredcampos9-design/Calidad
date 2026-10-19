@extends('layouts.app')

@section('title', 'Resultados de la encuesta')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4" style="max-width: 1440px;">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="small text-primary fw-semibold text-uppercase mb-2">Resultados de encuesta</p>
            <h1 class="h3 fw-bold text-dark mb-2">{{ $encuesta->titulo_encuesta }}</h1>
            <p class="text-secondary mb-0">
                {{ $encuesta->descripcion_encuesta ?: 'Resumen de respuestas recibidas para esta encuesta.' }}
            </p>
            @if ($encuesta->anonima)
                <p class="small text-secondary mt-2 mb-0">Encuesta anónima: no se muestran datos personales de quienes respondieron.</p>
            @else
                <p class="small text-primary mt-2 mb-0">Encuesta no anónima: los datos de participantes están disponibles abajo.</p>
            @endif
        </div>
        <a href="{{ route('resultados.index') }}" class="btn btn-outline-primary">← Volver a resultados</a>
    </div>

    @unless ($encuesta->anonima)
        <section class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h2 class="h6 fw-bold text-dark mb-1">Participantes</h2>
                        <p class="small text-secondary mb-0">Personas que enviaron una respuesta a esta encuesta.</p>
                    </div>
                    <span class="badge rounded-pill text-bg-primary">{{ $envios->count() }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                @if ($envios->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Correo electrónico</th>
                                    <th scope="col" class="text-end">Fecha de respuesta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($envios as $envio)
                                    <tr>
                                        <td class="fw-medium">{{ $envio->user?->name ?? 'Usuario no disponible' }}</td>
                                        <td>{{ $envio->user?->email ?? 'No disponible' }}</td>
                                        <td class="text-end">{{ $envio->created_at?->format('d/m/Y H:i') ?? 'No disponible' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-secondary small mb-0">Todavía no hay participantes.</p>
                @endif
            </div>
        </section>
    @endunless

    <div class="d-flex flex-column gap-3">
        @foreach ($preguntas as $pregunta)
            <section class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                    <div class="d-flex align-items-start gap-3">
                        <span class="badge rounded-pill text-bg-primary mt-1">{{ $loop->iteration }}</span>
                        <div>
                            <h2 class="h6 fw-bold text-dark mb-1">{{ $pregunta->pregunta }}</h2>
                            <p class="small text-secondary mb-0">
                                {{ $pregunta->opciones->isNotEmpty() ? 'Distribución de respuestas' : 'Respuestas de texto' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if ($pregunta->opciones->isNotEmpty())
                        <div class="mb-4" style="height: 280px;">
                            <canvas id="grafica-{{ $pregunta->id }}" aria-label="Gráfico de respuestas para {{ $pregunta->pregunta }}" role="img"></canvas>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Opción</th>
                                        <th scope="col" class="text-end">Votos</th>
                                        <th scope="col" class="text-end">Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pregunta->opciones as $opcion)
                                        <tr>
                                            <td class="fw-medium">{{ $opcion->opcion }}</td>
                                            <td class="text-end">{{ $opcion->votos }}</td>
                                            <td class="text-end">{{ $opcion->porcentaje }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @forelse ($pregunta->respuestasTexto as $respuesta)
                            <div class="border-start border-4 border-primary bg-light rounded-end px-3 py-2 mb-2">
                                {{ $respuesta }}
                            </div>
                        @empty
                            <p class="text-secondary small mb-0">Aún no hay respuestas para esta pregunta.</p>
                        @endforelse
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach ($preguntas as $pregunta)
        @if ($pregunta->opciones->isNotEmpty())
            new Chart(
                document.getElementById(
                    'grafica-{{ $pregunta->id }}'
                ),
                {
                    type: 'bar',

                    data: {
                        labels: {{ \Illuminate\Support\Js::from(
                            $pregunta->opciones->pluck('opcion')->values()
                        ) }},

                        datasets: [{
                            label: 'Votos',
                            data: {{ \Illuminate\Support\Js::from(
                                $pregunta->opciones->pluck('votos')->values()
                            ) }},
                            backgroundColor: 'rgba(13, 110, 253, 0.65)',
                            borderColor: 'rgb(13, 110, 253)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(108, 117, 125, 0.12)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                }
            );
        @endif
    @endforeach
});
</script>
@endsection
```