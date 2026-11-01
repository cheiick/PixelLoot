@extends('layouts.app')

@section('content')
@section('title', $product->name.' - '.\App\Models\SiteSetting::getValue('site_name', 'MonShop'))
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->description ?: 'Découvrez '.$product->name.' dans notre boutique.'), 160))
@section('og_type', 'product')
@if($product->image_path || $product->image_url)
    @section('meta_image', $product->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) : $product->image_url)
@endif

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        @if($errors->has('review'))
            <div class="mb-6 rounded-xl bg-red-50 p-4 text-red-700" role="alert">{{ $errors->first('review') }}</div>
        @endif
        @if(session('review_success'))
            <div class="mb-6 rounded-xl bg-green-50 p-4 text-green-700" role="status">{{ session('review_success') }}</div>
        @endif
        <nav class="mb-8 text-sm text-gray-500" aria-label="Fil d’Ariane">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ route('home') }}" class="transition hover:text-blue-700">{{ __('Accueil') }}</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ route('catalog.index') }}" class="transition hover:text-blue-700">{{ __('Catalogue') }}</a></li>
                @if($category)
                    <li aria-hidden="true">/</li>
                    <li class="text-gray-700">{{ $category->name }}</li>
                @endif
            </ol>
        </nav>

        <div class="grid gap-8 lg:grid-cols-2 lg:gap-14">
            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
                <div class="relative aspect-square bg-gray-50">
                    @if($product->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}" alt="{{ $product->name }}" fetchpriority="high" class="h-full w-full object-contain p-6 sm:p-10">
                    @elseif($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" fetchpriority="high" class="h-full w-full object-contain p-6 sm:p-10">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-gray-300">
                            <svg class="h-28 w-28" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    @if($product->stock > 0 && $product->stock <= 5)
                        <span class="absolute left-5 top-5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ __('Stock disponible : :stock unité(s)', ['stock' => $product->stock]) }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-col py-2 lg:py-5">
                @if($category)
                    <a href="{{ route('catalog.index', ['category' => $category->id]) }}" class="mb-3 w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 transition hover:bg-blue-100">
                        {{ $category->name }}
                    </a>
                @endif
                @if($product->brand)
                    <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-blue-700">{{ $product->brand }}</p>
                @endif

                <h1 class="break-words text-2xl font-bold tracking-tight text-gray-950 sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-amber-600" aria-label="Note moyenne">
                    @if($reviewCount)
                        {{ str_repeat('★', (int) round($averageRating)) }}{{ str_repeat('☆', 5 - (int) round($averageRating)) }}
                        <span class="text-gray-600">{{ number_format($averageRating, 1, ',', '') }}/5 ({{ $reviewCount }} avis)</span>
                    @else
                        <span class="text-gray-500">{{ __('Aucun avis pour le moment') }}</span>
                    @endif
                </p>

                <div class="mt-5 flex flex-wrap items-end justify-between gap-4 border-b border-gray-100 pb-6">
                    <p class="break-words text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">@money($product->price)</p>
                    @if($product->stock > 0)
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-700">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ __('En stock') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            {{ __('Indisponible') }}
                        </span>
                    @endif
                    @auth
                        <form action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" method="POST" class="mt-4">
                            @csrf
                            @if($isWishlisted) @method('DELETE') @endif
                            <button type="submit" class="rounded-xl border border-gray-300 px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                                {{ $isWishlisted ? '♥ '.__('Déjà dans vos favoris · Retirer') : '♡ '.__('Ajouter aux favoris') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="mt-4 inline-block text-sm font-medium text-blue-700 hover:underline">{{ __('Connectez-vous pour ajouter ce produit à votre wishlist') }}</a>
                    @endauth
                </div>

                <div class="py-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-900">{{ __('Description') }}</h2>
                    <p class="whitespace-pre-line leading-7 text-gray-600">{{ $product->description ?: __('Découvrez ce produit de notre sélection.') }}</p>
                </div>

                @if($product->stock > 0)
                    <form action="{{ route('cart.add', $product) }}" method="POST" data-cart-add class="mt-auto rounded-2xl bg-gray-50 p-4 sm:p-5">
                        @csrf
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                            <div>
                                <label for="quantity" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Quantité') }}</label>
                                <input
                                    type="number"
                                    name="quantity"
                                    id="quantity"
                                    value="1"
                                    min="1"
                                    max="{{ $product->stock }}"
                                    required
                                    class="h-12 w-28 rounded-xl border border-gray-300 bg-white px-3 text-center font-semibold text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                >
                            </div>
                            @auth
                                <button type="submit" class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l2.2 11.2a2 2 0 002 1.6h8.7a2 2 0 001.9-1.4L22 8H6"/>
                                        <circle cx="10" cy="20" r="1"/>
                                        <circle cx="18" cy="20" r="1"/>
                                    </svg>
                                    Ajouter au panier
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex h-12 flex-1 items-center justify-center rounded-xl bg-blue-600 px-6 text-center font-semibold text-white shadow-sm transition hover:bg-blue-700">
                                    Connectez-vous pour commander
                                </a>
                            @endauth
                        </div>
                        <p class="mt-3 text-xs text-gray-500">Stock disponible : {{ $product->stock }} unité(s)</p>
                    </form>
                @else
                    <div class="mt-auto rounded-2xl bg-gray-50 p-5">
                        <p class="font-medium text-gray-700">Ce produit est actuellement indisponible.</p>
                    </div>
                @endif

                <a href="{{ route('catalog.index') }}" class="mt-5 inline-flex w-fit items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-blue-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/>
                    </svg>
                    Continuer les achats
                </a>
            </div>
        </div>

        <section class="mt-14 border-t border-gray-200 pt-10" aria-labelledby="reviews-title">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="reviews-title" class="text-2xl font-bold text-gray-900">Avis clients</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $reviewCount }} avis vérifié(s)</p>
                </div>
            </div>
            @auth
                @if($canReview)
                    <form action="{{ route('products.reviews.store', $product) }}" method="POST" class="mt-6 max-w-2xl space-y-4 rounded-2xl bg-gray-50 p-5">
                        @csrf
                        <div>
                            <label for="rating" class="mb-2 block text-sm font-semibold text-gray-800">Votre note</label>
                            <select id="rating" name="rating" required class="rounded-lg border-gray-300">
                                @foreach(range(5, 1) as $rating)
                                    <option value="{{ $rating }}" @selected(old('rating') == $rating)>{{ $rating }} / 5</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="review-comment" class="mb-2 block text-sm font-semibold text-gray-800">Votre commentaire</label>
                            <textarea id="review-comment" name="comment" rows="4" minlength="10" maxlength="3000" required class="w-full rounded-lg border-gray-300" placeholder="Partagez votre expérience...">{{ old('comment') }}</textarea>
                        </div>
                        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 font-semibold text-white hover:bg-blue-700">Envoyer mon avis</button>
                    </form>
                @endif
            @else
                <p class="mt-4 text-sm text-gray-600"><a class="text-blue-700 hover:underline" href="{{ route('login') }}">Connectez-vous</a> pour laisser un avis après votre achat.</p>
            @endauth

            <div class="mt-8 space-y-4">
                @forelse($reviews as $review)
                    <article data-aos="fade-up" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-semibold text-gray-900">{{ $review->user->name }}</p>
                            <span class="text-amber-600" aria-label="{{ $review->rating }} sur 5">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        </div>
                        <p class="mt-3 whitespace-pre-line text-gray-700">{{ $review->comment }}</p>
                        <p class="mt-3 text-xs text-gray-500">{{ $review->created_at->format('d/m/Y') }}</p>
                    </article>
                @empty
                    <p class="text-gray-600">Soyez le premier à donner votre avis après votre achat.</p>
                @endforelse
            </div>
            <div class="mt-6">{{ $reviews->links() }}</div>
        </section>
    </div>
@endsection

@push('structured_data')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => \Illuminate\Support\Str::limit(strip_tags($product->description ?? ''), 500),
    'image' => $product->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) : $product->image_url,
    'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand] : null,
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => app(\App\Support\CurrencyFormatter::class)->code(),
        'price' => number_format((float) $product->price, 2, '.', ''),
        'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => route('products.show', $product),
    ],
], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush
