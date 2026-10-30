<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register with a phone number instead of an email', function () {
    $response = $this->post('/register', [
        'name' => 'Phone User',
        'phone' => '+33 6 12 34 56 78',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Phone User',
        'email' => null,
        'phone' => '+33612345678',
    ]);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registration requires at least one contact method', function () {
    $this->post('/register', [
        'name' => 'No Contact',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors(['email', 'phone']);
});
