<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'added_at' => $this->whenPivotLoadedAs('favorite', 'favorites', fn () => $this->favorite->created_at),
            'product' => ProductResource::make($this->resource),
        ];
    }
}
