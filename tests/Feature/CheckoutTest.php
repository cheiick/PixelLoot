<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\StripeCheckoutGateway;
use Illuminate\Support\Facades\Http;
use Stripe\Exception\InvalidRequestException;

it('requires a phone number before starting checkout', function () {
    $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Casque audio',
        'slug' => 'casque-audio',
        'price' => 49.90,
        'stock' => 5,
    ]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['cart' => [
            $product->id => ['name' => $product->name, 'price' => $product->price, 'quantity' => 1],
        ]])
        ->from(route('checkout.show'))
        ->post(route('checkout.store'), [
            'email' => 'buyer@example.test',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'address' => '10 rue de Paris',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'France',
        ])
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors('phone');

    expect(Order::count())->toBe(0);
});

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

    $response = $checkout->post(route('checkout.store'), [
        'email' => 'buyer@example.test',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '+33 6 12 34 56 78',
        'address' => '10 rue de Paris',
        'city' => 'Paris',
        'postal_code' => '75001',
        'country' => 'France',
    ]);

    $order = Order::firstOrFail();
    $response->assertRedirect('https://checkout.example/session');
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total' => '99.80',
        'currency_code' => 'EUR',
        'currency_symbol' => '€',
    ]);
    expect($order->address)->toContain('10 rue de Paris', '+33612345678', 'buyer@example.test');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'buyer@example.test',
        'phone' => '+33612345678',
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

    $this->post(route('checkout.store'), [
        'email' => 'buyer@example.test',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '+33 6 12 34 56 78',
        'address' => '10 rue de Paris',
        'city' => 'Paris',
        'postal_code' => '75001',
        'country' => 'France',
    ])
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
        ->post(route('checkout.store'), [
            'email' => 'buyer@example.test',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '+221 77 000 00 00',
            'address' => '10 rue de Dakar',
            'city' => 'Dakar',
            'postal_code' => '10000',
            'country' => 'Sénégal',
        ])
        ->assertRedirect('https://checkout.example/session');

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total' => '1250.00',
        'currency_code' => 'XOF',
        'currency_symbol' => 'FCFA',
    ]);
});

it('explains Stripe minimum amount errors and keeps the cart without creating an order', function () {
    SiteSetting::updateOrCreate(['key' => 'currency_code'], ['value' => 'XOF']);
    SiteSetting::updateOrCreate(['key' => 'currency_symbol'], ['value' => 'FCFA']);

    $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Article de test',
        'slug' => 'article-de-test',
        'price' => 60,
        'stock' => 5,
    ]);
    $user = User::factory()->create();

    $gateway = Mockery::mock(StripeCheckoutGateway::class);
    $gateway->shouldReceive('createSession')
        ->once()
        ->andThrow(InvalidRequestException::factory(
            "The Checkout Session's total amount must convert to at least 50 cents. 60 Fr converts to approximately €0.09.",
            400
        ));
    $this->app->instance(StripeCheckoutGateway::class, $gateway);

    $this->actingAs($user)
        ->withSession(['cart' => [
            $product->id => ['name' => $product->name, 'price' => 60, 'quantity' => 1],
        ]])
        ->from(route('checkout.show'))
        ->post(route('checkout.store'), [
            'email' => 'buyer@example.test',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '+221 77 000 00 00',
            'address' => '10 rue de Dakar',
            'city' => 'Dakar',
            'postal_code' => '10000',
            'country' => 'Sénégal',
        ])
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors('cart')
        ->assertSessionHas('cart');

    expect(Order::count())->toBe(0)
        ->and($product->fresh()->stock)->toBe(5);
});

it('starts FedaPay checkout in XOF and confirms the order from the trusted transaction API', function () {
    config([
        'services.fedapay.secret' => 'sk_sandbox_test',
        'services.fedapay.base_url' => 'https://sandbox-api.fedapay.com/v1',
    ]);
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

    Http::fake([
        'sandbox-api.fedapay.com/v1/transactions' => Http::response([
            'id' => 987,
            'status' => 'pending',
        ], 201),
        'sandbox-api.fedapay.com/v1/transactions/987/token' => Http::response([
            'url' => 'https://checkout.fedapay.com/token/example',
        ]),
        'sandbox-api.fedapay.com/v1/transactions/987' => fn () => Http::response([
            'id' => 987,
            'status' => 'approved',
            'amount' => 1250,
            'currency' => ['iso' => 'XOF'],
            'custom_metadata' => ['order_id' => (string) Order::firstOrFail()->id],
        ]),
    ]);

    $this->actingAs($user)
        ->withSession(['cart' => [
            $product->id => ['name' => $product->name, 'price' => $product->price, 'quantity' => 1],
        ]])
        ->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('FedaPay');

    $this->post(route('checkout.store'), [
        'email' => 'buyer@example.test',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'phone' => '+221 77 000 00 00',
        'address' => '10 rue de Dakar',
        'city' => 'Dakar',
        'postal_code' => '10000',
        'country' => 'Sénégal',
        'payment_method' => 'fedapay',
    ])->assertRedirect('https://checkout.fedapay.com/token/example');

    $order = Order::firstOrFail();
    Http::assertSent(fn ($request) => $request->url() === 'https://sandbox-api.fedapay.com/v1/transactions'
        && $request['amount'] === 1250
        && $request['currency']['iso'] === 'XOF'
        && $request['custom_metadata']['order_id'] === (string) $order->id
        && $request->hasHeader('Authorization', 'Bearer sk_sandbox_test'));

    $this->get(route('fedapay.return', ['id' => 987]))
        ->assertRedirect(route('checkout.confirmation', $order));

    expect($order->fresh()->status)->toBe('paid')
        ->and($product->fresh()->stock)->toBe(4);
});

it('validates FedaPay webhook signatures and reconciles approved transactions with the API', function () {
    config([
        'services.fedapay.secret' => 'sk_sandbox_test',
        'services.fedapay.webhook_secret' => 'wh_sandbox_test',
        'services.fedapay.base_url' => 'https://sandbox-api.fedapay.com/v1',
    ]);

    $customer = User::factory()->create();
    $order = Order::create([
        'user_id' => $customer->id,
        'total' => 1250,
        'currency_code' => 'XOF',
        'status' => 'pending',
        'address' => 'Dakar',
    ]);
    $payload = json_encode([
        'name' => 'transaction.approved',
        'object_id' => 987,
    ], JSON_THROW_ON_ERROR);
    $timestamp = now()->timestamp;
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'wh_sandbox_test');

    Http::fake([
        'sandbox-api.fedapay.com/v1/transactions/987' => Http::response([
            'id' => 987,
            'status' => 'approved',
            'amount' => 1250,
            'currency' => ['iso' => 'XOF'],
            'custom_metadata' => ['order_id' => (string) $order->id],
        ]),
    ]);

    $this->call('POST', route('fedapay.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_FEDAPAY_SIGNATURE' => "t={$timestamp},s={$signature}",
    ], $payload)->assertOk();

    expect($order->fresh()->status)->toBe('paid');

    $this->call('POST', route('fedapay.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_FEDAPAY_SIGNATURE' => 't='.$timestamp.',s=invalid',
    ], $payload)->assertStatus(400);
});

it('rejects FedaPay checkout when the configured currency is not XOF', function () {
    config(['services.fedapay.secret' => 'sk_sandbox_test']);
    $category = Category::create(['name' => 'Électronique', 'slug' => 'electronique']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Casque audio',
        'slug' => 'casque-audio',
        'price' => 10,
        'stock' => 5,
    ]);

    $this->actingAs(User::factory()->create())
        ->withSession(['cart' => [
            $product->id => ['name' => $product->name, 'price' => $product->price, 'quantity' => 1],
        ]])
        ->from(route('checkout.show'))
        ->post(route('checkout.store'), [
            'email' => 'buyer@example.test',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '+33 6 12 34 56 78',
            'address' => '10 rue de Paris',
            'city' => 'Paris',
            'postal_code' => '75001',
            'country' => 'France',
            'payment_method' => 'fedapay',
        ])
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors('payment_method')
        ->assertSessionHas('cart');

    expect(Order::count())->toBe(0);
});
