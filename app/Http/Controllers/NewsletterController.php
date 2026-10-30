<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => trim((string) $request->input('email'))]);
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = Str::lower(trim($validated['email']));
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber) {
            if (! $subscriber->unsubscribed_at) {
                return back()->with('newsletter_success', 'Cette adresse est déjà inscrite à la newsletter.');
            }
            $subscriber->update([
                'unsubscribed_at' => null,
                'subscribed_at' => now(),
            ]);
        } else {
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'unsubscribe_token' => Str::random(64),
                'subscribed_at' => now(),
            ]);
        }

        Mail::raw(
            "Merci pour votre inscription à notre newsletter.\n\nPour vous désinscrire, cliquez sur le lien suivant :\n"
                .route('newsletter.unsubscribe', $subscriber->unsubscribe_token),
            fn ($message) => $message->to($email)->subject('Confirmation de votre inscription à la newsletter')
        );

        return back()->with('newsletter_success', 'Merci, votre inscription à la newsletter est enregistrée.');
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();
        $subscriber->update(['unsubscribed_at' => now()]);

        return redirect()->route('home')->with('newsletter_success', 'Votre désinscription de la newsletter est prise en compte.');
    }
}
