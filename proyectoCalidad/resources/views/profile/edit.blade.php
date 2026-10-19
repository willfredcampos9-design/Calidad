@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    <div class="container-fluid py-4 px-3 px-md-4" style="max-width: 1100px;">
        <div class="mb-4">
            <p class="small text-primary fw-semibold text-uppercase mb-2">Cuenta</p>
            <h1 class="h3 fw-bold text-dark mb-2">Mi perfil</h1>
            <p class="text-secondary mb-0">Administra tus datos personales y la seguridad de tu cuenta.</p>
        </div>

        <div class="d-grid gap-3">
            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            <section class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.update-password-form')
                </div>
            </section>

            <section class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>
        </div>
    </div>
@endsection
