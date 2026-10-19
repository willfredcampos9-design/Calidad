@extends('layouts.app')

@section('title', 'Sistema de encuestas')

@section('content')
<style>
    .inicio-page { min-height: calc(100vh - 64px); padding: 48px 0 64px; background: #f4f7fc; }
    .inicio-wrap { max-width: 1120px; }
    .inicio-kicker { color: #2563eb; font-size: .78rem; font-weight: 700; letter-spacing: .08em; }
    .inicio-title { max-width: 760px; color: #172b4d; font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 750; line-height: 1.08; }
    .inicio-copy { max-width: 620px; color: #718096; font-size: 1.05rem; line-height: 1.7; }
    .inicio-panel { border: 1px solid #e5ebf4; border-radius: 16px; background: #fff; box-shadow: 0 8px 28px rgba(30,64,120,.06); }
    .inicio-action { height: 100%; padding: 22px; border: 1px solid #e5ebf4; border-radius: 13px; background: #fff; color: inherit; text-decoration: none; transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease; }
    .inicio-action:hover { border-color: #93b4f7; color: inherit; transform: translateY(-2px); box-shadow: 0 10px 24px rgba(30,64,120,.08); }
    .inicio-action-mark { display: inline-flex; width: 42px; height: 42px; align-items: center; justify-content: center; border-radius: 12px; background: #eaf1ff; color: #245fe7; font-size: 1.25rem; }
    .inicio-action h2 { color: #1e293b; }
    .inicio-action p { color: #718096; }
    .btn-inicio { color: #fff; background: #2563eb; border-color: #2563eb; border-radius: 9px; font-weight: 600; }
    .btn-inicio:hover { color: #fff; background: #1d4ed8; border-color: #1d4ed8; }
    .btn-inicio-soft { color: #245fe7; background: #edf3ff; border-color: #edf3ff; border-radius: 9px; font-weight: 600; }
    .btn-inicio-soft:hover { color: #1d4ed8; background: #dfeaff; border-color: #dfeaff; }
    @media (max-width: 575.98px) { .inicio-page { padding-top: 32px; } }
</style>

<main class="inicio-page">
    <div class="container inicio-wrap">
        <section class="inicio-panel p-4 p-md-5 mb-4">
            <div class="inicio-kicker text-uppercase mb-3">PLATAFORMA DE ENCUESTAS</div>
            <h1 class="inicio-title mb-3">Escucha, organiza y entiende cada respuesta.</h1>
            <p class="inicio-copy mb-4">
                Crea encuestas, participa en formularios disponibles y consulta resultados desde un solo lugar.
            </p>

            @auth
                @if (auth()->user()->canManageSurveys())
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-inicio px-4 py-2">Ir al panel</a>
                        <a href="{{ route('encuestas.index') }}" class="btn btn-inicio-soft px-4 py-2">Administrar encuestas</a>
                    </div>
                @else
                    <a href="{{ route('envios.index') }}" class="btn btn-inicio px-4 py-2">Responder encuestas</a>
                @endif
            @else
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('login') }}" class="btn btn-inicio px-4 py-2">Iniciar sesión</a>
                    <a href="{{ route('register') }}" class="btn btn-inicio-soft px-4 py-2">Crear cuenta</a>
                </div>
            @endauth
        </section>

        <div class="row g-3">
            @auth
                <div class="col-12 col-md-4">
                    <a href="{{ route('encuestas.index') }}" class="inicio-action d-block">
                        <span class="inicio-action-mark mb-3" aria-hidden="true">▤</span>
                        <h2 class="h6 fw-bold mb-2">Mis encuestas</h2>
                        <p class="small mb-0">Crea y administra tus formularios.</p>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('envios.index') }}" class="inicio-action d-block">
                        <span class="inicio-action-mark mb-3" aria-hidden="true">✓</span>
                        <h2 class="h6 fw-bold mb-2">Responder encuestas</h2>
                        <p class="small mb-0">Consulta los formularios disponibles para participar.</p>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('resultados.index') }}" class="inicio-action d-block">
                        <span class="inicio-action-mark mb-3" aria-hidden="true">▥</span>
                        <h2 class="h6 fw-bold mb-2">Resultados</h2>
                        <p class="small mb-0">Revisa las respuestas recopiladas.</p>
                    </a>
                </div>
            @else
                <div class="col-12 col-md-6">
                    <a href="{{ route('login') }}" class="inicio-action d-block">
                        <span class="inicio-action-mark mb-3" aria-hidden="true">↗</span>
                        <h2 class="h6 fw-bold mb-2">Ya tienes una cuenta</h2>
                        <p class="small mb-0">Inicia sesión para acceder a tus encuestas y resultados.</p>
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <a href="{{ route('register') }}" class="inicio-action d-block">
                        <span class="inicio-action-mark mb-3" aria-hidden="true">＋</span>
                        <h2 class="h6 fw-bold mb-2">Empieza a participar</h2>
                        <p class="small mb-0">Crea tu cuenta para administrar tus formularios.</p>
                    </a>
                </div>
            @endauth
        </div>
    </div>
</main>
@endsection