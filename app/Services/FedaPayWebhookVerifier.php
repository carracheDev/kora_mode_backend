<?php

namespace App\Services;

class FedaPayWebhookVerifier
{
    public function verify(string $payload, ?string $signatureHeader, string $secret, int $toleranceSeconds = 300): bool
    {
        if ($signatureHeader === null || $secret === '') {
            return false;
        }

        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $signatureHeader) as $part) {
            $segments = explode('=', trim($part), 2);

            if (count($segments) !== 2) {
                continue;
            }

            [$key, $value] = $segments;

            if ($key === 't' && ctype_digit($value)) {
                $timestamp = (int) $value;
            } elseif ($key === 's') {
                $signatures[] = $value;
            }
        }

        if ($timestamp === null || $signatures === [] || abs(time() - $timestamp) > $toleranceSeconds) {
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
}
