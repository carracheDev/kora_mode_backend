<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FedaPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PaymentController extends Controller
{
    public function store(Request $request, string $orderNumber, FedaPayService $fedaPay): JsonResponse
    {
        $order = Order::query()->where('order_number', $orderNumber)->firstOrFail();

        if ($order->user_id !== null && $order->user_id !== $request->user()?->id) {
            abort(404);
        }

        if ($order->payment_method === 'cod') {
            throw new UnprocessableEntityHttpException('Cette commande est réglée à la livraison.');
        }

        if ($order->payment_status === 'paid') {
            throw new ConflictHttpException('Cette commande est déjà payée.');
        }

        if (config('services.fedapay.driver') === 'simulation') {
            return response()->json(['data' => ['simulation' => true, 'order_number' => $order->order_number]]);
        }

        if ($order->payment_url) {
            return response()->json(['data' => ['payment_url' => $order->payment_url, 'order_number' => $order->order_number]]);
        }

        $paymentUrl = $fedaPay->createPaymentUrl($order);

        return response()->json(['data' => ['payment_url' => $paymentUrl, 'order_number' => $order->order_number]]);
    }
}
