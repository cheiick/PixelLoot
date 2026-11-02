<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', \App\Models\SiteSetting::getValue('site_name', 'MonShop'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: '#2563eb',
                        },
                    },
                },
            };
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="guest-shell min-h-screen bg-stone-100 font-sans antialiased">
        <div class="min-h-screen px-4 py-6 sm:px-6 sm:py-10">
            <div class="mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-6xl flex-col">
                <header class="mb-8 flex items-center justify-between sm:mb-12">
                    <a href="{{ route('home') }}" class="inline-flex min-w-0 items-center gap-3 text-slate-900">
                        @if(\App\Models\SiteSetting::getValue('site_logo'))
                            <img src="{{ \App\Support\ImageStorage::url(\App\Models\SiteSetting::getValue('site_logo')) }}" alt="" class="max-h-11 max-w-40 object-contain sm:max-h-14 sm:max-w-48">
                            <span class="sr-only">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}</span>
                        @else
                            <span class="truncate text-lg font-extrabold tracking-tight sm:text-xl">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}<span class="text-rose-600">.</span></span>
                        @endif
                    </a>
                    <a href="{{ route('home') }}" class="shrink-0 rounded-full border border-stone-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-900 hover:text-slate-950 sm:px-4 sm:text-sm">
                        <span class="sm:hidden">← {{ __('Boutique') }}</span>
                        <span class="hidden sm:inline">← {{ __('Retour à la boutique') }}</span>
                    </a>
                </header>

                <main class="my-auto grid w-full items-center gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)] lg:gap-16">
                    <section class="guest-brand-panel hidden max-w-xl rounded-2xl p-10 lg:block">
                        <p class="text-sm font-bold uppercase tracking-[0.2em] text-rose-300">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}</p>
                        <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight xl:text-5xl">{{ __('Le meilleur de la boutique, dans votre poche.') }}</h1>
                        <p class="mt-5 max-w-lg text-lg leading-8 text-slate-300">{{ __('Connectez-vous pour suivre vos commandes, retrouver vos favoris et profiter de votre espace client.') }}</p>
                        <div class="mt-10 flex items-center gap-3 text-sm text-slate-300">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-rose-300" aria-hidden="true">✓</span>
                            <span>{{ __('Un espace simple et sécurisé pour vos achats.') }}</span>
                        </div>
                    </section>

                    <section class="guest-form-panel mx-auto w-full max-w-lg bg-white p-5 sm:p-8 lg:p-10">
                        {{ $slot }}
                    </section>
                </main>

                <footer class="mt-8 text-center text-xs text-slate-500 sm:mt-12">
                    © {{ date('Y') }} {{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }} · {{ __('Tous droits réservés.') }}
                </footer>
            </div>
        </div>
    </body>
</html>
