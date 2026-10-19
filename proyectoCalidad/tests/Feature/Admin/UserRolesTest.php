<?php

use App\Models\User;

test('an administrator can change another user role', function () {
    $administrator = User::factory()->create(['role' => 'administrador']);
    $user = User::factory()->create(['role' => 'aprendiz']);

    $response = $this->actingAs($administrator)->patch(
        route('admin.usuarios.role', $user),
        ['role' => 'instructor']
    );

    $response->assertRedirect(route('admin.usuarios.show', $user));
    $this->assertSame('instructor', $user->fresh()->role);
});

test('an administrator cannot change their own role', function () {
    $administrator = User::factory()->create(['role' => 'administrador']);

    $response = $this->actingAs($administrator)->patch(
        route('admin.usuarios.role', $administrator),
        ['role' => 'aprendiz']
    );

    $response->assertForbidden();
    $this->assertSame('administrador', $administrator->fresh()->role);
});

test('apprentices cannot access survey management or the dashboard', function () {
    $apprentice = User::factory()->create(['role' => 'aprendiz']);

    $this->actingAs($apprentice)->get(route('encuestas.index'))->assertForbidden();
    $this->get(route('dashboard'))->assertForbidden();
    $this->get(route('resultados.index'))->assertForbidden();
});