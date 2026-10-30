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
        class="relative isolate flex min-h-[540px] h-[70vh] max-h-[800px] w-full items-center overflow-hidden bg-gradient-to-r from-gray-950 to-blue-950"
        x-data="{ activeSlide: 0, slideCount: {{ count($heroImages) }} }"
        x-init="if (slideCount > 1) setInterval(() => activeSlide = (activeSlide + 1) % slideCount, 5000)"
        aria-label="Bannière d’accueil"
    >
        @foreach($heroImages as $index => $image)
            <img
                src="{{ asset('storage/' . $image) }}"
                alt=""
                class="absolute inset-0 h-full w-full object-cover transition-opacity duration-1000"
                :class="activeSlide === {{ $index }} ? 'opacity-100' : 'opacity-0'"
                aria-hidden="true"
                @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
            >
        @endforeach
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/55 to-black/20" aria-hidden="true"></div>
        <div class="relative z-10 mx-auto w-full max-w-7xl px-6 py-20 lg:px-8">
            <div class="max-w-3xl">
                <h1 class="text-5xl md:text-6xl font-bold text-white leading-tight">
                    {{ $siteSettings->get('hero_title', 'Gaming & High-Tech') }}
                </h1>
                <p class="text-xl text-blue-100 mt-6 max-w-lg">
                    {{ $siteSettings->get('hero_description', 'Découvrez les derniers jeux, consoles et accessoires tech au meilleur prix. Livraison rapide et garantie incluse.') }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('catalog.index') }}" class="inline-flex items-center justify-center rounded-full bg-blue-600 px-8 py-4 font-bold text-white shadow-lg transition hover:bg-blue-700 hover:shadow-blue-500/50">
                        {{ __('Voir le catalogue') }}
                    </a>
                    @guest
                        @if(Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full border border-white/70 bg-white/10 px-8 py-4 font-bold text-white backdrop-blur transition hover:bg-white hover:text-slate-950">
                                Créer un compte
                            </a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
        @if(count($heroImages) > 1)
            <div class="absolute bottom-6 left-1/2 z-20 flex -translate-x-1/2 gap-2" aria-label="Sélectionner une image de bannière">
                @foreach($heroImages as $index => $image)
                    <button
                        type="button"
                        @click="activeSlide = {{ $index }}"
                        :aria-current="activeSlide === {{ $index }} ? 'true' : 'false'"
                        aria-label="Afficher l’image {{ $index + 1 }}"
                        class="h-2.5 w-2.5 rounded-full border border-white transition"
                        :class="activeSlide === {{ $index }} ? 'bg-white' : 'bg-white/40'"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>

    <!-- 2. SECTION CATÉGORIES -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">{{ __('Nos Catégories') }}</h2>
            
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
                           class="w-[calc(100%-1rem)] shrink-0 snap-start rounded-xl border border-gray-100 bg-white p-6 text-center shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl group sm:w-[calc((100%-1rem)/2)] lg:w-[calc((100%-2rem)/3)]">
                            <div class="w-16 h-16 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-100 transition">
                                <span class="text-2xl">🏷️</span>
                            </div>
                            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">
                                {{ $category->name }}
                            </h3>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500">{{ __('Les catégories seront affichées ici.') }}</p>
            @endif
        </div>
    </section>

    <!-- 3. SECTION PRODUITS EN VEDETTE -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">{{ __('Nos Coups de Cœur') }}</h2>
                    <p class="text-gray-600 mt-2">{{ __('Une sélection de nos meilleurs produits du moment') }}</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-blue-600 font-semibold hover:text-blue-700 hover:underline hidden sm:block">
                    {{ __('Voir tout le catalogue →') }}
                </a>
            </div>

            @if(isset($featuredProducts) && $featuredProducts->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($featuredProducts as $product)
                        @php($isWishlisted = (bool) ($product->is_wishlisted ?? false))
                        <div class="bg-white rounded-xl shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden border border-gray-100 group">
                            <a href="{{ route('products.show', $product) }}" class="block">
                                <div class="aspect-square bg-gray-100 overflow-hidden relative">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    @elseif($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400 text-4xl">📦</div>
                                    @endif
                                    @if($product->stock <= 5 && $product->stock > 0)
                                        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                            Plus que {{ $product->stock }} !
                                        </span>
                                    @endif
                                </div>
                            </a>
                            <div class="p-4">
                                @if($product->category)
                                    <span class="text-xs text-blue-600 font-semibold uppercase tracking-wide">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                                <a href="{{ route('products.show', $product) }}" class="block mt-1">
                                    <h3 class="text-base font-semibold text-gray-900 hover:text-blue-600 transition line-clamp-2 h-12">
                                        {{ $product->name }}
                                    </h3>
                                </a>
                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xl font-bold text-gray-900">
                                        @money($product->price)
                                    </span>
                                </div>
                                <a href="{{ route('products.show', $product) }}" class="mt-4 block w-full text-center bg-gray-900 text-white py-2 rounded-lg hover:bg-blue-600 transition font-medium text-sm">
                                    {{ __('Voir le détail') }}
                                </a>
                                @if($product->stock > 0)
                                    <form action="{{ route('cart.add', $product) }}" method="POST" data-cart-add class="mt-2">
                                        @csrf
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-medium text-sm">
                                            {{ __('Ajouter au panier') }}
                                        </button>
                                    </form>
                                @endif
                                @auth
                                    <form action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" method="POST" class="mt-2">
                                        @csrf
                                        @if($isWishlisted) @method('DELETE') @endif
                                        <button type="submit" class="w-full rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            {{ $isWishlisted ? __('Déjà dans vos favoris · Retirer') : __('Ajouter aux favoris') }}
                                        </button>
                                    </form>
                                @endauth
                            </div>
                        </div>
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