<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $hasPromotion = $this->old_price !== null && $this->old_price > $this->price;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'gender' => $this->gender,
            'subcategory' => $this->subcategory,
            'price' => (int) $this->price,
            'old_price' => $this->old_price === null ? null : (int) $this->old_price,
            'is_promo' => $hasPromotion,
            'discount_percent' => $hasPromotion ? (int) round((1 - ($this->price / $this->old_price)) * 100) : null,
            'images' => $this->images,
            'sizes' => $this->sizes,
            'colors' => $this->colors,
            'stock' => $this->stock,
            'in_stock' => $this->stock > 0,
            'tags' => $this->tags,
            'rating' => $this->rating === null ? null : (float) $this->rating,
            'popularity' => $this->popularity,
            'is_featured' => $this->is_featured,
            'is_demo_data' => $this->is_demo_data,
            'created_at' => $this->created_at,
        ];
    }
}
