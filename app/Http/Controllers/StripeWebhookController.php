<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Services\OrderStatusNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, OrderStatusNotifier $notifier): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            report(new \RuntimeException('La clé de signature du webhook Stripe n’est pas configurée.'));

            return response('Webhook is not configured.', 500);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            return response('Invalid Stripe webhook signature or payload.', 400);
        }

        if ($event->type === 'checkout.session.expired') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;

            if (is_numeric($orderId)) {
                $order = Order::with('items.product')->find($orderId);

                if ($order && $order->status === 'pending') {
                    DB::transaction(function () use ($order) {
                        foreach ($order->items as $item) {
                            if ($item->product) {
                                $item->product->increment('stock', $item->quantity);
                            }
                        }

                        if ($order->coupon_code) {
                            Coupon::where('code', $order->coupon_code)->decrement('used_count');
                        }

                        $order->update(['status' => 'cancelled']);
                    });
                }
            }

            return response('Session expired handled.', 200);
        }

        if (! in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return response('Event ignored.', 200);
        }

        $session = $event->data->object;

        if (($session->payment_status ?? null) !== 'paid') {
            return response('Payment is not complete.', 200);
        }

        $orderId = $session->metadata->order_id ?? null;

        if (! is_numeric($orderId)) {
            report(new \RuntimeException('Le webhook Stripe payé ne contient pas d’identifiant de commande.'));

            return response('Order metadata is missing.', 400);
        }

        $order = Order::find($orderId);

        if (! $order) {
            report(new \RuntimeException('Le webhook Stripe référence une commande introuvable.'));

            return response('Order not found.', 404);
        }

        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid']);
        }

        $notifier->sendIfNeeded($order->fresh());

        return response('Webhook handled.', 200);
    }
}
