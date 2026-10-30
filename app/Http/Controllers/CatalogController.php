<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'brand' => ['nullable', 'string', 'max:150'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:99999999', 'gte:min_price'],
            'in_stock' => ['nullable', 'in:1'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,name_asc,name_desc'],
        ]);
        $query = Product::where('is_active', true)->with('category');
        if ($request->user()) {
            $query->withExists([
                'wishlistUsers as is_wishlisted' => fn ($wishlistQuery) => $wishlistQuery->whereKey($request->user()->id),
            ]);
        }

        // Recherche par nom
        if (filled($filters['search'] ?? null)) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // Filtre par catégorie
        if (filled($filters['category'] ?? null)) {
            $query->where('category_id', $filters['category']);
        }

        if (filled($filters['brand'] ?? null)) {
            $query->where('brand', $filters['brand']);
        }

        // Filtre prix min
        if (filled($filters['min_price'] ?? null)) {
            $query->where('price', '>=', $filters['min_price']);
        }

        // Filtre prix max
        if (filled($filters['max_price'] ?? null)) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Stock disponible
        if (($filters['in_stock'] ?? null) === '1') {
            $query->where('stock', '>', 0);
        }

        // Tri
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();
        $facets = Cache::remember('storefront.catalog.facets', now()->addMinutes(30), function () {
            return [
                'categories' => Category::orderBy('name')->get(['id', 'name']),
                'brands' => Product::where('is_active', true)
                    ->whereNotNull('brand')
                    ->where('brand', '<>', '')
                    ->distinct()
                    ->orderBy('brand')
                    ->pluck('brand'),
            ];
        });
        $categories = $facets['categories'];
        $brands = $facets['brands'];

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('catalog.partials.products', compact('products'))->render(),
                'total' => $products->total(),
                'first' => $products->firstItem(),
                'last' => $products->lastItem(),
            ]);
        }

        return view('catalog.index', compact('products', 'categories', 'brands'));
    }
}
