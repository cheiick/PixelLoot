<?php

use App\Mail\OrderStatusUpdated;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

function configureOrderEmailSmtp(): void
{
    foreach ([
        'mail_enabled' => '1',
        'mail_smtp_host' => 'smtp.example.com',
        'mail_smtp_port' => '587',
        'mail_smtp_encryption' => 'tls',
        'mail_smtp_username' => 'smtp-user',
        'mail_smtp_password_encrypted' => Crypt::encryptString('smtp-secret'),
        'mail_from_address' => 'orders@example.com',
        'mail_from_name' => 'Helix Shop',
    ] as $key => $value) {
        SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

it('marks a paid Stripe order and sends one detailed email despite webhook retries', function () {
    Mail::fake();
    configureOrderEmailSmtp();
    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

    $user = User::factory()->create(['email' => 'client@example.com']);
    $category = Category::create(['name' => 'Audio', 'slug' => 'audio']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Casque Helix',
        'slug' => 'casque-helix',
        'price' => 59.90,
        'stock' => 3,
    ]);
    $order = Order::create([
        'user_id' => $user->id,
        'total' => 59.90,
        'status' => 'pending',
        'address' => 'Dakar, Sénégal',
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 59.90,
    ]);

    $payload = json_encode([
        'id' => 'evt_order_paid',
        'object' => 'event',
        'api_version' => '2024-06-20',
        'created' => now()->timestamp,
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_order',
                'object' => 'checkout.session',
                'payment_status' => 'paid',
                'metadata' => ['order_id' => (string) $order->id],
            ],
        ],
    ], JSON_THROW_ON_ERROR);
    $timestamp = now()->timestamp;
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_secret');
    $headers = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ];

    $this->call('POST', route('stripe.webhook'), [], [], [], $headers, $payload)->assertOk();

    expect($order->fresh()->status)->toBe('paid')
        ->and($order->fresh()->customer_status_emailed_at)->not->toBeNull();
    Mail::assertSent(OrderStatusUpdated::class, fn (OrderStatusUpdated $mail) => $mail->hasTo('client@example.com')
        && $mail->order->is($order)
        && $mail->order->status === 'paid');
    $mailable = Mail::sent(OrderStatusUpdated::class)->first();
    expect($mailable->render())->toContain('Casque Helix')
        ->toContain('Dakar, Sénégal')
        ->toContain('Payée');

    $this->call('POST', route('stripe.webhook'), [], [], [], $headers, $payload)->assertOk();
    Mail::assertSentCount(1);
});

it('rejects Stripe webhook requests without a valid signature', function () {
    config(['services.stripe.webhook_secret' => 'whsec_test_secret']);

    $this->call('POST', route('stripe.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => 'invalid',
    ], '{"type":"checkout.session.completed"}')->assertStatus(400);
});

it('emails the customer when an administrator updates the order status', function () {
    Mail::fake();
    configureOrderEmailSmtp();

    $customer = User::factory()->create(['email' => 'client@example.com']);
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::create([
        'user_id' => $customer->id,
        'total' => 80,
        'status' => 'paid',
        'address' => 'Dakar',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.orders.updateStatus', $order), ['status' => 'shipped'])
        ->assertRedirect(route('admin.orders.index'));

    expect($order->fresh()->status)->toBe('shipped')
        ->and($order->fresh()->customer_status_emailed_at)->not->toBeNull();
    Mail::assertSent(OrderStatusUpdated::class, fn (OrderStatusUpdated $mail) => $mail->hasTo('client@example.com')
        && $mail->order->status === 'shipped');
});

it('stores SMTP credentials encrypted and supports sending a test email from admin settings', function () {
    Mail::fake();
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);

    $this->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('name="mail_smtp_password"', false)
        ->assertSee(route('admin.settings.test-mail'));

    $this->put(route('admin.settings.update'), [
        'site_name' => 'Helix Shop',
        'currency' => 'EUR',
        'currency_symbol' => '€',
        'hero_title' => 'Gaming',
        'hero_description' => 'La boutique.',
        'footer_email' => 'contact@example.com',
        'footer_phone' => '+221 77 000 00 00',
        'mail_enabled' => '1',
        'mail_smtp_host' => 'smtp.example.com',
        'mail_smtp_port' => '587',
        'mail_smtp_encryption' => 'tls',
        'mail_smtp_username' => 'smtp-user',
        'mail_smtp_password' => 'secret-for-test',
        'mail_from_address' => 'orders@example.com',
        'mail_from_name' => 'Helix Shop',
    ])->assertRedirect(route('admin.settings.index'));

    $encryptedPassword = SiteSetting::getValue('mail_smtp_password_encrypted');
    expect($encryptedPassword)->not->toBe('secret-for-test')
        ->and(Crypt::decryptString($encryptedPassword))->toBe('secret-for-test');

    $this->post(route('admin.settings.test-mail'), ['mail_test_email' => 'admin@example.com'])
        ->assertRedirect(route('admin.settings.index'))
        ->assertSessionHas('mail_test_success');
    Mail::assertSentCount(1);
});
