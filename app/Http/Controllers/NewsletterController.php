<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Support\ConfiguredSmtpMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class NewsletterController extends Controller
{
    public function store(Request $request, ConfiguredSmtpMailer $smtpMailer): RedirectResponse
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

        if (! $smtpMailer->configure()) {
            Log::notice('Newsletter confirmation email was not sent because SMTP is disabled.', [
                'subscriber_id' => $subscriber->id,
            ]);

            return back()->with(
                'newsletter_error',
                'Votre inscription est enregistrée, mais l’e-mail de confirmation n’a pas été envoyé : configurez et activez un fournisseur e-mail dans les paramètres du site.'
            );
        }

        try {
            Mail::mailer($smtpMailer->mailerName())->raw(
                "Merci pour votre inscription à notre newsletter.\n\nPour vous désinscrire, cliquez sur le lien suivant :\n"
                    .route('newsletter.unsubscribe', $subscriber->unsubscribe_token),
                fn ($message) => $message->to($email)->subject('Confirmation de votre inscription à la newsletter')
            );
        } catch (TransportExceptionInterface $exception) {
            Log::error('Newsletter confirmation email could not be delivered.', [
                'subscriber_id' => $subscriber->id,
                'exception' => $exception,
            ]);

            return back()->with(
                'newsletter_error',
                'Votre inscription est enregistrée, mais le fournisseur e-mail n’a pas pu envoyer l’e-mail de confirmation. Vérifiez sa configuration.'
            );
        }

        return back()->with('newsletter_success', 'Merci, votre inscription à la newsletter est enregistrée.');
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->firstOrFail();
        $subscriber->update(['unsubscribed_at' => now()]);

        return redirect()->route('home')->with('newsletter_success', 'Votre désinscription de la newsletter est prise en compte.');
    }
}
