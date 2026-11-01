<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Support\UploadedImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function createStorefrontTestProduct(array $attributes = []): Product
{
    $category = Category::firstOrCreate([
        'name' => 'Électronique',
        'slug' => 'electronique',
    ]);

    return Product::create(array_merge([
        'category_id' => $category->id,
        'name' => 'Casque audio',
        'slug' => 'casque-audio',
        'brand' => 'Helix',
        'price' => 49.90,
        'stock' => 5,
        'is_active' => true,
    ], $attributes));
}

it('filters products by brand and returns AJAX results with the selected filters', function () {
    $product = createStorefrontTestProduct();
    createStorefrontTestProduct([
        'name' => 'Clavier mécanique',
        'slug' => 'clavier-mecanique',
        'brand' => 'Autre',
    ]);

    $response = $this->getJson(route('catalog.index', ['search' => 'Casque', 'brand' => 'Helix']))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('first', 1);
    $this->assertStringContainsString('Casque audio', $response->json('html'));
    $this->assertStringNotContainsString('Clavier mécanique', $response->json('html'));

    $this->actingAs(User::factory()->create())
        ->withSession(['locale' => 'fr'])
        ->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Toutes les marques')
        ->assertSee('Helix')
        ->assertSee('Ajouter aux favoris');
});

it('allows signed-in customers to manage a wishlist', function () {
    $user = User::factory()->create();
    $product = createStorefrontTestProduct();

    $this->actingAs($user)
        ->withSession(['locale' => 'fr'])
        ->post(route('wishlist.store', $product))
        ->assertRedirect()
        ->assertSessionHas('wishlist_message', 'Produit ajouté à vos favoris.');
    $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);

    $this->get(route('catalog.index'))
        ->assertOk()
        ->assertSee('Casque audio')
        ->assertSee('Déjà dans vos favoris')
        ->assertSee('Produit ajouté à vos favoris.');
    $this->get(route('wishlist.index'))
        ->assertOk()
        ->assertSee('Casque audio');

    $this->delete(route('wishlist.destroy', $product))->assertRedirect();
    $this->assertDatabaseMissing('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
});

it('shows only three home categories at a time in a horizontal carousel', function () {
    Cache::forget('storefront.home.categories');
    foreach (['Audio', 'Consoles', 'Jeux', 'Accessoires', 'Informatique'] as $name) {
        Category::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name)]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-category-carousel', false)
        ->assertSee('Afficher les catégories suivantes')
        ->assertSee('Audio')
        ->assertSee('Informatique');
});

it('sends baseline security headers on storefront responses', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

it('rejects malformed catalog filters and cart quantities', function () {
    $product = createStorefrontTestProduct();

    $this->getJson(route('catalog.index', ['min_price' => 100, 'max_price' => 50]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('max_price');

    $this->postJson(route('cart.add', $product), ['quantity' => 'not-a-number'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('quantity');
});

it('compresses uploaded images to resized WebP assets', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->image('photo.jpg', 2400, 1600);

    $path = app(UploadedImageOptimizer::class)->storeWebp($file, 'products');
    $storedImage = getimagesize(Storage::disk('public')->path($path));

    $this->assertStringEndsWith('.webp', $path);
    expect($storedImage['mime'])->toBe('image/webp')
        ->and($storedImage[0])->toBeLessThanOrEqual(1920);
});

it('stores optimized images on the configured Cloudinary disk', function () {
    Storage::fake('cloudinary');
    config(['filesystems.image_disk' => 'cloudinary']);
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

    $url = app(UploadedImageOptimizer::class)->storeWebp($file, 'products');
    $storedImages = Storage::disk('cloudinary')->allFiles('products');

    expect($storedImages)->toHaveCount(1)
        ->and($url)->toBe(Storage::disk('cloudinary')->url($storedImages[0]));
});

it('rate limits repeated wishlist mutations', function () {
    $user = User::factory()->create();
    $product = createStorefrontTestProduct();
    $this->actingAs($user);

    for ($attempt = 0; $attempt < 30; $attempt++) {
        $this->post(route('wishlist.store', $product))->assertRedirect();
    }

    $this->post(route('wishlist.store', $product))->assertTooManyRequests();
});

it('accepts product reviews only after purchase and requires admin approval', function () {
    $user = User::factory()->create();
    $product = createStorefrontTestProduct();

    $this->actingAs($user)
        ->post(route('products.reviews.store', $product), ['rating' => 5, 'comment' => 'Très bon produit, conforme à la description.'])
        ->assertSessionHasErrors('review');

    $order = Order::create([
        'user_id' => $user->id,
        'total' => 49.90,
        'status' => 'paid',
        'address' => '10 rue de Paris',
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 49.90,
    ]);

    $this->post(route('products.reviews.store', $product), ['rating' => 5, 'comment' => 'Très bon produit, conforme à la description.'])
        ->assertSessionHas('review_success');
    $review = ProductReview::firstOrFail();
    expect($review->status)->toBe('pending');

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertDontSee('Très bon produit, conforme à la description.');

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->patch(route('admin.reviews.update', $review), ['status' => 'approved'])
        ->assertRedirect();

    $this->get(route('products.show', $product))
        ->assertSee('Très bon produit, conforme à la description.');
});

it('records newsletter subscribers and supports tokenized unsubscribe links', function () {
    Mail::fake();

    $response = $this->from(route('home'))->post(route('newsletter.store'), ['email' => ' Client@Example.com ']);
    $response->assertRedirect(route('home'))
        ->assertSessionHas('newsletter_success');
    $subscriber = \App\Models\NewsletterSubscriber::firstOrFail();
    expect($subscriber->email)->toBe('client@example.com');

    $this->get(route('newsletter.unsubscribe', $subscriber->unsubscribe_token))
        ->assertRedirect(route('home'))
        ->assertSessionHas('newsletter_success');
    expect($subscriber->fresh()->unsubscribed_at)->not->toBeNull();
});

it('exposes review moderation and newsletter management to administrators', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.reviews.index'))
        ->assertOk()
        ->assertSee('Modération des avis');

    $this->get(route('admin.newsletter.index'))
        ->assertOk()
        ->assertSee('Abonnés à la newsletter');
});
