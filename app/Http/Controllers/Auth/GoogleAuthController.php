<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')->withErrors([
                'google' => __('La connexion Google n’est pas encore configurée. Renseignez GOOGLE_CLIENT_ID et GOOGLE_CLIENT_SECRET dans le fichier .env.'),
            ]);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        /** @var GoogleUser $googleUser */
        $googleUser = Socialite::driver('google')->user();
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $googleId = (string) $googleUser->getId();
        $rawUser = $googleUser->getRaw();

        if (
            $email === ''
            || $googleId === ''
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! filter_var($rawUser['email_verified'] ?? $rawUser['verified_email'] ?? false, FILTER_VALIDATE_BOOLEAN)
        ) {
            return redirect()->route('login')->withErrors([
                'google' => __('Google doit fournir une adresse e-mail vérifiée pour créer ou ouvrir votre compte.'),
            ]);
        }

        $user = DB::transaction(function () use ($googleUser, $googleId, $email): ?User {
            $user = User::where('google_id', $googleId)->first()
                ?? User::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($user) {
                if ($user->google_id && $user->google_id !== $googleId) {
                    return null;
                }

                $user->forceFill([
                    'google_id' => $googleId,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                return $user;
            }

            return User::create([
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleId,
                'email_verified_at' => now(),
                'password' => Str::random(48),
            ]);
        });

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'google' => __('Ce compte est déjà associé à une autre identité Google.'),
            ]);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
