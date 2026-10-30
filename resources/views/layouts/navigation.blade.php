<nav class="fixed top-0 left-0 w-full z-50 bg-white/70 backdrop-blur-md shadow-sm py-4 px-6 flex items-center justify-between">
    <!-- Logo -->
    <div class="flex items-center space-x-2">
        <a href="{{ route('home') }}" class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200">
            <x-application-logo />
        </a>
        <span class="font-semibold text-xl">{{ config('app.name', 'Laravel') }}</span>
    </div>

    <!-- Navigation Links -->
    <div class="hidden sm:flex space-x-8">
        <x-nav-link :href="route('home')" :active="request()->routeIs('home')">{{ __('Accueil') }}</x-nav-link>
        @auth
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
        @endauth
    </div>

    <!-- Right side: cart & auth -->
    <div class="flex items-center space-x-4">
        <!-- Cart Icon with badge -->
        <a href="{{ route('cart.index') }}" class="relative group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 hover:text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-1.5 9H4.5L3 3z" /><circle cx="7.5" cy="21.5" r="1.5" /><circle cx="16.5" cy="21.5" r="1.5" /></svg>
            @if(session('cart.total'))
                <span class="absolute -top-2 -right-3 bg-red-600 text-white rounded-full text-xs px-1">{{ session('cart.total') }}</span>
            @endif
        </a>

        <!-- Authentication Links -->
        @guest
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('Log in') }}</a>
            <a href="{{ route('register') }}" class="ml-4 text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('Register') }}</a>
        @else
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                        {{ Auth::user()->name }}
                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        @endguest
    </div>
</nav>