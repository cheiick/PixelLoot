<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('allows an existing administrator to create another admin account', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.admin-users.index'))
        ->assertOk()
        ->assertSee('Créer un compte administrateur');

    $this->post(route('admin.admin-users.store'), [
        'name' => 'Second Admin',
        'email' => 'admin2@example.com',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ])
        ->assertRedirect(route('admin.admin-users.index'))
        ->assertSessionHas('success');

    $newAdmin = User::where('email', 'admin2@example.com')->firstOrFail();
    expect($newAdmin->is_admin)->toBeTrue();
    $this->assertTrue(Hash::check('SecurePassword123!', $newAdmin->password));
});

it('does not let a customer create an admin account', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->post(route('admin.admin-users.store'), [
            'name' => 'Unauthorized Admin',
            'email' => 'unauthorized@example.com',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
        ])
        ->assertRedirect('/');

    $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.com']);
});

it('rejects weak admin passwords and duplicate administrator emails', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($admin)
        ->post(route('admin.admin-users.store'), [
            'name' => 'New Admin',
            'email' => 'existing@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors(['email', 'password']);

    $this->assertDatabaseMissing('users', ['name' => 'New Admin']);
});
