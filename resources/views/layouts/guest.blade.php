<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', \App\Models\SiteSetting::getValue('site_name', 'MonShop'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
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
    <body class="min-h-screen bg-slate-950 font-sans antialiased text-slate-900">
        <div class="relative isolate min-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 px-4 py-6 sm:px-6 sm:py-10">
            <div class="pointer-events-none absolute -left-32 top-20 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-40 -right-20 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl" aria-hidden="true"></div>

            <div class="relative mx-auto flex min-h-[calc(100vh-3rem)] w-full max-w-6xl flex-col">
                <header class="mb-8 flex items-center justify-between sm:mb-12">
                    <a href="{{ route('home') }}" class="inline-flex min-w-0 items-center gap-3 text-white">
                        @if(\App\Models\SiteSetting::getValue('site_logo'))
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(\App\Models\SiteSetting::getValue('site_logo')) }}" alt="" class="max-h-11 max-w-40 object-contain sm:max-h-14 sm:max-w-48">
                            <span class="sr-only">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}</span>
                        @else
                            <span class="truncate text-lg font-bold tracking-tight sm:text-xl">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}<span class="text-blue-400">.</span></span>
                        @endif
                    </a>
                    <a href="{{ route('home') }}" class="shrink-0 rounded-full border border-white/15 px-3 py-2 text-xs font-medium text-slate-300 transition hover:border-white/30 hover:text-white sm:px-4 sm:text-sm">
                        <span class="sm:hidden">← {{ __('Boutique') }}</span>
                        <span class="hidden sm:inline">← {{ __('Retour à la boutique') }}</span>
                    </a>
                </header>

                <main class="my-auto grid w-full items-center gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(420px,0.9fr)] lg:gap-16">
                    <section class="hidden max-w-xl text-white lg:block">
                        <div class="mb-8 inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-2xl shadow-lg shadow-blue-950/30" aria-hidden="true">✦</div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-300">{{ \App\Models\SiteSetting::getValue('site_name', 'MonShop') }}</p>
                        <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight xl:text-5xl">{{ __('Le meilleur de la boutique, dans votre poche.') }}</h1>
                        <p class="mt-5 max-w-lg text-lg leading-8 text-slate-300">{{ __('Connectez-vous pour suivre vos commandes, retrouver vos favoris et profiter de votre espace client.') }}</p>
                        <div class="mt-10 flex items-center gap-3 text-sm text-slate-300">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-400/15 text-emerald-300" aria-hidden="true">✓</span>
                            <span>{{ __('Un espace simple et sécurisé pour vos achats.') }}</span>
                        </div>
                    </section>

                    <section class="mx-auto w-full max-w-lg rounded-3xl border border-white/60 bg-white p-5 shadow-2xl shadow-black/25 sm:p-8 lg:p-10">
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
