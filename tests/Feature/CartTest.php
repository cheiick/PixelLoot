<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

it('adds a product to the cart and displays its details', function () {
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
    ]);

    $this->post(route('cart.add', $product), ['quantity' => 2])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas("cart.{$product->id}", [
            'name' => 'Casque audio',
            'price' => '49.90',
            'image' => null,
            'quantity' => 2,
        ]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Casque audio')
        ->assertSee('99.80');
});

it('displays the uploaded product image in the cart', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/casque.jpg', 'image content');

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
        'image_path' => 'products/casque.jpg',
    ]);

    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url('products/casque.jpg'))
        ->assertSee('alt="Casque audio"', false);
});

it('applies an active coupon to a cart that meets its minimum purchase', function () {
    Coupon::create([
        'code' => 'WELCOME10',
        'type' => 'percentage',
        'value' => 10,
        'min_purchase' => 50,
        'usage_limit' => 5,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $this->withSession([
        'cart' => [
            1 => ['name' => 'Produit', 'price' => 60, 'image' => null, 'quantity' => 1],
        ],
    ])->post(route('cart.apply_coupon'), ['coupon_code' => ' welcome10 '])
        ->assertRedirect()
        ->assertSessionHas('coupon_code', 'WELCOME10')
        ->assertSessionHas('coupon_success', 'Code promo appliqué avec succès !');

    $this->delete(route('cart.remove_coupon'))
        ->assertRedirect()
        ->assertSessionMissing('coupon_code')
        ->assertSessionHas('coupon_success', 'Code promo supprimé.');
});

it('decreases the quantity of a product already in the cart', function () {
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
    ]);

    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $this->post(route('cart.add', $product), ['quantity' => -1])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas("cart.{$product->id}.quantity", 1);
});

it('adds from product pages and updates cart quantities without a page reload', function () {
    $category = Category::create([
        'name' => 'Électronique',
        'slug' => 'electronique',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Casque audio',
        'slug' => 'casque-audio',
        'price' => 49.90,
        'stock' => 4,
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('data-cart-add', false);

    $this->withHeaders(['Accept' => 'application/json'])
        ->post(route('cart.add', $product), ['quantity' => 1])
        ->assertOk()
        ->assertJsonPath('cart_count', 1)
        ->assertSessionHas("cart.{$product->id}.quantity", 1);

    $this->putJson(route('cart.update_quantity', $product), ['quantity' => 3])
        ->assertOk()
        ->assertJsonPath('quantity', 3)
        ->assertJsonPath('cart_count', 3)
        ->assertSessionHas("cart.{$product->id}.quantity", 3);

    $this->putJson(route('cart.update_quantity', $product), ['quantity' => 5])
        ->assertUnprocessable()
        ->assertSessionHas("cart.{$product->id}.quantity", 3);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('data-quantity-change', false)
        ->assertSee('data-cart-total', false)
        ->assertSee('aria-label="Voir mon panier"', false);
});

it('removes a product from the cart', function () {
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
    ]);

    $this->withSession([
        'cart' => [
            $product->id => [
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image_url,
                'quantity' => 1,
            ],
        ],
    ])->post(route('cart.remove', $product))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('cart', []);
});
