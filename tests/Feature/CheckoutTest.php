<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\StripeCheckoutGateway;

it('creates an order with the correct total and updates product stock', function () {
    $category = Category::create([
        'name' => 'Électronique',
        'slug' => 'electronique',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Casque audio',
        'slug' => 'casque-audio',
        'price' => 49.90,
        'stock' => 5,
        'image_url' => 'https://example.com/casque.jpg',
    ]);

    $user = User::factory()->create();

    $gateway = Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('createSession')
        ->once()
        ->andReturn('https://checkout.example/session');
    $this->app->instance(StripeCheckoutGateway::class, $gateway);

    $checkout = $this->actingAs($user)
        ->withSession([
            'cart' => [
                $product->id => [
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => null,
                    'quantity' => 2,
                ],
            ],
        ]);

    $checkout->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('https://example.com/casque.jpg')
        ->assertSee('alt="Casque audio"', false);

    $response = $checkout->post(route('checkout.store'), ['address' => '10 rue de Paris']);

    $order = Order::firstOrFail();
    $response->assertRedirect('https://checkout.example/session');
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total' => '99.80',
        'currency_code' => 'EUR',
        'currency_symbol' => '€',
        'address' => '10 rue de Paris',
    ]);

    $this->assertDatabaseHas('order_items', [
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => '49.90',
    ]);

    expect($product->fresh()->stock)->toBe(3);
    $response->assertSessionMissing('cart');

    $this->get(route('checkout.confirmation', $order))
        ->assertOk()
        ->assertSee('Casque audio');
    expect($order->fresh()->status)->toBe('pending');
});

it('applies the selected coupon to the order and the Stripe checkout session', function () {
    $category = Category::create([
        'name' => 'Électronique',
        'slug' => 'electronique',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Casque audio',
        'slug' => 'casque-audio',
        'price' => 100,
        'stock' => 5,
    ]);

    $coupon = Coupon::create([
        'code' => 'WELCOME10',
        'type' => 'percentage',
        'value' => 10,
        'min_purchase' => 50,
        'usage_limit' => 5,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $gateway = Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('createSession')
        ->once()
        ->withArgs(function (array $items, string $successUrl, string $cancelUrl, ?Coupon $appliedCoupon, float $discount, string $currency, ?int $orderId) use ($coupon) {
            return $items[0]['quantity'] === 2
                && $appliedCoupon?->is($coupon)
                && $discount === 20.0
                && $currency === 'EUR'
                && $orderId !== null;
        })
        ->andReturn('https://checkout.example/session');
    $this->app->instance(StripeCheckoutGateway::class, $gateway);

    $this->actingAs($user)
        ->withSession([
            'cart' => [
                $product->id => [
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 2,
                ],
            ],
            'coupon_code' => 'WELCOME10',
        ])
        ->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('WELCOME10')
        ->assertSee('20')
        ->assertSee('180');

    $this->post(route('checkout.store'), ['address' => '10 rue de Paris'])
        ->assertRedirect('https://checkout.example/session');

    $order = Order::firstOrFail();
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'total' => '180.00',
        'coupon_code' => 'WELCOME10',
        'discount_amount' => '20.00',
        'currency_code' => 'EUR',
        'currency_symbol' => '€',
    ]);
    expect($coupon->fresh()->used_count)->toBe(1);

    $this->get(route('checkout.confirmation', $order))
        ->assertOk()
        ->assertSee('WELCOME10')
        ->assertSee('20')
        ->assertSee('180');
});

it('charges and snapshots an administrator-selected XOF currency', function () {
    SiteSetting::updateOrCreate(['key' => 'currency_code'], ['value' => 'XOF']);
    SiteSetting::updateOrCreate(['key' => 'currency_symbol'], ['value' => 'FCFA']);

    $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Casque audio',
        'slug' => 'casque-audio',
        'price' => 1250,
        'stock' => 5,
    ]);
    $user = User::factory()->create();

    $gateway = Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('createSession')
        ->once()
        ->withArgs(function (array $items, string $successUrl, string $cancelUrl, ?Coupon $coupon, float $discount, string $currency, ?int $orderId) {
            return $currency === 'XOF'
                && $items[0]['price_data']['currency'] === 'xof'
                && $items[0]['price_data']['unit_amount'] === 1250
                && $orderId !== null;
        })
        ->andReturn('https://checkout.example/session');
    $this->app->instance(StripeCheckoutGateway::class, $gateway);

    $this->actingAs($user)
        ->withSession(['cart' => [
            $product->id => ['name' => $product->name, 'price' => 1250, 'quantity' => 1],
        ]])
        ->post(route('checkout.store'), ['address' => 'Dakar'])
        ->assertRedirect('https://checkout.example/session');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total' => '1250.00',
        'currency_code' => 'XOF',
        'currency_symbol' => 'FCFA',
    ]);
});
