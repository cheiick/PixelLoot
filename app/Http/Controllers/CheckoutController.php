<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\ProcessCheckoutAction;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderStatusNotifier;
use App\Services\StripeCheckoutGateway;
use App\Support\ImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

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

        $nameParts = preg_split('/\s+/', trim(auth()->user()->name), 2) ?: [];

        return view('checkout.show', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'defaultFirstName' => $nameParts[0] ?? '',
            'defaultLastName' => $nameParts[1] ?? '',
        ]);
    }

    public function store(
        StoreCheckoutRequest $request,
        ProcessCheckoutAction $processCheckout,
        StripeCheckoutGateway $paymentGateway
    ): RedirectResponse
    {
        if (empty(session('cart', []))) {
            return redirect()->route('cart.index')->with('error', __('Votre panier est vide.'));
        }

        return redirect()->away($processCheckout($request->validated(), $paymentGateway));
    }

    public function confirmation(Order $order, OrderStatusNotifier $notifier)
    {
        abort_unless($order->user_id === auth()->id(), 404);

        Session::forget(['cart', 'coupon_code']);

        $sessionId = request()->query('session_id');
        if ($sessionId && $order->status === 'pending' && config('services.stripe.secret')) {
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $session = $stripe->checkout->sessions->retrieve($sessionId);

                if (($session->payment_status ?? null) === 'paid'
                    && (string) ($session->metadata->order_id ?? '') === (string) $order->id) {
                    $order->update(['status' => 'paid']);
                    $notifier->sendIfNeeded($order->fresh());
                }
            } catch (\Throwable $exception) {
                // Si la récupération Stripe échoue (ex: mauvaise clé en test local), on ignore et on laisse le webhook
            }
        }

        return view('checkout.confirmation', ['order' => $order]);
    }
}