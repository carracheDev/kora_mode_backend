<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ApplyPaymentOutcome;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FedaPayWebhookVerifier;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class FedaPayWebhookController extends Controller
{
    public function __invoke(Request $request, FedaPayWebhookVerifier $verifier, ApplyPaymentOutcome $applyOutcome): JsonResponse
    {
        $secret = config('services.fedapay.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            throw new ServiceUnavailableHttpException(null, 'Le secret de webhook FedaPay n’est pas configuré.');
        }

        $rawPayload = $request->getContent();

        if (! $verifier->verify($rawPayload, $request->header('X-FEDAPAY-SIGNATURE'), $secret)) {
            return response()->json(['message' => 'Signature de webhook invalide.'], 400);
        }

        try {
            $eventPayload = json_decode($rawPayload, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return response()->json(['message' => 'Contenu de webhook invalide.'], 400);
        }

        if (! is_array($eventPayload)) {
            return response()->json(['message' => 'Contenu de webhook invalide.'], 400);
        }

        $eventId = (string) (Arr::get($eventPayload, 'id') ?: hash('sha256', $rawPayload));
        $eventType = (string) (Arr::get($eventPayload, 'name') ?: Arr::get($eventPayload, 'type') ?: 'unknown');
        $data = $this->eventData($eventPayload);
        $orderNumber = Arr::get($data, 'custom_metadata.order_number')
            ?? Arr::get($data, 'metadata.order_number');
        $transactionId = (string) (Arr::get($data, 'id') ?: Arr::get($eventPayload, 'object_id') ?: '');
        $storedPayload = Arr::only($eventPayload, ['id', 'name', 'type', 'object', 'object_id', 'account_id', 'created_at']);
        $storedPayload['transaction_id'] = $transactionId;
        $storedPayload['order_number'] = $orderNumber;

        try {
            $wasProcessed = DB::transaction(function () use ($eventId, $eventType, $storedPayload, $orderNumber, $transactionId, $applyOutcome): bool {
                $inserted = DB::table('webhook_events')->insertOrIgnore([
                    'provider' => 'fedapay',
                    'event_id' => Str::limit($eventId, 200, ''),
                    'event_type' => Str::limit($eventType, 120, ''),
                    'payload' => json_encode($storedPayload, JSON_THROW_ON_ERROR),
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($inserted === 0) {
                    return false;
                }

                $order = $orderNumber
                    ? Order::query()->where('order_number', $orderNumber)->lockForUpdate()->first()
                    : Order::query()->where('fedapay_transaction_id', $transactionId)->lockForUpdate()->first();

                if (! $order || (string) $order->fedapay_transaction_id !== $transactionId) {
                    return true;
                }

                if ($eventType === 'transaction.approved') {
                    $applyOutcome->handle($order, 'paid');
                } elseif (in_array($eventType, ['transaction.canceled', 'transaction.declined', 'transaction.failed'], true)) {
                    $applyOutcome->handle($order, 'failed');
                }

                return true;
            }, attempts: 3);
        } catch (QueryException $exception) {
            if (! str_contains($exception->getMessage(), 'unique')) {
                throw $exception;
            }

            $wasProcessed = false;
        }

        return response()->json(['received' => true, 'duplicate' => ! $wasProcessed]);
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    private function eventData(array $payload): array
    {
        $data = Arr::get($payload, 'entity') ?? Arr::get($payload, 'data.object') ?? Arr::get($payload, 'data') ?? [];

        if (is_string($data)) {
            $decoded = json_decode($data, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($data) ? $data : [];
    }
}
