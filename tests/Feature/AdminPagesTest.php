<?php

use App\Models\User;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('loads the admin dashboard and its settings navigation for an administrator', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('admin.settings.index'))
        ->assertSee(route('admin.coupons.index'))
        ->assertSee('Codes promo')
        ->assertSee(route('admin.messages.index'))
        ->assertSee(route('admin.admin-users.index'))
        ->assertSee('id="admin-sidebar"', false)
        ->assertSee('aria-label="Ouvrir le menu d’administration"', false)
        ->assertSee('overflow-y-auto', false)
        ->assertSee('Dashboard administrateur');
});

it('provides coupon management from the admin navigation', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.coupons.index'))
        ->assertOk()
        ->assertSee('Codes promo')
        ->assertSee(route('admin.coupons.create'));
});

it('loads the admin orders page for an administrator', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('Gestion des commandes')
        ->assertSee('Aucune commande pour le moment.');
});

it('shows product images immediately after their ids in the admin list', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $category = Category::create(['name' => 'Jeux', 'slug' => 'jeux']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Clavier gaming',
        'slug' => 'clavier-gaming',
        'price' => 80,
        'stock' => 4,
        'image_path' => 'products/clavier.webp',
    ]);
    $externalImage = 'https://example.com/souris.jpg';
    Product::create([
        'category_id' => $category->id,
        'name' => 'Souris gaming',
        'slug' => 'souris-gaming',
        'price' => 40,
        'stock' => 3,
        'image_url' => $externalImage,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($product->image_path))
        ->assertSee($externalImage);

    $content = $response->getContent();
    $idPosition = strpos($content, $product->id.'</td>');
    $imagePosition = strpos($content, Storage::disk('public')->url($product->image_path));
    $namePosition = strpos($content, 'Clavier gaming</td>');

    expect($idPosition)->not->toBeFalse()
        ->and($imagePosition)->not->toBeFalse()
        ->and($namePosition)->not->toBeFalse()
        ->and($idPosition)->toBeLessThan($imagePosition)
        ->and($imagePosition)->toBeLessThan($namePosition);
});

it('allows an administrator to create and update product categories', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee('Catégories de produits');

    $this->post(route('admin.categories.store'), [
        'name' => 'Jeux vidéo',
        'slug' => 'slug-qui-ne-doit-pas-etre-utilise',
        'description' => 'Consoles et jeux.',
    ])
        ->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('success', 'Catégorie créée avec succès.');

    $category = Category::where('slug', 'jeux-video')->firstOrFail();
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'description' => 'Consoles et jeux.',
    ]);

    $this->put(route('admin.categories.update', $category), [
        'name' => 'Jeux et consoles',
        'slug' => 'jeux-consoles',
        'description' => 'Jeux et consoles vidéo.',
    ])
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Jeux et consoles',
        'slug' => 'jeux-et-consoles',
    ]);
});

it('generates a unique category slug from its name', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    Category::create(['name' => 'Jeux vidéo', 'slug' => 'jeux-video']);

    $this->actingAs($admin)
        ->post(route('admin.categories.store'), ['name' => 'Jeux vidéo'])
        ->assertRedirect(route('admin.categories.index'));

    $this->assertDatabaseHas('categories', [
        'name' => 'Jeux vidéo',
        'slug' => 'jeux-video-2',
    ]);
});

it('shows purchased products in the admin order list', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $category = Category::create(['name' => 'Jeux', 'slug' => 'jeux']);
    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Spider-Man 2',
        'slug' => 'spider-man-2',
        'price' => 60,
        'stock' => 5,
    ]);
    $order = Order::create([
        'user_id' => $admin->id,
        'total' => 120,
        'status' => 'pending',
        'address' => '10 rue de Paris',
    ]);
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 60,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('Articles achetés')
        ->assertSee('Spider-Man 2')
        ->assertSee('2 ×')
        ->assertSee('120,00 €');
});

it('updates an order status and confirms the change', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $order = Order::create([
        'user_id' => $admin->id,
        'total' => 49.90,
        'status' => 'pending',
        'address' => '10 rue de Paris',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.orders.updateStatus', $order), ['status' => 'paid'])
        ->assertRedirect(route('admin.orders.index'))
        ->assertSessionHas('success', "Le statut de la commande #{$order->id} a été mis à jour.");

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'paid',
    ]);
    expect(Order::where('status', 'pending')->count())->toBe(0);

    $this->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('paid')
        ->assertSee("Le statut de la commande #{$order->id} a été mis à jour.");
});

it('defaults the validation action to paid for pending orders', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    Order::create([
        'user_id' => $admin->id,
        'total' => 49.90,
        'status' => 'pending',
        'address' => '10 rue de Paris',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('<option value="paid" selected>', false);
});

it('saves and reloads the footer settings', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('name="site_name"', false)
        ->assertSee('name="hero_title"', false)
        ->assertSee('name="hero_description"', false)
        ->assertSee('name="hero_images[]"', false)
        ->assertSee('10 Mo maximum par image')
        ->assertSee('name="site_logo"', false)
        ->assertSee('value="contact@monshop.com"', false)
        ->assertSee('value="01 23 45 67 89"', false)
        ->assertSee('name="social_facebook"', false)
        ->assertSee('name="social_instagram"', false)
        ->assertSee('name="social_whatsapp"', false)
        ->assertSee('name="social_tiktok"', false);

    $this->put(route('admin.settings.update'), [
        'site_name' => 'MonShop Gaming',
        'hero_title' => 'Le meilleur du Gaming',
        'hero_description' => 'Jeux, consoles et accessoires en promotion.',
        'site_logo' => UploadedFile::fake()->image('logo.png'),
        'hero_images' => [
            UploadedFile::fake()->image('hero-one.webp'),
            UploadedFile::fake()->image('hero-two.jpg'),
        ],
        'footer_email' => 'support@example.com',
        'footer_phone' => '06 12 34 56 78',
        'social_facebook' => 'https://facebook.com/monshop',
        'social_instagram' => '',
        'social_whatsapp' => 'https://wa.me/33612345678',
        'social_tiktok' => '',
    ])
        ->assertRedirect(route('admin.settings.index'))
        ->assertSessionHas('success', 'Paramètres mis à jour avec succès.');

    $logoPath = SiteSetting::getValue('site_logo');
    $heroImagePaths = json_decode(SiteSetting::getValue('hero_images'), true);
    Storage::disk('public')->assertExists($logoPath);
    expect($heroImagePaths)->toHaveCount(2);
    foreach ($heroImagePaths as $heroImagePath) {
        Storage::disk('public')->assertExists($heroImagePath);
    }

    expect(SiteSetting::getValue('site_name'))->toBe('MonShop Gaming')
        ->and(SiteSetting::getValue('hero_title'))->toBe('Le meilleur du Gaming')
        ->and(SiteSetting::getValue('hero_description'))->toBe('Jeux, consoles et accessoires en promotion.')
        ->and(json_decode(SiteSetting::getValue('hero_images'), true))->toBe($heroImagePaths)
        ->and(SiteSetting::getValue('footer_email'))->toBe('support@example.com')
        ->and(SiteSetting::getValue('footer_phone'))->toBe('06 12 34 56 78')
        ->and(SiteSetting::getValue('social_facebook'))->toBe('https://facebook.com/monshop')
        ->and(SiteSetting::getValue('social_instagram'))->toBe('')
        ->and(SiteSetting::getValue('social_whatsapp'))->toBe('https://wa.me/33612345678')
        ->and(SiteSetting::getValue('social_tiktok'))->toBe('');

    $this->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('value="MonShop Gaming"', false)
        ->assertSee('value="Le meilleur du Gaming"', false)
        ->assertSee('value="support@example.com"', false)
        ->assertSee('value="06 12 34 56 78"', false)
        ->assertSee('value="https://facebook.com/monshop"', false);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<title>MonShop Gaming - Boutique Gaming &amp; High-Tech</title>', false)
        ->assertSee('Le meilleur du Gaming')
        ->assertSee('Jeux, consoles et accessoires en promotion.')
        ->assertSee($heroImagePaths[0], false)
        ->assertSee($heroImagePaths[1], false)
        ->assertSee('5000', false)
        ->assertSee('Bannière d’accueil', false)
        ->assertSee('alt="MonShop Gaming"', false)
        ->assertSee($logoPath, false)
        ->assertSee('href="https://facebook.com/monshop"', false)
        ->assertSee('href="https://wa.me/33612345678"', false)
        ->assertSee('aria-label="Facebook"', false)
        ->assertSee('aria-label="WhatsApp"', false)
        ->assertDontSee('aria-label="Instagram"', false)
        ->assertDontSee('aria-label="TikTok"', false)
        ->assertSee('mailto:support@example.com', false)
        ->assertSee('tel:0612345678', false);
});

it('allows an administrator to remove the site logo', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $logoPath = 'site/logo.png';
    Storage::disk('public')->put($logoPath, 'logo');
    SiteSetting::updateOrCreate(['key' => 'site_logo'], ['value' => $logoPath]);

    $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), [
            'site_name' => 'MonShop',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Découvrez les nouveautés.',
            'remove_site_logo' => '1',
            'footer_email' => 'contact@monshop.com',
            'footer_phone' => '01 23 45 67 89',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_whatsapp' => '',
            'social_tiktok' => '',
        ])
        ->assertRedirect(route('admin.settings.index'));

    expect(SiteSetting::getValue('site_logo'))->toBe('');
    Storage::disk('public')->assertMissing($logoPath);
});

it('allows an administrator to replace and remove the home hero gallery', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $oldImagePath = 'site/old-hero.png';
    Storage::disk('public')->put($oldImagePath, 'old image');
    SiteSetting::updateOrCreate(['key' => 'hero_image'], ['value' => $oldImagePath]);

    $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), [
            'site_name' => 'MonShop',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Découvrez les nouveautés.',
            'hero_images' => [
                UploadedFile::fake()->image('new-hero-one.jpg'),
                UploadedFile::fake()->image('new-hero-two.jpg'),
                UploadedFile::fake()->image('new-hero-three.jpg'),
            ],
            'footer_email' => 'contact@monshop.com',
            'footer_phone' => '01 23 45 67 89',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_whatsapp' => '',
            'social_tiktok' => '',
        ])
        ->assertRedirect(route('admin.settings.index'));

    $newImagePaths = json_decode(SiteSetting::getValue('hero_images'), true);
    expect($newImagePaths)->toHaveCount(3);
    expect($newImagePaths)->not->toContain($oldImagePath);
    foreach ($newImagePaths as $newImagePath) {
        Storage::disk('public')->assertExists($newImagePath);
    }
    expect(SiteSetting::getValue('hero_image'))->toBe('');
    Storage::disk('public')->assertMissing($oldImagePath);
    $this->get(route('home'))->assertOk();
    foreach ($newImagePaths as $newImagePath) {
        $this->get(route('home'))->assertOk()->assertSee($newImagePath, false);
    }

    $this->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), [
            'site_name' => 'MonShop',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Découvrez les nouveautés.',
            'remove_hero_images' => '1',
            'footer_email' => 'contact@monshop.com',
            'footer_phone' => '01 23 45 67 89',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_whatsapp' => '',
            'social_tiktok' => '',
        ])
        ->assertRedirect(route('admin.settings.index'));

    expect(json_decode(SiteSetting::getValue('hero_images'), true))->toBe([]);
    foreach ($newImagePaths as $newImagePath) {
        Storage::disk('public')->assertMissing($newImagePath);
        $this->get(route('home'))->assertOk()->assertDontSee($newImagePath, false);
    }
});

it('rejects more than five home hero images', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), [
            'site_name' => 'MonShop',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Découvrez les nouveautés.',
            'hero_images' => [
                UploadedFile::fake()->image('hero-1.jpg'),
                UploadedFile::fake()->image('hero-2.jpg'),
                UploadedFile::fake()->image('hero-3.jpg'),
                UploadedFile::fake()->image('hero-4.jpg'),
                UploadedFile::fake()->image('hero-5.jpg'),
                UploadedFile::fake()->image('hero-6.jpg'),
            ],
            'footer_email' => 'contact@monshop.com',
            'footer_phone' => '01 23 45 67 89',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_whatsapp' => '',
            'social_tiktok' => '',
        ])
        ->assertSessionHasErrors('hero_images');

    expect(SiteSetting::getValue('hero_images'))->toBe('');
    expect(Storage::disk('public')->allFiles('site'))->toBe([]);
});

it('accepts home hero images up to ten megabytes', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), [
            'site_name' => 'MonShop',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Découvrez les nouveautés.',
            'hero_images' => [
                UploadedFile::fake()->image('hero.jpg')->size(10240),
            ],
            'footer_email' => 'contact@monshop.com',
            'footer_phone' => '01 23 45 67 89',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_whatsapp' => '',
            'social_tiktok' => '',
        ])
        ->assertRedirect(route('admin.settings.index'))
        ->assertSessionHasNoErrors();

    $heroImages = json_decode(SiteSetting::getValue('hero_images'), true);
    expect($heroImages)->toHaveCount(1);
    Storage::disk('public')->assertExists($heroImages[0]);
});

it('rejects home hero images larger than ten megabytes', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), [
            'site_name' => 'MonShop',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Découvrez les nouveautés.',
            'hero_images' => [
                UploadedFile::fake()->image('hero.jpg')->size(10241),
            ],
            'footer_email' => 'contact@monshop.com',
            'footer_phone' => '01 23 45 67 89',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_whatsapp' => '',
            'social_tiktok' => '',
        ])
        ->assertSessionHasErrors('hero_images.0');

    expect(SiteSetting::getValue('hero_images'))->toBe('');
    expect(Storage::disk('public')->allFiles('site'))->toBe([]);
});
