@extends('layouts.app')

@section('title', 'Administrar usuarios')

@section('content')
<div class="container-fluid py-4 px-3 px-md-4" style="max-width: 1200px;">
    <div class="mb-4">
        <p class="small text-primary fw-semibold text-uppercase mb-2">Administración</p>
        <h1 class="h3 fw-bold text-dark mb-2">Usuarios</h1>
        <p class="text-secondary mb-0">Consulta perfiles y administra los roles de acceso.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="px-4">Usuario</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Rol</th>
                            <th scope="col" class="text-end px-4">Perfil</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-4 fw-semibold text-dark">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->isAdministrador() ? 'text-bg-danger' : ($user->isInstructor() ? 'text-bg-primary' : 'text-bg-secondary') }}">
                                        {{ ['aprendiz' => 'Aprendiz', 'instructor' => 'Instructor', 'administrador' => 'Administrador'][$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td class="text-end px-4">
                                    <a href="{{ route('admin.usuarios.show', $user) }}" class="btn btn-sm btn-outline-primary">Ver perfil</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">No hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages())
            <div class="card-footer bg-white border-0 px-4">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection