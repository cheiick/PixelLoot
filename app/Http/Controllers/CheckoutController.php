<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\StripeCheckoutGateway;
use App\Support\CurrencyFormatter;
use App\Support\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show()
    {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_map('intval', array_keys($cart)))
            ->get()
            ->keyBy('id');

        foreach ($cart as $productId => $item) {
            $product = $products->get((int) $productId);

            if ($product?->image_path) {
                $cart[$productId]['image'] = ImageStorage::url($product->image_path);
            } elseif ($product?->image_url) {
                $cart[$productId]['image'] = $product->image_url;
            } else {
                $cart[$productId]['image'] = is_array($item) ? ($item['image'] ?? null) : null;
            }
        }

        $subtotal = collect($cart)->sum(
            fn (array $item) => $item['price'] * $item['quantity']
        );
        $coupon = null;
        $discount = 0;
        $couponCode = session('coupon_code');

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->first();

            if ($coupon && $coupon->isAvailableFor($subtotal)) {
                $discount = $coupon->discountFor($subtotal);
            } else {
                $coupon = null;
                Session::forget('coupon_code');
                session()->flash('coupon_error', __('Ce code promo n’est plus valide pour ce panier.'));
            }
        }

        return view('checkout.show', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'total' => $subtotal - $discount,
        ]);
    }

    public function store(Request $request, StripeCheckoutGateway $paymentGateway)
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $cart = Session::get('cart', []);

        if (!$cart) {
            return redirect()->route('cart.index')->with('error', __('Votre panier est vide.'));
        }

        $couponCode = Session::get('coupon_code');
        $result = DB::transaction(function () use ($cart, $couponCode, $validated, $paymentGateway) {
            $currency = app(CurrencyFormatter::class);
            $currencyCode = $currency->code();
            $productIds = array_map('intval', array_keys($cart));
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $cartTotal = 0;
            $lineItems = [];

            foreach ($cart as $productId => $item) {
                $product = $products->get((int) $productId);
                $quantity = is_array($item) ? (int) ($item['quantity'] ?? 0) : 0;

                if (!$product || $quantity < 1 || $quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => __('Un produit de votre panier n’est plus disponible dans la quantité demandée.'),
                    ]);
                }

                $cartTotal += (float) $product->price * $quantity;
                $lineItems[] = [
                    'price_data' => [
                        'currency' => strtolower($currencyCode),
                        'unit_amount' => $currency->minorUnits($product->price),
                        'product_data' => ['name' => $product->name],
                    ],
                    'quantity' => $quantity,
                ];
            }

            $coupon = null;
            $discount = 0;

            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();

                if (!$coupon || !$coupon->isAvailableFor($cartTotal)) {
                    throw ValidationException::withMessages([
                        'coupon_code' => __('Ce code promo n’est plus valide pour ce panier.'),
                    ]);
                }

                $discount = $coupon->discountFor($cartTotal);
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'total' => $cartTotal - $discount,
                'coupon_code' => $coupon?->code,
                'discount_amount' => $discount,
                'currency_code' => $currencyCode,
                'currency_symbol' => $currency->symbol(),
                'status' => 'pending',
                'address' => $validated['address'],
            ]);

            foreach ($cart as $productId => $item) {
                $product = $products->get((int) $productId);
                $quantity = (int) $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);

                $product->decrement('stock', $quantity);
            }

            $checkoutUrl = $paymentGateway->createSession(
                $lineItems,
                route('checkout.confirmation', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                route('cart.index'),
                $coupon,
                $discount,
                $currencyCode,
                $order->id
            );

            return [
                'order' => $order,
                'checkout_url' => $checkoutUrl,
                'coupon' => $coupon,
            ];
        });

        if ($result['coupon']) {
            $result['coupon']->increment('used_count');
        }

        Session::forget(['cart', 'coupon_code']);

        return redirect()->away($result['checkout_url']);
    }

    public function confirmation(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 404);

        Session::forget(['cart', 'coupon_code']);

        return view('checkout.confirmation', ['order' => $order]);
    }
}
