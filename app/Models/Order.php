<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_number', 'user_id', 'customer_name', 'customer_email', 'customer_phone',
    'customer_city', 'customer_address', 'subtotal', 'delivery_fee', 'discount_amount',
    'promo_code', 'total', 'status', 'payment_method', 'payment_status',
    'fedapay_transaction_id', 'payment_url', 'stock_reserved_at', 'stock_released_at', 'paid_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'stock_reserved_at' => 'immutable_datetime',
            'stock_released_at' => 'immutable_datetime',
            'paid_at' => 'immutable_datetime',
        ];
    }
}
