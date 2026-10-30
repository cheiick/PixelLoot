<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('shows product images and detail links on the home page', function () {
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
        'is_active' => true,
    ]);

    $this->withSession(['locale' => 'fr'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('https://example.com/casque.jpg')
        ->assertSee(route('products.show', $product))
        ->assertSee('Voir le détail')
        ->assertSee('Ajouter au panier')
        ->assertSee('data-cart-add', false)
        ->assertSee('Créer un compte')
        ->assertSee(route('register'));
});

it('does not show the registration call to action to signed-in customers', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['locale' => 'fr'])
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Créer un compte');
});

it('shows the product detail and add-to-cart button', function () {
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
        'is_active' => true,
    ]);

    $this->actingAs(User::factory()->create())
        ->withSession(['locale' => 'fr'])
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Casque audio')
        ->assertSee('https://example.com/casque.jpg')
        ->assertSee('<meta name="description"', false)
        ->assertSee('application/ld+json')
        ->assertSee('Ajouter au panier')
        ->assertSee('data-cart-add', false);
});
