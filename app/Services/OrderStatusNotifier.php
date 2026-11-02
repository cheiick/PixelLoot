<?php

namespace App\Services;

use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Support\ConfiguredSmtpMailer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderStatusNotifier
{
    public function sendIfNeeded(Order $order): void
    {
        $order->loadMissing('user');

        if ($order->customer_status_emailed_at || ! $order->user?->email) {
            return;
        }

        $mailer = app(ConfiguredSmtpMailer::class);

        if (! $mailer->configure()) {
            Log::notice('Order status email was not sent because SMTP is disabled.', [
                'order_id' => $order->id,
            ]);

            return;
        }

        Mail::mailer($mailer->mailerName())
            ->to($order->user->email)
            ->send(new OrderStatusUpdated($order));

        $order->forceFill(['customer_status_emailed_at' => now()])->save();
    }
}
