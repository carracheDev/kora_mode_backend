<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'gender' => ['nullable', Rule::in(['femme', 'homme', 'mixte'])],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0', 'gte:min_price'],
            'promo' => ['sometimes', 'boolean'],
            'in_stock' => ['sometimes', 'boolean'],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc', 'popular'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:48'],
        ]);

        $query = Product::query()->active()->with('category');

        if (isset($filters['q'])) {
            $search = mb_strtolower($filters['q']);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
            });
        }

        if (isset($filters['category'])) {
            $query->whereHas('category', fn (Builder $builder): Builder => $builder->where('slug', $filters['category']));
        }

        if (isset($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (isset($filters['subcategory'])) {
            $query->where('subcategory', $filters['subcategory']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (($filters['promo'] ?? false) === true || ($filters['promo'] ?? null) === '1') {
            $query->onPromotion();
        }

        if (($filters['in_stock'] ?? false) === true || ($filters['in_stock'] ?? null) === '1') {
            $query->inStock();
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            'popular' => $query->orderByDesc('popularity')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        return ProductResource::collection($query->paginate($filters['per_page'] ?? 12)->withQueryString());
    }

    public function show(string $slug): ProductResource
    {
        $product = Product::query()
            ->active()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }
}
