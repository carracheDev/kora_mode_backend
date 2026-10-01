<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateOrder
{
    private const COMPLETE_LOOK_SLUGS = [
        'blazer-oversize-nova',
        'pantalon-wide-leg-atlas',
        'sneakers-blanc-studio',
    ];

    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes, ?User $user): Order
    {
        return DB::transaction(function () use ($attributes, $user): Order {
            $items = $attributes['items'];
            $slugs = collect($items)->pluck('product_slug')->unique()->sort()->values();
            $products = Product::query()
                ->active()
                ->whereIn('slug', $slugs)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('slug');

            if ($products->count() !== $slugs->count()) {
                throw ValidationException::withMessages(['items' => 'Un article n’est plus disponible. Actualisez votre panier.']);
            }

            if ($attributes['payment_method'] !== 'cod' && config('services.fedapay.driver') === 'fedapay' && empty($attributes['customer']['email'])) {
                throw ValidationException::withMessages(['customer.email' => 'L’e-mail est requis pour ouvrir un paiement FedaPay.']);
            }

            $this->validateBundleSelection($items);
            $this->validateVariants($items, $products->all());
            $this->validateAndReserveStock($items, $products->all());

            $lineItems = [];
            $subtotal = 0;
            $bundleDiscount = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_slug']);
                $quantity = (int) $item['quantity'];
                $baseLineTotal = (int) $product->price * $quantity;
                $lineDiscount = ($item['bundle_id'] ?? null) === 'complete-look-10'
                    ? (int) round($baseLineTotal * 0.10)
                    : 0;
                $subtotal += $baseLineTotal;
                $bundleDiscount += $lineDiscount;
                $lineItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'line_discount' => $lineDiscount,
                    'line_total' => $baseLineTotal - $lineDiscount,
                ];
            }

            $promo = $this->findPromoCode($attributes['promo_code'] ?? null);
            $promoDiscount = $this->calculatePromoDiscount($promo, $lineItems);
            $deliveryFee = $this->deliveryFee($attributes['customer']['city']);
            $discountAmount = $bundleDiscount + $promoDiscount;

            $order = Order::query()->create([
                'order_number' => (string) Str::uuid(),
                'user_id' => $user?->id,
                'customer_name' => trim($attributes['customer']['name']),
                'customer_email' => $attributes['customer']['email'] ?? null,
                'customer_phone' => trim($attributes['customer']['phone']),
                'customer_city' => trim($attributes['customer']['city']),
                'customer_address' => trim($attributes['customer']['address']),
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'promo_code' => $promo?->code,
                'total' => max(0, $subtotal - $discountAmount) + $deliveryFee,
                'status' => $attributes['payment_method'] === 'cod' ? 'confirmed' : 'pending',
                'payment_method' => $attributes['payment_method'],
                'payment_status' => $attributes['payment_method'] === 'cod' ? 'unpaid' : 'pending',
                'stock_reserved_at' => now(),
            ]);

            foreach ($lineItems as $lineItem) {
                /** @var Product $product */
                $product = $lineItem['product'];
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'sku' => $product->sku,
                    'size' => $lineItem['size'],
                    'color' => $lineItem['color'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $product->price,
                    'discount_amount' => $lineItem['line_discount'],
                    'line_total' => $lineItem['line_total'],
                ]);
            }

            return $order->load('items');
        }, attempts: 3);
    }

    /** @param array<int, array<string, mixed>> $items */
    private function validateBundleSelection(array $items): void
    {
        $lookItems = collect($items)->filter(fn (array $item): bool => ($item['bundle_id'] ?? null) === 'complete-look-10');

        if ($lookItems->isEmpty()) {
            return;
        }

        $slugs = $lookItems->pluck('product_slug')->sort()->values()->all();

        if ($slugs !== collect(self::COMPLETE_LOOK_SLUGS)->sort()->values()->all() || $lookItems->contains(fn (array $item): bool => (int) $item['quantity'] !== 1)) {
            throw ValidationException::withMessages(['items' => 'Le tarif du look complet s’applique uniquement à ses trois pièces, à raison d’une unité par article.']);
        }
    }

    /** @param array<int, array<string, mixed>> $items @param array<string, Product> $products */
    private function validateVariants(array $items, array $products): void
    {
        foreach ($items as $index => $item) {
            $product = $products[$item['product_slug']];
            $sizes = $product->sizes ?? [];
            $colors = collect($product->colors ?? [])->pluck('name')->all();

            if ($sizes !== [] && ! in_array($item['size'] ?? null, $sizes, true)) {
                throw ValidationException::withMessages(["items.{$index}.size" => 'Choisissez une taille disponible pour cet article.']);
            }

            if ($colors !== [] && ! in_array($item['color'] ?? null, $colors, true)) {
                throw ValidationException::withMessages(["items.{$index}.color" => 'Choisissez une couleur disponible pour cet article.']);
            }
        }
    }

    /** @param array<int, array<string, mixed>> $items @param array<string, Product> $products */
    private function validateAndReserveStock(array $items, array $products): void
    {
        $requested = collect($items)->groupBy('product_slug')->map(fn ($lines): int => $lines->sum(fn (array $item): int => (int) $item['quantity']));

        foreach ($requested as $slug => $quantity) {
            $product = $products[$slug];

            if ($product->stock < $quantity) {
                throw ValidationException::withMessages(['items' => "Le stock disponible pour {$product->name} est insuffisant."]);
            }
        }

        foreach ($requested as $slug => $quantity) {
            $products[$slug]->decrement('stock', $quantity);
        }
    }

    private function findPromoCode(?string $code): ?PromoCode
    {
        if ($code === null || trim($code) === '') {
            return null;
        }

        $promo = PromoCode::query()->where('code', mb_strtoupper(trim($code)))->first();

        if (! $promo || ! $promo->isCurrentlyValid()) {
            throw ValidationException::withMessages(['promo_code' => 'Ce code promo est inconnu ou n’est pas actif.']);
        }

        return $promo;
    }

    /** @param array<int, array<string, mixed>> $lineItems */
    private function calculatePromoDiscount(?PromoCode $promo, array $lineItems): int
    {
        if (! $promo) {
            return 0;
        }

        $eligibleTotal = collect($lineItems)
            ->filter(fn (array $lineItem): bool => $promo->eligible_slugs === null || in_array($lineItem['product']->slug, $promo->eligible_slugs, true))
            ->sum('line_total');

        if ($eligibleTotal <= 0) {
            throw ValidationException::withMessages(['promo_code' => 'Aucun article du panier n’est éligible à ce code promo.']);
        }

        return (int) round($eligibleTotal * $promo->discount_percent / 100);
    }

    private function deliveryFee(string $city): int
    {
        $fee = Arr::get(config('services.kora.delivery_fees'), $city);

        if (! is_int($fee)) {
            throw ValidationException::withMessages(['customer.city' => 'La zone de livraison sélectionnée n’est pas prise en charge.']);
        }

        return $fee;
    }
}
