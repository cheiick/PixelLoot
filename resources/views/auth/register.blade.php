<x-guest-layout>
    <div class="mb-6">
        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m6-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-3v6m-3-3h6"/>
            </svg>
        </div>
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">{{ __('Créer votre compte') }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('Rejoignez-nous pour suivre vos commandes et retrouver vos produits préférés.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ __('Nom complet') }}</label>
            <input id="name" class="block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-base text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="{{ __('Votre nom') }}">
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-sm" />
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ __('Adresse e-mail') }} <span class="font-normal text-slate-400">({{ __('facultatif si vous utilisez un téléphone') }})</span></label>
            <input id="email" class="block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-base text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="vous@exemple.fr">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-sm" />
        </div>

        <div>
            <label for="phone" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ __('Téléphone') }} <span class="font-normal text-slate-400">({{ __('facultatif si vous utilisez un e-mail') }})</span></label>
            <input id="phone" class="block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-base text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+225 07 00 00 00 00">
            <x-input-error :messages="$errors->get('phone')" class="mt-1.5 text-sm" />
            <p class="mt-1.5 text-xs leading-5 text-slate-500">{{ __('Indiquez au moins une adresse e-mail ou un numéro de téléphone.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ __('Mot de passe') }}</label>
                <input id="password" class="block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-base text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" type="password" name="password" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-sm" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ __('Confirmer le mot de passe') }}</label>
                <input id="password_confirmation" class="block min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-base text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10" type="password" name="password_confirmation" required autocomplete="new-password">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-sm" />
            </div>
        </div>

        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/30">
            {{ __('Créer mon compte') }}
            <svg class="ms-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/>
            </svg>
        </button>
    </form>

    @include('auth.partials.google-button')

    <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-600">
        {{ __('Vous avez déjà un compte ?') }}
        <a class="font-semibold text-blue-700 hover:text-blue-900" href="{{ route('login') }}">{{ __('Se connecter') }}</a>
    </p>
</x-guest-layout>
