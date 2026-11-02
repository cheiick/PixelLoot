@extends('layouts.app')

@section('content')
@section('title', $siteSettings->get('site_name', 'MonShop').' - Boutique Gaming & High-Tech')
@section('meta_description', $siteSettings->get('hero_description', 'Découvrez notre sélection de jeux, consoles et accessoires high-tech.'))

    @php
        $heroImages = json_decode($siteSettings->get('hero_images', '[]'), true);
        if (! is_array($heroImages)) {
            $legacyHeroImage = $siteSettings->get('hero_image');
            $heroImages = $legacyHeroImage ? [$legacyHeroImage] : [];
        }
    @endphp

    <section
        class="relative isolate flex min-h-[460px] w-full items-center overflow-hidden bg-slate-950 sm:min-h-[540px] lg:min-h-[620px]"
        x-data="{ activeSlide: 0, slideCount: {{ count($heroImages) }} }"
        x-init="if (slideCount > 1) setInterval(() => activeSlide = (activeSlide + 1) % slideCount, 5000)"
        aria-label="Bannière d’accueil"
    >
        @foreach($heroImages as $index => $image)
            <img
                src="{{ \App\Support\ImageStorage::url($image) }}"
                alt=""
                class="absolute inset-0 h-full w-full object-cover transition-opacity duration-1000"
                :class="activeSlide === {{ $index }} ? 'opacity-100' : 'opacity-0'"
                aria-hidden="true"
                @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
            >
        @endforeach
        <div class="absolute inset-0 bg-gradient-to-r from-slate-950/90 via-slate-950/55 to-slate-950/15" aria-hidden="true"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/50 via-transparent to-slate-950/15" aria-hidden="true"></div>
        <div class="relative z-10 mx-auto grid w-full max-w-7xl items-center px-6 pb-20 pt-16 lg:px-8 lg:py-24">
            <div class="max-w-2xl">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/30 bg-slate-950/30 px-4 py-2 text-xs font-bold uppercase tracking-[0.16em] text-white backdrop-blur-sm sm:text-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-400" aria-hidden="true"></span>
                    Gaming <span class="text-rose-300">·</span> High-Tech <span class="text-rose-300">·</span> Nouveautés
                </div>
                <h1 class="break-words text-4xl font-extrabold leading-[1.08] tracking-tight text-white drop-shadow-lg sm:text-5xl md:text-6xl">
                    {{ $siteSettings->get('hero_title', 'Gaming & High-Tech') }}
                </h1>
                <div class="mt-6 h-0.5 w-16 bg-rose-400" aria-hidden="true"></div>
                <p class="mt-6 max-w-xl text-base leading-7 text-white/90 sm:text-lg sm:leading-8">
                    {{ $siteSettings->get('hero_description', 'Découvrez les derniers jeux, consoles et accessoires tech au meilleur prix. Livraison rapide et garantie incluse.') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3 sm:gap-4">
                    <a href="{{ route('catalog.index') }}" class="group inline-flex min-h-12 items-center justify-center gap-3 rounded-full bg-rose-600 px-6 py-3 text-center font-bold text-white shadow-lg transition duration-300 hover:-translate-y-0.5 hover:bg-rose-700 sm:px-8 sm:py-4">
                        {{ __('Voir le catalogue') }}
                        <svg class="h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                    @guest
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/60 bg-white/10 px-6 py-3 text-center font-bold text-white backdrop-blur-sm transition hover:bg-white hover:text-slate-950 sm:px-8 sm:py-4">
                                Créer un compte
                            </a>
                        @endif
                    @endguest
                </div>
                <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-sm font-medium text-white/90">
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l8 4v5c0 5-3.4 8-8 9-4.6-1-8-4-8-9V7l8-4z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4"/>
                        </svg>
                        Paiement sécurisé
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"/>
                            <circle cx="7.5" cy="18" r="1.5"/>
                            <circle cx="17.5" cy="18" r="1.5"/>
                        </svg>
                        Livraison disponible
                    </span>
                </div>
            </div>

        </div>
        @if(count($heroImages) > 1)
            <div class="absolute bottom-7 left-1/2 z-20 flex -translate-x-1/2 gap-2 rounded-full border border-white/15 bg-slate-950/40 px-3 py-2 backdrop-blur-md" aria-label="Sélectionner une image de bannière">
                @foreach($heroImages as $index => $image)
                    <button
                        type="button"
                        @click="activeSlide = {{ $index }}"
                        :aria-current="activeSlide === {{ $index }} ? 'true' : 'false'"
                        aria-label="Afficher l’image {{ $index + 1 }}"
                        class="h-2.5 rounded-full border border-white/80 transition-all duration-300"
                        :class="activeSlide === {{ $index }} ? 'w-8 bg-white' : 'w-2.5 bg-white/40 hover:bg-white/70'"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>

    <!-- 2. SECTION CATÉGORIES -->
    <section class="storefront-page-section bg-stone-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-rose-600">{{ __('Explorez la boutique') }}</p>
                    <h2 class="storefront-section-heading">{{ __('Nos catégories') }}</h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-sm font-bold text-slate-800 transition hover:text-rose-600">{{ __('Toutes les catégories') }} <span aria-hidden="true">→</span></a>
            </div>
            
            <!-- Vérifie que le contrôleur passe bien la variable $categories -->
            @if(isset($categories) && $categories->count() > 0)
                @if($categories->count() > 1)
                    <div class="mb-4 flex items-center justify-end gap-2">
                        <button type="button" data-category-scroll="-1" aria-label="Afficher les catégories précédentes" class="rounded-full border border-gray-300 bg-white px-4 py-2 text-gray-700 shadow-sm hover:bg-gray-100">←</button>
                        <button type="button" data-category-scroll="1" aria-label="Afficher les catégories suivantes" class="rounded-full border border-gray-300 bg-white px-4 py-2 text-gray-700 shadow-sm hover:bg-gray-100">→</button>
                    </div>
                @endif
                <div data-category-carousel class="flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-3" aria-label="Catégories de produits">
                    @foreach($categories as $category)
                        <a href="{{ route('catalog.index', ['category' => $category->id]) }}"
                           class="storefront-category-card group flex w-[calc(100%-1rem)] shrink-0 snap-start items-center gap-5 p-5 transition-all duration-300 hover:-translate-y-1 sm:w-[calc((100%-1rem)/2)] lg:w-[calc((100%-2rem)/3)]">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-stone-100 text-rose-600 transition group-hover:bg-rose-50">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M20 13 13 20a2 2 0 0 1-2.8 0L4 13V4h9l7 7a2 2 0 0 1 0 2Z"/>
                                    <circle cx="8.5" cy="8.5" r="1"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 transition group-hover:text-rose-600">{{ $category->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ __('Parcourir la sélection') }} <span aria-hidden="true">→</span></p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500">{{ __('Les catégories seront affichées ici.') }}</p>
            @endif
        </div>
    </section>

    <!-- 3. SECTION PRODUITS EN VEDETTE -->
    <section class="storefront-page-section bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-rose-600">{{ __('La sélection du moment') }}</p>
                    <h2 class="storefront-section-heading">{{ __('Nos coups de cœur') }}</h2>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Une sélection de nos meilleurs produits du moment') }}</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="hidden text-sm font-bold text-slate-800 transition hover:text-rose-600 sm:block">
                    {{ __('Voir tout le catalogue →') }}
                </a>
            </div>

            @if(isset($featuredProducts) && $featuredProducts->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($featuredProducts as $product)
                        @php($isWishlisted = (bool) ($product->is_wishlisted ?? false))
                        <article class="storefront-product-card group overflow-hidden transition-all duration-300 hover:-translate-y-1">
                            <a href="{{ route('products.show', $product) }}" class="block">
                                <div class="relative aspect-square overflow-hidden bg-stone-100">
                                    @if($product->image_path)
                                        <img src="{{ \App\Support\ImageStorage::url($product->image_path) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-contain p-5 transition-transform duration-500 group-hover:scale-105">
                                    @elseif($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-contain p-5 transition-transform duration-500 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-300">
                                            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9ZM12 12l8-4.5M12 12v9m0-9L4 7.5m4-2.25 8 4.5"/></svg>
                                        </div>
                                    @endif
                                    @if($product->stock <= 5 && $product->stock > 0)
                                        <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-rose-600 shadow-sm">
                                            Plus que {{ $product->stock }} !
                                        </span>
                                    @endif
                                </div>
                            </a>
                            <div class="p-5">
                                @if($product->category)
                                    <span class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                <a href="{{ route('products.show', $product) }}" class="mt-2 block">
                                    <h3 class="h-12 line-clamp-2 text-base font-bold leading-6 text-slate-900 transition group-hover:text-rose-600">
                                        {{ $product->name }}
                                    </h3>
                                </a>
                                <div class="mt-3 flex items-center justify-between border-t border-stone-100 pt-4">
                                    <span class="text-xl font-extrabold tracking-tight text-slate-900">
                                        @money($product->price)
                                    </span>
                                    <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-slate-500 transition hover:text-rose-600">{{ __('Voir le détail') }} →</a>
                                </div>
                                @if($product->stock > 0)
                                    <form action="{{ route('cart.add', $product) }}" method="POST" data-cart-add class="mt-4">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="storefront-primary-button w-full rounded-lg py-3 text-sm font-bold text-white transition">
                                            {{ __('Ajouter au panier') }}
                                        </button>
                                    </form>
                                @endif
                                @auth
                                    <form action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" method="POST" class="mt-2">
                                        @csrf
                                        @if($isWishlisted) @method('DELETE') @endif
                                        <button type="submit" class="w-full rounded-lg border border-stone-200 py-2 text-sm font-medium text-slate-600 transition hover:border-rose-200 hover:text-rose-600">
                                            {{ $isWishlisted ? __('Déjà dans vos favoris · Retirer') : __('Ajouter aux favoris') }}
                                        </button>
                                    </form>
                                @endauth
                            </article>
                    @endforeach
                </div>
                
                <div class="mt-8 text-center sm:hidden">
                    <a href="{{ route('catalog.index') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        {{ __('Voir tout le catalogue') }}
                    </a>
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-xl">
                    <p class="text-gray-500">{{ __('Les produits en vedette apparaîtront ici.') }}</p>
                </div>
            @endif
        </div>
    </section>
@endsection