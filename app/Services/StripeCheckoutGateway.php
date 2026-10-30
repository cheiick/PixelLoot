<?php

namespace App\Services;

use App\Models\Coupon;
use RuntimeException;
use Stripe\StripeClient;
use App\Support\CurrencyFormatter;

class StripeCheckoutGateway
{
    /**
     * @param array<int, array<string, mixed>> $lineItems
     */
    public function createSession(
        array $lineItems,
        string $successUrl,
        string $cancelUrl,
        ?Coupon $coupon = null,
        float $discount = 0,
        string $currency = 'EUR',
        ?int $orderId = null
    ): string {
        $secret = config('services.stripe.secret');

        if (!$secret) {
            throw new RuntimeException('La clé secrète Stripe n’est pas configurée.');
        }

        $stripe = new StripeClient($secret);
        $discounts = [];

        if ($coupon && $discount > 0) {
            $stripeCouponData = [
                'name' => $coupon->code,
                'duration' => 'once',
            ];

            if ($coupon->type === 'percentage') {
                $stripeCouponData['percent_off'] = (float) $coupon->value;
            } else {
                $stripeCouponData['amount_off'] = app(CurrencyFormatter::class)->minorUnits($discount);
                $stripeCouponData['currency'] = strtolower($currency);
            }

            $stripeCoupon = $stripe->coupons->create($stripeCouponData);
            $discounts[] = ['coupon' => $stripeCoupon->id];
        }

        $sessionData = [
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $orderId ? (string) $orderId : null,
            'metadata' => $orderId ? ['order_id' => (string) $orderId] : [],
        ];

        if ($discounts) {
            $sessionData['discounts'] = $discounts;
        }

        $session = $stripe->checkout->sessions->create($sessionData);

        if (!$session->url) {
            throw new RuntimeException('Stripe n’a pas retourné de lien de paiement.');
        }

        return $session->url;
    }
}
