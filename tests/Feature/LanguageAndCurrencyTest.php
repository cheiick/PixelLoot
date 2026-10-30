<?php

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\CurrencyFormatter;

it('keeps the selected storefront language between requests', function () {
    config(['app.locale' => 'fr']);

    $this->from(route('home'))
        ->post(route('language.update'), ['locale' => 'en'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('locale', 'en');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Shop by category');

    $this->post(route('language.update'), ['locale' => 'fr']);
    $this->get(route('home'))->assertOk();

    $this->post(route('language.update'), ['locale' => 'invalid'])
        ->assertSessionHasErrors('locale');

    session()->forget('locale');
    app()->setLocale('fr');
});

it('lets administrators change the storefront currency and formats zero-decimal currencies', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->from(route('admin.settings.index'))
        ->put(route('admin.settings.update'), [
            'site_name' => 'Helix Shop',
            'currency' => 'XOF',
            'currency_symbol' => 'FCFA',
            'hero_title' => 'Gaming & High-Tech',
            'hero_description' => 'Les dernières nouveautés.',
            'footer_email' => 'contact@example.com',
            'footer_phone' => '+221 77 000 00 00',
        ])
        ->assertRedirect(route('admin.settings.index'));

    $this->assertDatabaseHas('site_settings', [
        'key' => 'currency_code',
        'value' => 'XOF',
    ]);
    $this->assertDatabaseHas('site_settings', [
        'key' => 'currency_symbol',
        'value' => 'FCFA',
    ]);

    $formatted = app(CurrencyFormatter::class)->format(1250.5);
    expect($formatted)->toEndWith('FCFA')
        ->and(app(CurrencyFormatter::class)->fractionDigits())->toBe(0);
});
