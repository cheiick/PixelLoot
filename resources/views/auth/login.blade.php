<x-guest-layout>
    <div class="mb-7">
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM4 20a8 8 0 0 1 16 0M19 8h3m-1.5-1.5v3"/>
            </svg>
        </div>
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">{{ __('Heureux de vous revoir') }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Connectez-vous pour retrouver vos commandes et vos favoris.') }}</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="login" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('E-mail ou numéro de téléphone') }}</label>
            <input id="login" class="block min-h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" type="text" name="login" value="{{ old('login', old('email')) }}" required autofocus autocomplete="username" placeholder="{{ __('exemple@email.com ou +225…') }}">
            <x-input-error :messages="$errors->get('login')" class="mt-2 text-sm" />
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between gap-2">
                <label for="password" class="block text-sm font-semibold text-slate-700">{{ __('Mot de passe') }}</label>
                @if (Route::has('password.request'))
                    <a class="text-right text-xs font-semibold text-blue-700 hover:text-blue-900 sm:text-sm" href="{{ route('password.request') }}">{{ __('Mot de passe oublié ?') }}</a>
                @endif
            </div>
            <input id="password" class="block min-h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-base text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
        </div>

        <label for="remember_me" class="flex min-h-6 items-center gap-3 text-sm text-slate-600">
            <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" name="remember">
            <span>{{ __('Se souvenir de moi') }}</span>
        </label>

        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
            {{ __('Se connecter') }}
            <svg class="ms-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/>
            </svg>
        </button>
    </form>

    @if(Route::has('register'))
        <p class="mt-7 border-t border-slate-100 pt-6 text-center text-sm text-slate-600">
            {{ __('Vous n’avez pas encore de compte ?') }}
            <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-900">{{ __('Créer un compte') }}</a>
        </p>
    @endif
</x-guest-layout>
