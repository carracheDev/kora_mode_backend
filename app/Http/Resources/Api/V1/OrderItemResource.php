<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'product_name' => $this->product_name,
            'product_slug' => $this->product_slug,
            'sku' => $this->sku,
            'size' => $this->size,
            'color' => $this->color,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'line_total' => $this->line_total,
        ];
    }
}
