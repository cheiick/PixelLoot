<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>

    <title>@yield('title', $siteSettings->get('site_name', 'MonShop'))</title>
    <meta name="description" content="@yield('meta_description', 'Découvrez notre sélection de produits gaming et high-tech, avec livraison rapide et garantie.')">
    <meta name="robots" content="@yield('meta_robots', 'index,follow')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', $siteSettings->get('site_name', 'MonShop'))">
    <meta property="og:description" content="@yield('meta_description', 'Découvrez notre sélection de produits gaming et high-tech, avec livraison rapide et garantie.')">
    @hasSection('meta_image')
        <meta property="og:image" content="@yield('meta_image')">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    @stack('structured_data')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS via CDN -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- AOS Animations -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Configuration Tailwind personnalisée -->
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    primary: '#3b82f6',
                    secondary: '#1e293b',
                }
            }
        }
    }
</script>

<!-- Initialisation AOS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            once: true
        });
    });
</script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js']) 
</head>
<body data-currency-code="{{ app(\App\Support\CurrencyFormatter::class)->code() }}"
      data-currency-symbol="{{ app(\App\Support\CurrencyFormatter::class)->symbol() }}"
      data-currency-digits="{{ app(\App\Support\CurrencyFormatter::class)->fractionDigits() }}"
      data-app-locale="{{ app()->getLocale() }}"
      class="bg-gray-50 font-sans antialiased dark:bg-slate-950">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 bg-slate-900 text-white shadow-lg" x-data="{ open: false }">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-w-0 shrink-0 items-center gap-2 font-bold text-xl tracking-tight">
                @if($siteSettings->get('site_logo'))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($siteSettings->get('site_logo')) }}" alt="{{ $siteSettings->get('site_name', 'MonShop') }}" class="max-h-10 max-w-36 object-contain sm:max-h-14 sm:max-w-48 lg:max-h-16 lg:max-w-64">
                @else
                    <span class="max-w-36 truncate sm:max-w-56">{{ $siteSettings->get('site_name', 'MonShop') }}</span><span class="-ml-2 text-blue-500">.</span>
                @endif
            </a>

            <ul class="hidden items-center gap-8 lg:flex">
                <li><a href="{{ route('home') }}" class="font-medium transition hover:text-blue-400">{{ __('Accueil') }}</a></li>
                <li><a href="{{ route('catalog.index') }}" class="font-medium transition hover:text-blue-400">{{ __('Catalogue') }}</a></li>
            </ul>

            @php
                $cartCount = collect(session('cart', []))->sum(fn ($item) => is_array($item) ? (int) ($item['quantity'] ?? 0) : 0);
            @endphp

            <div class="hidden shrink-0 items-center gap-5 lg:flex">
                <form action="{{ route('language.update') }}" method="POST" class="flex items-center">
                    @csrf
                    <label class="sr-only" for="desktop-language">{{ __('Langue') }}</label>
                    <select id="desktop-language" name="locale" onchange="this.form.submit()" aria-label="{{ __('Choisir la langue') }}" class="rounded-md border border-slate-700 bg-slate-800 px-2 py-1 text-sm text-white">
                        <option value="fr" @selected(app()->getLocale() === 'fr')>FR</option>
                        <option value="en" @selected(app()->getLocale() === 'en')>EN</option>
                    </select>
                </form>
                <button type="button" data-theme-toggle aria-label="Activer ou désactiver le mode sombre" aria-pressed="false" class="rounded-lg px-2 py-1 text-xl transition hover:bg-slate-800">🌙</button>
                @auth
                    <a href="{{ route('dashboard') }}" class="font-medium transition hover:text-blue-400">{{ __('Mon compte') }}</a>
                    <a href="{{ route('wishlist.index') }}" class="font-medium transition hover:text-blue-400">{{ __('Wishlist') }}</a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="font-medium transition hover:text-blue-400">{{ __('Administration') }}</a>
                    @endif
                @endauth
                <a href="{{ route('cart.index') }}" aria-label="Panier ({{ $cartCount }} article(s))" class="relative inline-flex items-center gap-2 transition hover:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-1.5 9H4.5L3 3z"/>
                        <circle cx="7.5" cy="20.5" r="1.5"/>
                        <circle cx="16.5" cy="20.5" r="1.5"/>
                    </svg>
                    <span>{{ __('Panier') }}</span>
                    <span data-cart-count class="absolute -right-3 -top-2 {{ $cartCount > 0 ? '' : 'hidden' }} flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-xs font-bold text-white">{{ $cartCount }}</span>
                </a>
                @guest
                    <a href="{{ route('login') }}" class="rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold transition hover:bg-blue-700">{{ __('Se connecter') }}</a>
                @else
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="font-medium transition hover:text-red-400">{{ __('Déconnexion') }}</button>
                    </form>
                @endguest
            </div>

            <div class="flex shrink-0 items-center gap-4 lg:hidden">
                <button type="button" data-theme-toggle aria-label="Activer ou désactiver le mode sombre" aria-pressed="false" class="rounded-lg px-1 py-1 text-xl">🌙</button>
                <a href="{{ route('cart.index') }}" aria-label="Panier ({{ $cartCount }} article(s))" class="relative inline-flex items-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-1.5 9H4.5L3 3z"/>
                        <circle cx="7.5" cy="20.5" r="1.5"/>
                        <circle cx="16.5" cy="20.5" r="1.5"/>
                    </svg>
                    <span data-cart-count class="absolute -right-2 -top-2 {{ $cartCount > 0 ? '' : 'hidden' }} flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-xs font-bold text-white">{{ $cartCount }}</span>
                </a>
                <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-navigation" aria-label="Ouvrir ou fermer le menu" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-700 transition hover:bg-slate-800">
                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>
        </nav>

        <div id="mobile-navigation" x-show="open" x-transition.origin.top x-cloak class="border-t border-slate-700 bg-slate-900 px-4 py-3 lg:hidden sm:px-6">
            <ul class="mx-auto max-w-7xl space-y-1">
                <li>
                    <form action="{{ route('language.update') }}" method="POST" class="px-4 py-2">
                        @csrf
                        <label for="mobile-language" class="mr-3 text-sm text-slate-300">{{ __('Langue') }}</label>
                        <select id="mobile-language" name="locale" onchange="this.form.submit()" aria-label="{{ __('Choisir la langue') }}" class="rounded-md border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-white">
                            <option value="fr" @selected(app()->getLocale() === 'fr')>Français</option>
                            <option value="en" @selected(app()->getLocale() === 'en')>English</option>
                        </select>
                    </form>
                </li>
                <li><a @click="open = false" href="{{ route('home') }}" class="block rounded-lg px-4 py-3 font-medium transition hover:bg-slate-800 hover:text-blue-300">{{ __('Accueil') }}</a></li>
                <li><a @click="open = false" href="{{ route('catalog.index') }}" class="block rounded-lg px-4 py-3 font-medium transition hover:bg-slate-800 hover:text-blue-300">{{ __('Catalogue') }}</a></li>
                @auth
                    <li><a @click="open = false" href="{{ route('dashboard') }}" class="block rounded-lg px-4 py-3 font-medium transition hover:bg-slate-800 hover:text-blue-300">{{ __('Mon compte') }}</a></li>
                    <li><a @click="open = false" href="{{ route('wishlist.index') }}" class="block rounded-lg px-4 py-3 font-medium transition hover:bg-slate-800 hover:text-blue-300">{{ __('Ma wishlist') }}</a></li>
                    @if(auth()->user()->is_admin)
                        <li><a @click="open = false" href="{{ route('admin.dashboard') }}" class="block rounded-lg px-4 py-3 font-medium transition hover:bg-slate-800 hover:text-blue-300">{{ __('Administration') }}</a></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="px-4 py-2">
                            @csrf
                            <button type="submit" class="font-medium text-red-300 transition hover:text-red-200">{{ __('Déconnexion') }}</button>
                        </form>
                    </li>
                @else
                    <li><a @click="open = false" href="{{ route('login') }}" class="block rounded-lg px-4 py-3 font-medium transition hover:bg-slate-800 hover:text-blue-300">{{ __('Se connecter') }}</a></li>
                    @if(Route::has('register'))
                        <li><a @click="open = false" href="{{ route('register') }}" class="block rounded-lg px-4 py-3 font-medium transition hover:bg-slate-800 hover:text-blue-300">{{ __('Créer un compte') }}</a></li>
                    @endif
                @endauth
            </ul>
        </div>
    </header>

    <!-- Page Content -->
    <main class="min-h-screen">
        @if(session('wishlist_message'))
            <div data-wishlist-notice role="status" aria-live="polite" class="fixed right-4 top-20 z-[60] max-w-sm rounded-lg bg-emerald-700 px-5 py-4 font-medium text-white shadow-xl">
                {{ session('wishlist_message') }}
            </div>
        @endif
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-gray-400 py-12 px-4 border-t border-slate-800">
        <div class="mx-auto mb-10 max-w-3xl text-center">
            <h2 class="text-xl font-bold text-white">{{ __('Recevez nos nouveautés') }}</h2>
            <p class="mt-2 text-sm">{{ __('Inscrivez-vous à la newsletter pour suivre les nouveaux produits et offres.') }}</p>
            @if(session('newsletter_success'))
                <p class="mt-3 text-sm text-green-300" role="status">{{ session('newsletter_success') }}</p>
            @endif
            @error('email')
                <p class="mt-3 text-sm text-red-300" role="alert">{{ $message }}</p>
            @enderror
            <form action="{{ route('newsletter.store') }}" method="POST" class="mx-auto mt-4 flex max-w-xl flex-col gap-2 sm:flex-row">
                @csrf
                <label for="newsletter-email" class="sr-only">Votre adresse e-mail</label>
                <input id="newsletter-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="vous@exemple.fr"
                    class="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white placeholder:text-gray-400">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700">{{ __('S’inscrire') }}</button>
            </form>
        </div>
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                @if($siteSettings->get('site_logo'))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($siteSettings->get('site_logo')) }}" alt="{{ $siteSettings->get('site_name', 'MonShop') }}" class="max-h-10 max-w-40 object-contain mb-4">
                @else
                    <h2 class="text-white text-lg font-bold mb-4">{{ $siteSettings->get('site_name', 'MonShop') }}</h2>
                @endif
                <p class="text-sm leading-relaxed">{{ App\Models\SiteSetting::getValue('footer_description', 'Votre boutique en ligne de confiance pour le gaming et la high-tech. Qualité, design et innovation.') }}</p>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-4">Liens rapides</h3>
                <a href="{{ route('home') }}" class="block mb-2 hover:text-blue-400 transition text-sm">Accueil</a>
                <a href="{{ route('catalog.index') }}" class="block mb-2 hover:text-blue-400 transition text-sm">Catalogue</a>
                <a href="{{ route('cart.index') }}" class="block mb-2 hover:text-blue-400 transition text-sm">Mon Panier</a>
                <a href="{{ route('pages.about') }}" class="block mb-2 hover:text-blue-400 transition text-sm">À propos</a>
                <a href="{{ route('pages.contact') }}" class="block mb-2 hover:text-blue-400 transition text-sm">Contact</a>
                <a href="{{ route('pages.legal') }}" class="block mb-2 hover:text-blue-4? transition text-sm">Mentions légales</a>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-4">Contact</h3>
                <p class="text-sm mb-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5 shrink-0" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7.5 12 13l9-5.5M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <a href="mailto:{{ App\Models\SiteSetting::getValue('footer_email', 'contact@monshop.com') }}" class="hover:text-white">
                        {{ App\Models\SiteSetting::getValue('footer_email', 'contact@monshop.com') }}
                    </a>
                </p>
                <p class="text-sm flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5 shrink-0" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 3h4l2 5-2.5 1.5a15 15 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2C10 21 3 14 3 5a2 2 0 0 1 2-2Z"/>
                    </svg>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', App\Models\SiteSetting::getValue('footer_phone', '01 23 45 67 89')) }}" class="hover:text-white">
                        {{ App\Models\SiteSetting::getValue('footer_phone', '01 23 45 67 89') }}
                    </a>
                </p>
                @php
                    $socialLinks = [
                        'Facebook' => ['key' => 'social_facebook', 'path' => 'M13.5 21v-8h2.7l.4-3h-3.1V8.1c0-.9.3-1.5 1.6-1.5h1.7V3.9c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3V10H7.3v3h2.8v8h3.4Z'],
                        'Instagram' => ['key' => 'social_instagram', 'path' => 'M7 2.8h10A4.2 4.2 0 0 1 21.2 7v10a4.2 4.2 0 0 1-4.2 4.2H7A4.2 4.2 0 0 1 2.8 17V7A4.2 4.2 0 0 1 7 2.8Zm5 4a5.2 5.2 0 1 0 0 10.4 5.2 5.2 0 0 0 0-10.4Zm0 2a3.2 3.2 0 1 1 0 6.4 3.2 3.2 0 0 1 0-6.4Zm5.4-3.2a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4Z'],
                        'WhatsApp' => ['key' => 'social_whatsapp', 'path' => 'M12 2.8a9.1 9.1 0 0 0-7.8 13.8L3 21l4.6-1.2A9.2 9.2 0 1 0 12 2.8Zm0 2a7.1 7.1 0 0 1 0 14.2 7 7 0 0 1-3.6-1l-.5-.3-2.1.6.6-2-.3-.5A7.1 7.1 0 0 1 12 4.8Zm-2.4 3c-.2 0-.5.1-.7.4-.2.2-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.8 2.8 4.4 3.8 2.1.8 2.5.6 3 .6.4-.1 1.3-.5 1.5-1.1.2-.6.2-1 .1-1.1-.1-.1-.3-.2-.6-.3l-1.5-.7c-.2-.1-.4-.1-.6.2l-.7.9c-.1.2-.3.2-.5.1-.3-.1-1.1-.4-2-1.2-.7-.6-1.2-1.4-1.3-1.7-.1-.2 0-.3.1-.5l.5-.6c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.7-1.6c-.2-.4-.4-.4-.7-.4Z'],
                        'TikTok' => ['key' => 'social_tiktok', 'path' => 'M16.6 3c.3 2 1.5 3.4 3.4 3.8v3.1a8.3 8.3 0 0 1-3.4-1.1v6.8a6 6 0 1 1-6-6 7 7 0 0 1 1.2.1v3.3a3 3 0 1 0 1.8 2.8V3h3Z'],
                    ];
                    $availableSocialLinks = collect($socialLinks)
                        ->map(fn (array $social) => ['url' => App\Models\SiteSetting::getValue($social['key']), 'path' => $social['path']])
                        ->filter(fn (array $social) => filled($social['url']));
                @endphp
                @if($availableSocialLinks->isNotEmpty())
                    <div class="mt-5">
                        <h3 class="text-white font-semibold mb-3">Suivez-nous</h3>
                        <div class="flex items-center gap-3">
                            @foreach($availableSocialLinks as $name => $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $name }}" title="{{ $name }}" class="text-gray-400 hover:text-white transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-6 w-6" fill="{{ $name === 'Facebook' || $name === 'TikTok' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $name === 'Facebook' || $name === 'TikTok' ? '0' : '1.8' }}" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $social['path'] }}"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="max-w-7xl mx-auto border-t border-slate-800 mt-8 pt-8">
            <p class="text-center text-xs text-gray-500">© {{ date('Y') }} {{ $siteSettings->get('site_name', 'MonShop') }}. Tous droits réservés.</p>
        </div>
    </footer>
    <a href="{{ route('cart.index') }}" aria-label="Voir mon panier" title="Voir mon panier" class="fixed bottom-6 right-6 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white shadow-xl transition hover:scale-105 hover:bg-blue-700">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-7 w-7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.2 11.2a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 1.9-1.4L22 8H6"/>
            <circle cx="10" cy="20" r="1"/>
            <circle cx="18" cy="20" r="1"/>
        </svg>
        <span data-cart-count class="absolute -right-1 -top-1 {{ $cartCount > 0 ? '' : 'hidden' }} flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-bold text-white">
            {{ $cartCount }}
        </span>
    </a>
    <div data-cart-notice role="status" aria-live="polite" class="fixed right-4 top-20 z-[60] hidden max-w-sm rounded-lg px-4 py-3 text-white shadow-lg"></div>
    <script>
        (() => {
            const notice = document.querySelector('[data-cart-notice]');
            let noticeTimeout;

            const currencySymbol = document.body.dataset.currencySymbol;
            const currencyDigits = Number(document.body.dataset.currencyDigits || 2);
            const numberLocale = document.body.dataset.appLocale === 'en' ? 'en-US' : 'fr-FR';
            const formatMoney = (amount) => {
                const formatted = new Intl.NumberFormat(numberLocale, {
                    minimumFractionDigits: currencyDigits,
                    maximumFractionDigits: currencyDigits,
                }).format(amount);
                return ['$', '£', '¥'].includes(currencySymbol)
                    ? `${currencySymbol}${formatted}`
                    : `${formatted}\u00a0${currencySymbol}`;
            };

            const showNotice = (message, isError = false) => {
                notice.textContent = message;
                notice.className = `fixed right-4 top-20 z-[60] max-w-sm rounded-lg px-4 py-3 text-white shadow-lg ${isError ? 'bg-red-700' : 'bg-green-700'}`;
                window.clearTimeout(noticeTimeout);
                noticeTimeout = window.setTimeout(() => notice.classList.add('hidden'), 3500);
            };

            const updateCartBadges = (count) => {
                document.querySelectorAll('[data-cart-count]').forEach((badge) => {
                    badge.textContent = count;
                    badge.classList.toggle('hidden', count < 1);
                });
            };

            const sendCartRequest = async (url, method, data) => {
                const requestUrl = new URL(url, window.location.href);
                if (requestUrl.origin !== window.location.origin) {
                    throw new Error('L’URL du panier ne correspond pas au domaine actuel. Vérifiez APP_URL dans Render.');
                }

                let response;
                try {
                    response = await fetch(requestUrl, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(data),
                    });
                } catch {
                    throw new Error('Connexion au panier impossible. Vérifiez que APP_URL utilise l’URL HTTPS de Render et réessayez.');
                }

                const responseBody = await response.text();
                let result = {};
                try {
                    result = responseBody ? JSON.parse(responseBody) : {};
                } catch {
                    if (response.status === 413) {
                        throw new Error('La requête est trop volumineuse. Réduisez la taille des fichiers envoyés.');
                    }
                    throw new Error(`Le serveur a répondu avec une page non JSON (HTTP ${response.status}). Consultez les journaux Render.`);
                }

                if (!response.ok) {
                    throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || `Erreur HTTP ${response.status}.`);
                }

                if (typeof result.cart_count !== 'number') {
                    throw new Error('Réponse inattendue du serveur pour la mise à jour du panier.');
                }

                return result;
            };

            document.addEventListener('submit', async (event) => {
                const form = event.target.closest('form[data-cart-add]');
                if (!form) return;

                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                button.disabled = true;

                try {
                    const formData = new FormData(form);
                    const result = await sendCartRequest(form.action, 'POST', {
                        quantity: Number(formData.get('quantity') || 1),
                    });
                    updateCartBadges(result.cart_count);
                    showNotice(result.message);
                } catch (error) {
                    showNotice(error.message, true);
                } finally {
                    button.disabled = false;
                }
            });

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-quantity-change]');
                if (!button) return;

                const row = button.closest('[data-cart-row]');
                const quantity = row.querySelector('[data-quantity-value]');
                const nextQuantity = Number(quantity.textContent) + Number(button.dataset.quantityChange);
                const stock = Number(quantity.dataset.stock);

                if (nextQuantity < 1) return;
                if (nextQuantity > stock) {
                    showNotice(@json(__('La quantité demandée dépasse le stock disponible.')), true);
                    return;
                }

                row.querySelectorAll('[data-quantity-change]').forEach((control) => control.disabled = true);
                try {
                    const result = await sendCartRequest(button.dataset.url, 'PUT', { quantity: nextQuantity });
                    quantity.textContent = result.quantity;
                    const subtotal = Number(row.dataset.unitPrice) * result.quantity;
                    const subtotalElement = row.querySelector('[data-cart-subtotal]');
                    subtotalElement.dataset.rawAmount = subtotal;
                    subtotalElement.textContent = formatMoney(subtotal);
                    let total = 0;
                    document.querySelectorAll('[data-cart-row]').forEach((cartRow) => {
                        total += Number(cartRow.dataset.unitPrice) *
                            Number(cartRow.querySelector('[data-quantity-value]').textContent);
                    });
                    const totalElement = document.querySelector('[data-cart-total]');
                    totalElement.dataset.rawAmount = total;
                    totalElement.textContent = formatMoney(total);
                    updateCartBadges(result.cart_count);
                } catch (error) {
                    showNotice(error.message, true);
                } finally {
                    row.querySelectorAll('[data-quantity-change]').forEach((control) => control.disabled = false);
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>