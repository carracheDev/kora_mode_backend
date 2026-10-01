<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class FedaPayService
{
    public function createPaymentUrl(Order $order): string
    {
        $apiKey = config('services.fedapay.secret_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new ServiceUnavailableHttpException(null, 'Le paiement FedaPay sandbox n’est pas configuré.');
        }

        $client = $this->client($apiKey);
        $transactionId = $order->fedapay_transaction_id;

        if (! $transactionId) {
            $transactionResponse = $client->post('transactions', [
                'description' => 'Commande KORA MODE '.$order->order_number,
                'amount' => $order->total,
                'currency' => ['iso' => 'XOF'],
                'callback_url' => $this->callbackUrl($order),
                'customer' => $this->customerPayload($order),
                'custom_metadata' => ['order_number' => $order->order_number, 'payment_method' => $order->payment_method],
            ])->throw()->json();

            $transaction = $transactionResponse['v1/transaction']
                ?? data_get($transactionResponse, 'transaction')
                ?? data_get($transactionResponse, 'data')
                ?? $transactionResponse;
            $transactionId = data_get($transaction, 'id');

            if (! is_string($transactionId) && ! is_int($transactionId)) {
                throw new ServiceUnavailableHttpException(null, 'FedaPay n’a pas renvoyé d’identifiant de transaction.');
            }

            $order->update(['fedapay_transaction_id' => (string) $transactionId]);
        }

        $tokenResponse = $client->post('transactions/'.rawurlencode((string) $transactionId).'/token')->throw()->json();
        $paymentUrl = data_get($tokenResponse, 'url')
            ?? data_get($tokenResponse, 'v1/token.url')
            ?? data_get($tokenResponse, 'token.url');

        if (! is_string($paymentUrl) || ! filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
            throw new ServiceUnavailableHttpException(null, 'FedaPay n’a pas renvoyé de lien de paiement valide.');
        }

        $order->update(['payment_url' => $paymentUrl]);

        return $paymentUrl;
    }

    private function client(string $apiKey): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.fedapay.base_url'), '/').'/')
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    /** @return array<string, mixed> */
    private function customerPayload(Order $order): array
    {
        $nameParts = preg_split('/\s+/', trim($order->customer_name), 2) ?: [];

        return [
            'firstname' => $nameParts[0] ?? $order->customer_name,
            'lastname' => $nameParts[1] ?? $nameParts[0] ?? $order->customer_name,
            'email' => $order->customer_email,
            'phone_number' => [
                'number' => $this->localPhoneNumber($order->customer_phone),
                'country' => 'bj',
            ],
        ];
    }

    private function localPhoneNumber(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return str_starts_with($digits, '229') ? substr($digits, 3) : $digits;
    }

    private function callbackUrl(Order $order): string
    {
        $callbackUrl = config('services.fedapay.callback_url');

        if (is_string($callbackUrl) && $callbackUrl !== '') {
            return str_replace('{order_number}', $order->order_number, $callbackUrl);
        }

        return rtrim((string) config('services.fedapay.frontend_url'), '/').'/commande/'.$order->order_number;
    }
}
