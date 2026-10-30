<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('shows customer information and only that customers order history', function () {
    $customer = User::factory()->create([
        'name' => 'Client Exemple',
        'email' => null,
        'phone' => '+33612345678',
        'address' => '10 rue de Paris',
    ]);
    $otherCustomer = User::factory()->create();

    $category = Category::create([
        'name' => 'Consoles',
        'slug' => 'consoles',
    ]);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Console de jeu',
        'slug' => 'console-de-jeu',
        'price' => 250,
        'stock' => 4,
    ]);
    $order = Order::create([
        'user_id' => $customer->id,
        'total' => 250,
        'status' => 'pending',
        'address' => '10 rue de Paris',
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 250,
    ]);
    Order::create([
        'user_id' => $otherCustomer->id,
        'total' => 999,
        'status' => 'paid',
        'address' => 'Adresse privée',
    ]);

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Mes informations')
        ->assertSee('value="Client Exemple"', false)
        ->assertSee('value="+33612345678"', false)
        ->assertSee('10 rue de Paris')
        ->assertSee('Console de jeu')
        ->assertSee('250,00 €')
        ->assertDontSee('999,00 €')
        ->assertSee('Ajoutez une adresse e-mail à votre compte');
});

it('lets a phone-only customer save an email and delivery address', function () {
    $customer = User::factory()->create([
        'email' => null,
        'phone' => '+33612345678',
    ]);

    $this->actingAs($customer)
        ->put(route('dashboard.update'), [
            'name' => 'Client Exemple',
            'email' => 'client@example.com',
            'phone' => '+33 6 12 34 56 78',
            'address' => '15 avenue de Lyon',
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $customer->id,
        'email' => 'client@example.com',
        'phone' => '+33612345678',
        'address' => '15 avenue de Lyon',
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Ajoutez une adresse e-mail à votre compte');
});

it('shows a dashboard link to signed-in customers', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Mon compte')
        ->assertSee(route('dashboard'))
        ->assertSee('id="mobile-navigation"', false)
        ->assertSee('aria-label="Ouvrir ou fermer le menu"', false);
});

it('shows administration links only to administrators', function () {
    $customer = User::factory()->create();
    $this->actingAs($customer)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('Administration');

    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Administration')
        ->assertSee(route('admin.dashboard'));
});
