<?php

namespace App\Actions\Checkout;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\StripeCheckoutGateway;
use App\Support\CurrencyFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\InvalidRequestException;

class ProcessCheckoutAction
{
    /**
     * Exécute le processus de checkout.
     *
     * @param  array<string, mixed>  $data
     * @return string Stripe Checkout URL
     *
     * @throws \Throwable
     */
    public function __invoke(array $data, StripeCheckoutGateway $paymentGateway): string
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            throw ValidationException::withMessages([
                'cart' => __('Votre panier est vide.'),
            ]);
        }

        try {
            $result = DB::transaction(function () use ($cart, $data, $paymentGateway) {
                $currency = app(CurrencyFormatter::class);
                $currencyCode = $currency->code();
                $products = Product::whereIn('id', array_map('intval', array_keys($cart)))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');
                $lineItems = [];
                $subtotal = 0;

                foreach ($cart as $productId => $item) {
                    $product = $products->get((int) $productId);
                    $quantity = is_array($item) ? (int) ($item['quantity'] ?? 0) : 0;

                    if (! $product || $quantity < 1 || $quantity > $product->stock) {
                        throw ValidationException::withMessages([
                            'cart' => __('Un produit de votre panier n’est plus disponible dans la quantité demandée.'),
                        ]);
                    }

                    $subtotal += (float) $product->price * $quantity;
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => strtolower($currencyCode),
                            'unit_amount' => $currency->minorUnits($product->price),
                            'product_data' => ['name' => $product->name],
                        ],
                        'quantity' => $quantity,
                    ];
                }

                $couponCode = filled($data['coupon_code'] ?? null)
                    ? $data['coupon_code']
                    : session('coupon_code');
                $coupon = null;
                $discount = 0;

                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();

                    if (! $coupon || ! $coupon->isAvailableFor($subtotal)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => __('Ce code promo n’est plus valide pour ce panier.'),
                        ]);
                    }

                    $discount = $coupon->discountFor($subtotal);
                }

                $user = auth()->user();
                $shippingAddress = implode("\n", [
                    $data['first_name'].' '.$data['last_name'],
                    $data['phone'],
                    $data['address'],
                    $data['postal_code'].' '.$data['city'],
                    $data['country'],
                    $data['email'],
                ]);

                $user->forceFill([
                    'name' => $data['first_name'].' '.$data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                ])->save();

                $order = Order::create([
                    'user_id' => $user->id,
                    'total' => max(0, $subtotal - $discount),
                    'coupon_code' => $coupon?->code,
                    'discount_amount' => $discount,
                    'currency_code' => $currencyCode,
                    'currency_symbol' => $currency->symbol(),
                    'status' => 'pending',
                    'address' => $shippingAddress,
                ]);

                foreach ($cart as $productId => $item) {
                    $product = $products->get((int) $productId);
                    $quantity = (int) $item['quantity'];
                    $order->items()->save(new OrderItem([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->price,
                    ]));
                    $product->decrement('stock', $quantity);
                }

                $checkoutUrl = $paymentGateway->createSession(
                    $lineItems,
                    route('checkout.confirmation', $order).'?session_id={CHECKOUT_SESSION_ID}',
                    route('cart.index'),
                    $coupon,
                    $discount,
                    $currencyCode,
                    $order->id
                );

                return ['checkout_url' => $checkoutUrl, 'coupon' => $coupon];
            });
        } catch (InvalidRequestException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'total amount must convert to at least')) {
                throw ValidationException::withMessages([
                    'cart' => __('Stripe refuse ce montant car il est inférieur au minimum de paiement pour cette devise. Augmentez le total du panier ou choisissez une autre devise.'),
                ]);
            }

            throw $exception;
        }

        if ($result['coupon']) {
            $result['coupon']->increment('used_count');
        }

        Session::forget(['cart', 'coupon_code']);

        return $result['checkout_url'];
    }
}