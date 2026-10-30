<?php

use App\Models\ContactMessage;
use App\Models\User;

it('stores a valid contact form message for administrators', function () {
    $this->get(route('pages.contact'))
        ->assertOk()
        ->assertSee(route('contact.store'))
        ->assertSee('Envoyer le message');

    $this->post(route('contact.store'), [
        'name' => 'Camille Client',
        'email' => 'camille@example.com',
        'subject' => 'Question sur une commande',
        'message' => 'Pouvez-vous me renseigner sur ma commande ?',
    ])
        ->assertRedirect(route('pages.contact'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'Camille Client',
        'email' => 'camille@example.com',
        'subject' => 'Question sur une commande',
        'message' => 'Pouvez-vous me renseigner sur ma commande ?',
    ]);

    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();
    $this->actingAs($admin)
        ->get(route('admin.messages.index'))
        ->assertOk()
        ->assertSee('Question sur une commande')
        ->assertSee('camille@example.com')
        ->assertSee('Pouvez-vous me renseigner sur ma commande ?');
});

it('validates required fields in the contact form', function () {
    $this->from(route('pages.contact'))
        ->post(route('contact.store'), [
            'name' => 'Camille',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => 'short',
        ])
        ->assertSessionHasErrors(['email', 'subject', 'message']);

    expect(ContactMessage::count())->toBe(0);
});
