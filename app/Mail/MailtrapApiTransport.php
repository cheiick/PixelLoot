<?php

namespace App\Mail;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailtrapApiTransport extends AbstractTransport
{
    public function __construct(
        private string $token,
        private string $endpoint,
        private string $category = 'Transactional',
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new TransportException('Le transport Mailtrap nécessite un e-mail Symfony.');
        }

        $sender = $message->getEnvelope()->getSender();
        $payload = [
            'from' => [
                'email' => $sender->getAddress(),
                'name' => $sender->getName(),
            ],
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject() ?? '',
            'category' => $this->category,
        ];

        if ($email->getCc()) {
            $payload['cc'] = $this->formatAddresses($email->getCc());
        }

        if ($email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($email->getBcc());
        }

        if ($email->getTextBody() !== null) {
            $payload['text'] = $email->getTextBody();
        }

        if ($email->getHtmlBody() !== null) {
            $payload['html'] = $email->getHtmlBody();
        }

        try {
            $response = Http::acceptJson()
                ->withToken($this->token)
                ->post($this->endpoint, $payload);
        } catch (ConnectionException $exception) {
            throw new TransportException('Impossible de joindre l’API Mailtrap Email Sending.', 0, $exception);
        }

        if ($response->failed()) {
            throw new TransportException(
                'Mailtrap Email Sending a refusé le message (HTTP '.$response->status().'). Vérifiez le jeton API et l’adresse expéditrice.'
            );
        }
    }

    public function __toString(): string
    {
        return 'mailtrap-api';
    }

    /**
     * @param  Address[]  $addresses
     * @return array<int, array{email: string}>
     */
    private function formatAddresses(array $addresses): array
    {
        return array_map(
            static fn (Address $address): array => ['email' => $address->getAddress()],
            $addresses
        );
    }
}
