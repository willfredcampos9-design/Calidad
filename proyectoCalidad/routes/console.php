<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:make-administrator {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (!$user) {
        $this->error('No existe un usuario con ese correo.');

        return 1;
    }

    $user->update(['role' => 'administrador']);
    $this->info("{$user->email} ahora es administrador.");

    return 0;
})->purpose('Asignar el primer administrador a una cuenta existente');
