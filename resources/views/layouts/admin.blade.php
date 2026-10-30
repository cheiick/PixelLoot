<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $siteSettings->get('site_name', 'MonShop') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({ duration: 800, once: true });
    });
</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <header class="fixed inset-x-0 top-0 z-40 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 shadow-sm sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button type="button" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen.toString()" aria-controls="admin-sidebar" aria-label="Ouvrir le menu d’administration" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="truncate text-lg font-bold text-gray-900 sm:text-xl">
                {{ $siteSettings->get('site_name', 'MonShop') }} <span class="text-blue-700">Admin</span>
            </a>
        </div>
        <div class="flex shrink-0 items-center gap-2 sm:gap-4">
            <span class="hidden max-w-40 truncate text-sm font-medium text-gray-700 sm:inline">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-700 sm:px-4">Déconnexion</button>
            </form>
        </div>
    </header>

    <div x-show="sidebarOpen" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-gray-950/50 lg:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>

    <aside id="admin-sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed bottom-0 left-0 top-16 z-50 flex w-72 max-w-[85vw] flex-col border-r border-gray-200 bg-white shadow-xl transition-transform duration-200 ease-in-out lg:translate-x-0">
        <div class="border-b border-gray-100 px-5 py-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Espace de gestion</p>
            <p class="mt-1 truncate font-semibold text-gray-900">{{ Auth::user()->name }}</p>
        </div>
        <nav aria-label="Navigation d’administration" class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-3 py-4">
            <ul class="space-y-1">
                <li><a @click="sidebarOpen = false" href="{{ route('admin.dashboard') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.dashboard'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.dashboard')])><span aria-hidden="true">⌂</span>Dashboard</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.products.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.products.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.products.*')])><span aria-hidden="true">▣</span>Produits</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.categories.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.categories.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.categories.*')])><span aria-hidden="true">▦</span>Catégories</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.orders.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.orders.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.orders.*')])><span aria-hidden="true">▤</span>Commandes</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.coupons.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.coupons.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.coupons.*')])><span aria-hidden="true">％</span>Codes promo</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.messages.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.messages.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.messages.*')])><span aria-hidden="true">✉</span>Messages</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.reviews.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.reviews.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.reviews.*')])><span aria-hidden="true">★</span>Avis clients</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.newsletter.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.newsletter.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.newsletter.*')])><span aria-hidden="true">✉</span>Newsletter</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.admin-users.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.admin-users.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.admin-users.*')])><span aria-hidden="true">♙</span>Administrateurs</a></li>
                <li><a @click="sidebarOpen = false" href="{{ route('admin.settings.index') }}" @class(['flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-blue-50 text-blue-700' => request()->routeIs('admin.settings.*'), 'text-gray-700 hover:bg-gray-100' => !request()->routeIs('admin.settings.*')])><span aria-hidden="true">⚙</span>Paramètres</a></li>
            </ul>
        </nav>
        <div class="border-t border-gray-100 p-3">
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Voir la boutique ↗</a>
        </div>
    </aside>

    <main class="mt-16 min-h-[calc(100vh-8rem)] px-4 py-6 sm:px-6 sm:py-8 lg:ml-72 lg:px-8">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white px-4 py-4 sm:px-6 lg:ml-72">
        <div class="text-center text-xs text-gray-500">© {{ date('Y') }} {{ $siteSettings->get('site_name', 'MonShop') }} Admin. Tous droits réservés.</div>
    </footer>
</body>
</html>
