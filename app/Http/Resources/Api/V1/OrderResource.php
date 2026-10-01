<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
                'city' => $this->customer_city,
                'address' => $this->customer_address,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->delivery_fee,
            'discount_amount' => $this->discount_amount,
            'promo_code' => $this->promo_code,
            'total' => $this->total,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_url' => $this->payment_url,
            'created_at' => $this->created_at,
        ];
    }
}
