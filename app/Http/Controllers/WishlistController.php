<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('wishlist.index', [
            'products' => $request->user()->wishlistProducts()->with('category')->latest('wishlists.created_at')->paginate(12),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlistProducts()->syncWithoutDetaching([$product->id]);

        return back()->with('wishlist_message', 'Produit ajouté à vos favoris.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlistProducts()->detach($product->id);

        return back()->with('wishlist_message', 'Produit retiré de vos favoris.');
    }
}
