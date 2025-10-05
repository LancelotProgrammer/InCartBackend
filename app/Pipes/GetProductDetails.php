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

        $product = Product::where('id', '=', $request->route('id'))
            ->first();
        $branchProduct = $product->branchProducts->first();

        if ($branchProduct->published_at === null)
        {
            throw new LogicalException('product is not active', 'You are trying to get a product which is not active for the selected branch');
        }

        $images = $product->files()->get();
        $imagesResult = [];
        foreach ($images as $image) {
            $imagesResult[] = $image->url;
        }

        $categoryIds = $product->categories->pluck('id')->toArray();
        $relatedProducts = Product::whereHas('categories', function (Builder $query) use ($categoryIds): void {
            $query->whereIn('categories.id', $categoryIds);
        })
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->filter(function (Product $product) {
                return $product->branchProducts->whereNotNull('published_at')->isNotEmpty();
            })
            ->map(function (Product $related): array {
                $branchProduct = $related->branchProducts->first();
                $image = $related->files->first();

                return [
                    'id' => $related->id,
                    'title' => $related->title,
                    'image' => $image->url,
                    'max_limit' => $branchProduct->maximum_order_quantity > $branchProduct->quantity ? $branchProduct->quantity : $branchProduct->maximum_order_quantity,
                    'min_limit' => $branchProduct->minimum_order_quantity,
                    'price' => $branchProduct->price,
                    'discount' => $branchProduct->discount,
                    'discount_price' => $branchProduct->discount_price,
                    'expired_at' => $branchProduct->expires_at,
                ];
            });

        return $next([
            'id' => $product->id,
            'title' => $product->title,
            'description' => $product->description,
            'image' => $imagesResult,
            'max_limit' => $branchProduct->maximum_order_quantity > $branchProduct->quantity ? $branchProduct->quantity : $branchProduct->maximum_order_quantity,
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
