@if($products->isNotEmpty())
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($products as $product)
            @php($isWishlisted = (bool) ($product->is_wishlisted ?? false))
            <article data-aos="fade-up" class="group overflow-hidden rounded-xl bg-white shadow transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-slate-900">
                <a href="{{ route('products.show', $product) }}" class="block">
                    <div class="aspect-square overflow-hidden bg-gray-100 dark:bg-slate-800">
                        @if($product->image_path)
                            <img src="{{ \App\Support\ImageStorage::url($product->image_path) }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @elseif($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center text-gray-400">Image non disponible</div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <div class="flex items-center justify-between gap-2">
                        @if($product->brand)<span class="text-xs font-semibold uppercase tracking-wide text-blue-600">{{ $product->brand }}</span>@endif
                        @if($product->category)<span class="text-xs text-gray-500 dark:text-gray-400">{{ $product->category->name }}</span>@endif
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="mt-1 block">
                        <h2 class="line-clamp-2 text-lg font-semibold text-gray-900 hover:text-blue-600 dark:text-white">{{ $product->name }}</h2>
                    </a>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">@money($product->price)</span>
                        <span class="rounded-full px-2 py-1 text-xs {{ $product->stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $product->stock > 0 ? 'En stock' : 'Rupture' }}</span>
                    </div>
                    <a href="{{ route('products.show', $product) }}" class="mt-4 block w-full rounded-lg bg-blue-600 py-2 text-center font-medium text-white transition hover:bg-blue-700">Voir le détail</a>
                    @if($product->stock > 0)
                        <form action="{{ route('cart.add', $product) }}" method="POST" data-cart-add class="mt-2">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full rounded-lg bg-slate-900 py-2 font-medium text-white transition hover:bg-slate-700">{{ __('Ajouter au panier') }}</button>
                        </form>
                    @endif
                    @auth
                        <form action="{{ $isWishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" method="POST" class="mt-2">
                            @csrf
                            @if($isWishlisted) @method('DELETE') @endif
                            <button type="submit" class="w-full rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:text-gray-200 dark:hover:bg-slate-800">
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
