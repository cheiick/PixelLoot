<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\ProcessCheckoutAction;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\FedaPayCheckoutGateway;
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
            'fedaPayAvailable' => (bool) config('services.fedapay.secret')
                && app(\App\Support\CurrencyFormatter::class)->code() === 'XOF',
        ]);
    }

    public function store(
        StoreCheckoutRequest $request,
        ProcessCheckoutAction $processCheckout,
        StripeCheckoutGateway $stripeGateway,
        FedaPayCheckoutGateway $fedaPayGateway
    ): RedirectResponse
    {
        if (empty(session('cart', []))) {
            return redirect()->route('cart.index')->with('error', __('Votre panier est vide.'));
        }

        return redirect()->away($processCheckout($request->validated(), $stripeGateway, $fedaPayGateway));
    }

    public function fedapayReturn(
        FedaPayCheckoutGateway $paymentGateway,
        OrderStatusNotifier $notifier
    ): RedirectResponse
    {
        $transactionId = request()->query('id', request()->query('transaction_id'));

        if (! is_string($transactionId) || ! ctype_digit($transactionId)) {
            return redirect()->route('cart.index')->with('error', __('La référence de paiement FedaPay est invalide.'));
        }

        $transaction = $paymentGateway->getTransaction($transactionId);
        $orderId = $transaction['custom_metadata']['order_id'] ?? $transaction['metadata']['order_id'] ?? null;
        $order = is_numeric($orderId) ? Order::find($orderId) : null;

        abort_unless($order && $order->user_id === auth()->id(), 404);

        if ($paymentGateway->confirmsOrder($transaction, $order) && $order->status !== 'paid') {
            $order->update(['status' => 'paid']);
            $notifier->sendIfNeeded($order->fresh());
        }

        return redirect()->route('checkout.confirmation', $order);
    }

    public function fedapayWebhook(
        \Illuminate\Http\Request $request,
        FedaPayCheckoutGateway $paymentGateway,
        OrderStatusNotifier $notifier
    ): \Illuminate\Http\Response
    {
        $secret = config('services.fedapay.webhook_secret');

        if (! $secret) {
            report(new \RuntimeException('La clé de signature du webhook FedaPay n’est pas configurée.'));

            return response('Webhook is not configured.', 500);
        }

        $payload = $request->getContent();

        if (! $paymentGateway->hasValidWebhookSignature(
            $payload,
            $request->header('X-FEDAPAY-SIGNATURE', ''),
            $secret
        )) {
            return response('Invalid FedaPay webhook signature.', 400);
        }

        $event = json_decode($payload, true);

        if (! is_array($event)) {
            return response('Invalid FedaPay webhook payload.', 400);
        }

        if (($event['name'] ?? $event['type'] ?? null) !== 'transaction.approved') {
            return response('Event ignored.', 200);
        }

        $transactionId = $event['data']['object']['id']
            ?? $event['entity']['id']
            ?? $event['transaction']['id']
            ?? $event['object_id']
            ?? null;

        if (! is_string($transactionId) && ! is_int($transactionId)) {
            return response('Transaction identifier is missing.', 400);
        }

        $transaction = $paymentGateway->getTransaction((string) $transactionId);
        $orderId = $transaction['custom_metadata']['order_id'] ?? $transaction['metadata']['order_id'] ?? null;
        $order = is_numeric($orderId) ? Order::find($orderId) : null;

        if (! $order || ! $paymentGateway->confirmsOrder($transaction, $order)) {
            return response('Transaction does not match an order.', 400);
        }

        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid']);
            $notifier->sendIfNeeded($order->fresh());
        }

        return response('Webhook handled.', 200);
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