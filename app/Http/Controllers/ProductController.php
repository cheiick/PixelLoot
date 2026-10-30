<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category; // <-- Ajouté pour récupérer les catégories
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Page d'accueil (Vitrine)
     */
    public function index()
    {
        $categories = Cache::remember('storefront.home.categories', now()->addMinutes(30), function () {
            return Category::query()->select(['id', 'name'])->orderBy('name')->get();
        });

        // Récupérer les 4 derniers produits actifs pour la section "Coups de cœur"
        $featuredQuery = Product::with('category')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->take(4);
        if (auth()->check()) {
            $featuredQuery->withExists([
                'wishlistUsers as is_wishlisted' => fn ($query) => $query->whereKey(auth()->id()),
            ]);
        }
        $featuredProducts = $featuredQuery->get();

        return view('products.index', compact('categories', 'featuredProducts'));
    }

    /**
     * Page de détail d'un produit
     */
    public function show(Product $product)
    {
        abort_unless($product->is_active || auth()->user()?->is_admin, 404);

        return view('products.show', [
            'product' => $product,
            'category' => $product->category,
            'reviews' => $product->approvedReviews()->with('user')->latest()->paginate(10),
            'averageRating' => $product->approvedReviews()->avg('rating'),
            'reviewCount' => $product->approvedReviews()->count(),
            'isWishlisted' => auth()->check()
                ? auth()->user()->wishlistProducts()->whereKey($product->id)->exists()
                : false,
            'canReview' => auth()->check()
                ? \App\Models\Order::where('user_id', auth()->id())
                    ->whereIn('status', ['paid', 'shipped', 'delivered'])
                    ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                    ->exists()
                : false,
        ]);
    }
}