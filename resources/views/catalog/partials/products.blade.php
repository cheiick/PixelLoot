@if($products->isNotEmpty())
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($products as $product)
            @php($isWishlisted = (bool) ($product->is_wishlisted ?? false))
            <article data-aos="fade-up" class="storefront-product-card group overflow-hidden transition duration-300 hover:-translate-y-1 dark:bg-slate-900">
                <a href="{{ route('products.show', $product) }}" class="block">
                    <div class="aspect-square overflow-hidden bg-stone-100 dark:bg-slate-800">
                        @if($product->image_path)
                            <img src="{{ \App\Support\ImageStorage::url($product->image_path) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-contain p-5 transition-transform duration-500 group-hover:scale-105">
                        @elseif($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-contain p-5 transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center text-gray-400">Image non disponible</div>
                        @endif
                    </div>
                </a>
                <div class="p-5">
                    <div class="flex items-center justify-between gap-2">
                        @if($product->brand)<span class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">{{ $product->brand }}</span>@endif
                        @if($product->category)<span class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category->name }}</span>@endif
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="mt-1 block">
                        <h2 class="line-clamp-2 text-base font-bold leading-6 text-gray-900 transition group-hover:text-rose-600 dark:text-white">{{ $product->name }}</h2>
                    </a>
                    <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-4 dark:border-slate-700">
                        <span class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">@money($product->price)</span>
                        <span class="rounded-full px-2 py-1 text-xs {{ $product->stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $product->stock > 0 ? 'En stock' : 'Rupture' }}</span>
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="mt-3 block text-sm font-semibold text-slate-500 transition hover:text-rose-600">Voir le détail →</a>
                    @if($product->stock > 0)
                        <form action="{{ route('cart.add', $product) }}" method="POST" data-cart-add class="mt-2">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="storefront-primary-button w-full rounded-lg py-3 font-bold text-white transition">{{ __('Ajouter au panier') }}</button>
                        </form>
                    @endif
                    @auth
                        <form action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" method="POST" class="mt-2">
                            @csrf
                            @if($isWishlisted) @method('DELETE') @endif
                            <button type="submit" class="w-full rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700 transition hover:border-rose-200 hover:text-rose-600 dark:border-slate-700 dark:text-gray-200 dark:hover:bg-slate-800">
                                {{ $isWishlisted ? '♥ '.__('Déjà dans vos favoris · Retirer') : '♡ '.__('Ajouter aux favoris') }}
                            </button>
                        </form>
                    @endauth
                </div>
            </article>
        @endforeach
    </div>
    <div class="mt-8">{{ $products->links() }}</div>
@else
    <div class="rounded-xl bg-white p-12 text-center shadow dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Aucun produit trouvé') }}</h2>
        <p class="mt-2 text-gray-600 dark:text-gray-300">{{ __('Essayez de modifier vos filtres.') }}</p>
    </div>
@endif
