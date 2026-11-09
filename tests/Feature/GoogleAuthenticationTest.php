<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

function fakeGoogleProvider(array $attributes): void
{
    $googleUser = GoogleUser::fake($attributes);
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->once()->andReturn($googleUser);

    Socialite::shouldReceive('driver')
        ->once()
        ->with('google')
        ->andReturn($provider);
}

it('shows Google sign-in and registration when OAuth is configured', function () {
    config([
        'services.google.client_id' => 'client-id',
        'services.google.client_secret' => 'client-secret',
    ]);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('auth.google.redirect'));

    $this->get(route('register'))
        ->assertOk()
        ->assertSee(route('auth.google.redirect'));
});

it('shows Google sign-in when OAuth is not configured and explains how to enable it', function () {
    config([
        'services.google.client_id' => null,
        'services.google.client_secret' => null,
    ]);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('auth.google.redirect'));

    $this->get(route('register'))
        ->assertOk()
        ->assertSee(route('auth.google.redirect'));

    $this->get(route('auth.google.redirect'))
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('google');
});

it('registers a user from a verified Google account', function () {
    fakeGoogleProvider([
        'id' => 'google-user-123',
        'name' => 'Google Customer',
        'email' => 'customer@example.test',
        'email_verified' => true,
    ]);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'customer@example.test')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'google_id' => 'google-user-123',
        'email_verified_at' => $user->email_verified_at,
    ]);
});

it('links a verified Google account to an existing account with the same email', function () {
    $user = User::factory()->unverified()->create([
        'email' => 'customer@example.test',
    ]);

    fakeGoogleProvider([
        'id' => 'google-user-456',
        'name' => 'Google Customer',
        'email' => 'customer@example.test',
        'email_verified' => true,
    ]);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->google_id)->toBe('google-user-456')
        ->and($user->fresh()->email_verified_at)->not->toBeNull();
    expect(User::count())->toBe(1);
});

it('rejects an unverified Google email address', function () {
    fakeGoogleProvider([
        'id' => 'google-user-789',
        'name' => 'Google Customer',
        'email' => 'customer@example.test',
        'email_verified' => false,
    ]);

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHasErrors('google');

    $this->assertGuest();
    expect(User::count())->toBe(0);
});
