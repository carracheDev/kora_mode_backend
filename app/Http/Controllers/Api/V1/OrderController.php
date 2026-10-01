<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(20);

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request, CreateOrder $createOrder): JsonResponse
    {
        $order = $createOrder->handle($request->validated(), $request->user());

        return OrderResource::make($order)->response()->setStatusCode(201);
    }

    public function show(Request $request, string $orderNumber): OrderResource
    {
        $order = Order::query()->with('items')->where('order_number', $orderNumber)->firstOrFail();

        if ($order->user_id !== null && $order->user_id !== $request->user()?->id) {
            abort(404);
        }

        return OrderResource::make($order);
    }
}
