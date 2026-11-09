<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FedaPayCheckoutGateway
{
    public function createCheckoutUrl(Order $order, string $firstName, string $lastName, string $email): string
    {
        if ($order->currency_code !== 'XOF') {
            throw new RuntimeException('FedaPay ne prend en charge que les commandes en XOF.');
        }

        $client = $this->client();
        $transaction = $this->responseData($client->post('/transactions', [
            'description' => 'Commande #'.$order->id,
            'amount' => (int) round((float) $order->total),
            'currency' => ['iso' => $order->currency_code],
            'callback_url' => route('fedapay.return'),
            'custom_metadata' => ['order_id' => (string) $order->id],
            'customer' => [
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $email,
            ],
        ])->throw()->json());

        if (! isset($transaction['id']) || ! is_numeric($transaction['id'])) {
            throw new RuntimeException('FedaPay n’a pas retourné d’identifiant de transaction.');
        }

        $token = $this->responseData(
            $client->post('/transactions/'.$transaction['id'].'/token')->throw()->json()
        );
        $url = $token['url'] ?? null;
        $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;

        if (! is_string($url)
            || parse_url($url, PHP_URL_SCHEME) !== 'https'
            || ! is_string($host)
            || ($host !== 'fedapay.com' && ! str_ends_with($host, '.fedapay.com'))) {
            throw new RuntimeException('FedaPay n’a pas retourné un lien de paiement valide.');
        }

        return $url;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransaction(string $transactionId): array
    {
        if (! ctype_digit($transactionId)) {
            throw new RuntimeException('Identifiant de transaction FedaPay invalide.');
        }

        return $this->responseData(
            $this->client()->get('/transactions/'.$transactionId)->throw()->json()
        );
    }

    public function confirmsOrder(array $transaction, Order $order): bool
    {
        if ($order->currency_code !== 'XOF') {
            return false;
        }

        $orderId = $transaction['custom_metadata']['order_id']
            ?? $transaction['metadata']['order_id']
            ?? null;

        if ((string) $orderId !== (string) $order->id
            || ($transaction['status'] ?? null) !== 'approved'
            || ! isset($transaction['amount'])
            || (int) $transaction['amount'] !== (int) round((float) $order->total)) {
            return false;
        }

        $currency = $transaction['currency']['iso']
            ?? $transaction['currency_iso']
            ?? null;

        return $currency === null || strtoupper((string) $currency) === strtoupper($order->currency_code);
    }

    public function hasValidWebhookSignature(string $payload, string $header, string $secret): bool
    {
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

            if ($key === 't' && is_string($value) && ctype_digit($value)) {
                $timestamp = (int) $value;
            } elseif ($key === 's' && is_string($value)) {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || abs(time() - $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function client(): PendingRequest
    {
        $secret = config('services.fedapay.secret');

        if (! $secret) {
            throw new RuntimeException('La clé secrète FedaPay n’est pas configurée.');
        }

        return Http::baseUrl(rtrim(config('services.fedapay.base_url'), '/'))
            ->withToken($secret)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    /**
     * @return array<string, mixed>
     */
    private function responseData(mixed $response): array
    {
        if (! is_array($response)) {
            throw new RuntimeException('Réponse FedaPay invalide.');
        }

        foreach (['data', 'v1/transaction', 'transaction'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return $response[$key];
            }
        }

        return $response;
    }
}
