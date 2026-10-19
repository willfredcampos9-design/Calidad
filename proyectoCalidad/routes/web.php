<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\PreguntasController;
use App\Http\Controllers\EnviosController;
use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Models\Encuesta;
use App\Models\Respuestas;

Route::get('/', function () {
    return view('index');
});

Route::get('/dashboard', function () {
    $encuestasUsuario = Encuesta::query();
    if (!auth()->user()->isAdministrador()) {
        $encuestasUsuario->where('id_usuario', auth()->id());
    }

    $totalEncuestas = (clone $encuestasUsuario)->count();
    $encuestasActivas = (clone $encuestasUsuario)
        ->where('estado_encuesta', true)
        ->count();
    $encuestasInactivas = (clone $encuestasUsuario)
        ->where('estado_encuesta', false)
        ->count();

    $encuestasRecientes = (clone $encuestasUsuario)
        ->latest()
        ->take(5)
        ->get();

    $totalRespuestas = Respuestas::whereHas('pregunta.encuesta', function ($query) {
        if (!auth()->user()->isAdministrador()) {
            $query->where('id_usuario', auth()->id());
        }
    })->count();

    return view('dashboard', compact(
        'totalEncuestas',
        'encuestasActivas',
        'encuestasInactivas',
        'totalRespuestas',
        'encuestasRecientes'
    ));
})->middleware(['auth', 'verified', 'role:instructor,administrador'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/{user}', [AdminUserController::class, 'show'])->name('usuarios.show');
        Route::patch('/usuarios/{user}/rol', [AdminUserController::class, 'updateRole'])->name('usuarios.role');
    });

    Route::middleware('role:instructor,administrador')->group(function () {
    // Rutas de Encuestas
    Route::get('/encuestas', [EncuestaController::class, 'index'])
        ->name('encuestas.index');
    Route::get('/encuestas/create', [EncuestaController::class, 'create'])
        ->name('encuestas.create');
    Route::post('/encuestas', [EncuestaController::class, 'store'])
        ->name('encuestas.store');
    Route::get('/encuestas/{encuesta}', [EncuestaController::class, 'show'])
        ->name('encuestas.show');
    Route::get('/encuestas/{encuesta}/edit', [EncuestaController::class, 'edit'])
        ->name('encuestas.edit');
    Route::put('/encuestas/{encuesta}', [EncuestaController::class, 'update'])
        ->name('encuestas.update');
    Route::delete('/encuestas/{encuesta}', [EncuestaController::class, 'destroy'])
        ->name('encuestas.destroy');
    Route::patch('/encuestas/{encuesta}/estado', [EncuestaController::class, 'cambiarEstado'])
        ->name('encuestas.estado');
    });

    // Rutas de envios
    Route::get('/envios', [EnviosController::class, 'index'])
        ->name('envios.index');
    Route::get('/envios/create', [EnviosController::class, 'create'])
        ->name('envios.create');
    Route::post('/envios', [EnviosController::class, 'store'])
        ->name('envios.store');
    Route::get('/envios/{id}', [EnviosController::class, 'show'])
        ->middleware('role:instructor,administrador')
        ->name('envios.show');
    Route::get('/envios/{encuesta}/responder',[EnviosController::class, 'responder'])
        ->name('envios.responder');
    Route::get('/encuestas/{encuesta}/resultados',[EnviosController::class, 'resultados'])
        ->middleware('role:instructor,administrador')
        ->name('encuestas.resultados');
    

    // Rutas de resultados
    Route::get('/resultados', [ResultadosController::class, 'index'])
        ->middleware('role:instructor,administrador')
        ->name('resultados.index');
    Route::get('/resultados/{id}', [ResultadosController::class, 'show'])
        ->middleware('role:instructor,administrador')
        ->name('resultados.show');
});

require __DIR__.'/auth.php';
