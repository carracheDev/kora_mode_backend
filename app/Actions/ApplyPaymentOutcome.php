<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplyPaymentOutcome
{
    public function handle(Order $order, string $outcome): Order
    {
        if (! in_array($outcome, ['paid', 'failed', 'pending'], true)) {
            throw ValidationException::withMessages(['outcome' => 'Résultat de paiement invalide.']);
        }

        return DB::transaction(function () use ($order, $outcome): Order {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->payment_status === 'paid' || $lockedOrder->payment_status === 'failed') {
                return $lockedOrder;
            }

            if ($outcome === 'pending') {
                return $lockedOrder;
            }

            if ($outcome === 'paid') {
                $lockedOrder->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'paid_at' => now(),
                ]);

                return $lockedOrder->fresh('items');
            }

            $quantities = $lockedOrder->items
                ->whereNotNull('product_id')
                ->groupBy('product_id')
                ->map(fn ($items): int => $items->sum('quantity'));
            $products = Product::query()
                ->whereIn('id', $quantities->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($quantities as $productId => $quantity) {
                $products->get($productId)?->increment('stock', $quantity);
            }

            $lockedOrder->update([
                'payment_status' => 'failed',
                'status' => 'cancelled',
                'stock_released_at' => now(),
            ]);

            return $lockedOrder->fresh('items');
        }, attempts: 3);
    }
}
