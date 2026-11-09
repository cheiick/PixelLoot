<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $siteSettings->get('site_name', 'MonShop') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="admin-shell min-h-screen font-sans antialiased" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <header class="admin-topbar fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen.toString()" aria-controls="admin-sidebar" aria-label="Ouvrir le menu d’administration" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="truncate text-lg font-extrabold tracking-tight text-gray-900 sm:text-xl">
                {{ $siteSettings->get('site_name', 'MonShop') }} <span class="text-rose-600">Admin</span>
            </a>
        </div>
        <div class="flex shrink-0 items-center gap-2 sm:gap-4">
            <span class="hidden max-w-40 truncate text-sm font-medium text-gray-700 sm:inline">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-rose-600 sm:px-4">Déconnexion</button>
            </form>
        </div>
    </header>

    <div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-gray-950/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    <aside id="admin-sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="admin-sidebar fixed bottom-0 left-0 top-16 z-50 flex w-72 max-w-[85vw] flex-col border-r shadow-xl transition-transform duration-200 ease-in-out lg:translate-x-0">
        <div class="border-b border-white/10 px-5 py-5">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Espace de gestion</p>
            <p class="mt-1 truncate font-semibold text-white">{{ Auth::user()->name }}</p>
        </div>
        <nav aria-label="Navigation d’administration" class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-4">
            <ul class="space-y-1">
                <li><a @click="sidebarOpen = false" href="{{ route('admin.dashboard') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.dashboard')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z"/></svg>Dashboard</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.products.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.products.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm0 9 8-4.5M12 12v9m0-9L4 7.5"/></svg>Produits</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.categories.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.categories.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M20 13 13 20a2 2 0 0 1-2.8 0L4 13V4h9l7 7a2 2 0 0 1 0 2Z"/><circle cx="8.5" cy="8.5" r="1"/></svg>Catégories</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.orders.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.orders.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 3h10v3h3v15H4V6h3V3Zm0 3h10M8 11h8m-8 4h8"/></svg>Commandes</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.coupons.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.coupons.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 7V5h16v2a2 2 0 0 0 0 4v2a2 2 0 0 0 0 4v2H4v-2a2 2 0 0 0 0-4v-2a2 2 0 0 0 0-4Z"/></svg>Codes promo</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.messages.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.messages.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 5h16v12H9l-5 4V5Z"/></svg>Messages</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.reviews.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.reviews.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>Avis clients</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.newsletter.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.newsletter.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m3 6 9 7 9-7M5 5h14a2 2 0 0 1 2 2v10H3V7a2 2 0 0 1 2-2Z"/></svg>Newsletter</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.admin-users.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.admin-users.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 20v-1a6 6 0 0 1 12 0v1m2-9a3 3 0 0 0 0-6m1 9a5 5 0 0 1 3 4v1"/></svg>Administrateurs</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.settings.index') }}" @class(['admin-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'is-active' => request()->routeIs('admin.settings.*')])><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.7-1.4-2.4 1.4-1.1a7 7 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.7 1.4 2.4-1.4 1.1a7 7 0 0 1 0 1.8Z"/></svg>Paramètres</a></li>
            </ul>
        </nav>
        <div class="border-t border-white/10 p-3">
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center rounded-xl border border-white/20 px-4 py-3 text-sm font-semibold text-white transition hover:border-white/40 hover:bg-white/10">Voir la boutique ↗</a>
        </div>
    </aside>

    <main class="admin-content mt-16 min-h-[calc(100vh-8rem)] min-w-0 px-4 py-6 sm:px-6 sm:py-8 lg:ml-72 lg:px-8">
        @yield('content')
    </main>

    <footer class="border-t border-stone-200 bg-white px-4 py-4 sm:px-6 lg:ml-72">
        <div class="text-center text-xs text-gray-500">© {{ date('Y') }} {{ $siteSettings->get('site_name', 'MonShop') }} Admin. Tous droits réservés.</div>
    </footer>
</body>
</html>
