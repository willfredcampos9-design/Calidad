@extends('layouts.app')

@section('title', 'Perfil de usuario')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <a href="{{ route('admin.usuarios.index') }}" class="text-decoration-none small">← Volver a usuarios</a>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3 mt-4 mb-4">
        <div>
            <p class="small text-primary fw-semibold text-uppercase mb-2">Perfil de usuario</p>
            <h1 class="h3 fw-bold text-dark mb-1">{{ $user->name }}</h1>
            <p class="text-secondary mb-0">{{ $user->email }}</p>
        </div>
        <span class="badge {{ $user->isAdministrador() ? 'text-bg-danger' : ($user->isInstructor() ? 'text-bg-primary' : 'text-bg-secondary') }} fs-6">
            {{ ['aprendiz' => 'Aprendiz', 'instructor' => 'Instructor', 'administrador' => 'Administrador'][$user->role] ?? $user->role }}
        </span>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 pt-4">
            <h2 class="h6 fw-bold mb-1">Acceso del usuario</h2>
            <p class="small text-secondary mb-0">Los administradores pueden asignar o retirar el rol de administrador a otros usuarios.</p>
        </div>
        <div class="card-body p-4">
            @if (auth()->id() === $user->id)
                <p class="text-secondary mb-0">No puedes cambiar tu propio rol. Pide a otro administrador que gestione tu acceso.</p>
            @else
                <form action="{{ route('admin.usuarios.role', $user) }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    @method('PATCH')
                    <div class="col-12 col-md-8">
                        <label for="role" class="form-label fw-semibold">Rol</label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="aprendiz" @selected($user->role === 'aprendiz')>Aprendiz</option>
                            <option value="instructor" @selected($user->role === 'instructor')>Instructor</option>
                            <option value="administrador" @selected($user->role === 'administrador')>Administrador</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Guardar rol</button>
                    </div>
                </form>
            @endif
        </div>
    </section>
</div>
@endsection