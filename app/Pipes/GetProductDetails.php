<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Product;
use App\Services\FavoriteService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetProductDetails
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->merge(['id' => $request->route('id')]);

        $request->validate([
            'id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::where('id', '=', $request->route('id'))->first();
        $branchProduct = $product->branchProducts->first();
        $imagesResult = [];
        foreach ($product->files()->get() as $image) {
            $imagesResult[] = $image->url;
        }

        if ($branchProduct->published_at === null) {
            throw new LogicalException('product is not active', 'You are trying to get a product which is not active for the selected branch');
        }

        $relatedProducts = Product::whereHas('categories', fn($q) => $q->whereIn('categories.id', $product->categories->pluck('id')->toArray()))
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->filter->isPublishedInBranches()
            ->map->toApiArray()
            ->values();

        return $next([
            'id' => $product->id,
            'title' => $product->title,
            'description' => $product->description,
            'image' => $imagesResult,
            'max_limit' => $branchProduct->maximum_order_quantity,
            'min_limit' => $branchProduct->minimum_order_quantity,
            'price' => $branchProduct->price,
            'discount' => $branchProduct->discount,
            'discount_price' => $branchProduct->discount_price,
            'expired_at' => $branchProduct->expires_at,
            'is_favorite' => auth('sanctum')->user()?->id !== null ? FavoriteService::isProductFavorite($product->id, auth('sanctum')->user()->id) : false,
            'related' => $relatedProducts,
        ]);
    }
}
