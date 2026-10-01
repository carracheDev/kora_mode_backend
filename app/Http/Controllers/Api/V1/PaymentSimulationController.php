<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ApplyPaymentOutcome;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentSimulationController extends Controller
{
    public function store(Request $request, string $orderNumber, ApplyPaymentOutcome $applyOutcome): JsonResponse
    {
        if (config('services.fedapay.driver') !== 'simulation') {
            throw new NotFoundHttpException;
        }

        $attributes = $request->validate([
            'outcome' => ['required', Rule::in(['succeeded', 'failed', 'pending'])],
        ]);
        $order = Order::query()->where('order_number', $orderNumber)->firstOrFail();

        if ($order->user_id !== null && $order->user_id !== $request->user()?->id) {
            throw new NotFoundHttpException;
        }

        if ($order->payment_method === 'cod') {
            return response()->json(['message' => 'Cette commande utilise le paiement à la livraison.'], 422);
        }

        $outcome = $attributes['outcome'] === 'succeeded' ? 'paid' : $attributes['outcome'];
        $order = $applyOutcome->handle($order, $outcome);

        return OrderResource::make($order)->response();
    }
}
