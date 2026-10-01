<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id', 'name', 'slug', 'sku', 'description', 'gender', 'subcategory',
    'price', 'old_price', 'images', 'sizes', 'colors', 'stock', 'tags', 'rating',
    'popularity', 'is_active', 'is_featured', 'is_demo_data',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return Builder<Product> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return Builder<Product> */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /** @return Builder<Product> */
    public function scopeOnPromotion(Builder $query): Builder
    {
        return $query->whereNotNull('old_price')->whereColumn('old_price', '>', 'price');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'sizes' => 'array',
            'colors' => 'array',
            'tags' => 'array',
            'rating' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_demo_data' => 'boolean',
        ];
    }
}
