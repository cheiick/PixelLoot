<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function index()
    {
        $cart = Session::get('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        foreach ($cart as $productId => $item) {
            $product = $products->get($productId);
            $quantity = is_array($item) ? (int) ($item['quantity'] ?? 0) : 0;

            if (!$product || $quantity < 1) {
                unset($cart[$productId]);
                continue;
            }

            $cart[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'image' => $this->productImageUrl($product),
                'quantity' => $quantity,
            ];
        }

        Session::put('cart', $cart);
        $productStocks = $products->mapWithKeys(fn (Product $product) => [$product->id => $product->stock]);

        return view('cart.index', compact('cart', 'productStocks'));
    }

    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:-1', 'max:'.$product->stock],
        ]);
        $quantity = (int) ($validated['quantity'] ?? 1);
        $cart = Session::get('cart', []);
        $currentQuantity = is_array($cart[$product->id] ?? null)
            ? (int) ($cart[$product->id]['quantity'] ?? 0)
            : 0;

        if ($quantity === -1) {
            if ($currentQuantity <= 0) {
                return redirect()->route('cart.index')->with('error', __('Ce produit n’est pas dans votre panier.'));
            }

            if ($currentQuantity === 1) {
                unset($cart[$product->id]);
            } else {
                $cart[$product->id]['quantity'] = $currentQuantity - 1;
            }

            Session::put('cart', $cart);

            return redirect()->route('cart.index');
        }

        if ($quantity < 1 || $quantity > $product->stock) {
            return $this->cartError($request, __('Quantité invalide ou dépasse le stock.'));
        }

        if ($currentQuantity + $quantity > $product->stock) {
            return $this->cartError($request, __('La quantité demandée dépasse le stock disponible.'));
        }

        $cart[$product->id] = [
            'name' => $product->name,
            'price' => $product->price,
            'image' => $this->productImageUrl($product),
            'quantity' => $currentQuantity + $quantity,
        ];

        Session::put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Produit ajouté au panier.'),
                'cart_count' => $this->cartCount($cart),
                'quantity' => $cart[$product->id]['quantity'],
            ]);
        }

        return redirect()->route('cart.index')->with('success', __('Produit ajouté au panier.'));
    }

    public function updateQuantity(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $product->stock],
        ]);
        $cart = Session::get('cart', []);

        if (! isset($cart[$product->id]) || ! is_array($cart[$product->id])) {
            return response()->json(['message' => __('Ce produit n’est pas dans votre panier.')], 404);
        }

        $cart[$product->id]['quantity'] = (int) $validated['quantity'];
        Session::put('cart', $cart);

        return response()->json([
            'message' => __('Quantité mise à jour.'),
            'cart_count' => $this->cartCount($cart),
            'quantity' => $cart[$product->id]['quantity'],
        ]);
    }

    public function remove(Product $product)
    {
        $cart = Session::get('cart', []);
        unset($cart[$product->id]);
        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', __('Produit supprimé du panier.'));
    }

    public function clear()
    {
        Session::forget('cart');

        return redirect()->route('cart.index')->with('success', __('Panier vidé.'));
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:255'],
        ]);
        $code = strtoupper(trim($validated['coupon_code']));

        $cartTotal = collect(Session::get('cart', []))
            ->sum(fn (array $item) => $item['price'] * $item['quantity']);

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->isAvailableFor($cartTotal)) {
            return back()->with('coupon_error', __('Code promo invalide, expiré ou conditions non remplies.'));
        }

        Session::put('coupon_code', $code);

        return back()->with('coupon_success', __('Code promo appliqué avec succès !'));
    }

    public function removeCoupon()
    {
        Session::forget('coupon_code');

        return back()->with('coupon_success', __('Code promo supprimé.'));
    }

    private function productImageUrl(Product $product): ?string
    {
        if ($product->image_path) {
            return Storage::disk('public')->url($product->image_path);
        }

        return $product->image_url;
    }

    private function cartError(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return redirect()->route('cart.index')->with('error', $message);
    }

    private function cartCount(array $cart): int
    {
        return array_sum(array_map(
            static fn ($item): int => is_array($item) ? max(0, (int) ($item['quantity'] ?? 0)) : 0,
            $cart
        ));
    }
}
