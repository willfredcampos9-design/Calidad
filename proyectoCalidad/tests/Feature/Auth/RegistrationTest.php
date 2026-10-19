<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'aprendiz',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('envios.index', absolute: false));
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'role' => 'aprendiz',
    ]);
});

test('public registration cannot create administrators', function () {
    $response = $this->from('/register')->post('/register', [
        'name' => 'Admin Attempt',
        'email' => 'admin-attempt@example.com',
        'role' => 'administrador',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register')->assertSessionHasErrors('role');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'admin-attempt@example.com']);
});
