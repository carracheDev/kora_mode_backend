<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FavoriteResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class FavoriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $request->user()
            ->favoriteProducts()
            ->active()
            ->with('category')
            ->orderByPivot('created_at', 'desc')
            ->paginate(24);

        return FavoriteResource::collection($products);
    }

    public function store(Request $request): JsonResponse
    {
        $attributes = $request->validate([
            'product_slug' => ['required', 'string', 'exists:products,slug'],
        ]);

        $product = Product::query()
            ->active()
            ->where('slug', $attributes['product_slug'])
            ->firstOrFail();

        $user = $request->user();
        $user->favoriteProducts()->syncWithoutDetaching([$product->id]);

        $favorite = $user->favoriteProducts()
            ->with('category')
            ->whereKey($product->id)
            ->firstOrFail();

        return FavoriteResource::make($favorite)->response()->setStatusCode(201);
    }

    public function destroy(Request $request, string $slug): Response
    {
        $product = Product::query()->where('slug', $slug)->firstOrFail();
        $request->user()->favoriteProducts()->detach($product->id);

        return response()->noContent();
    }
}
