<?php

namespace App\Pipes;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetAdvertisements
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
        ]);

        $products = Product::whereHas('branchProducts', function ($query) {
            $query->whereNotNull('discount');
        })->simplePaginate();

        return $next(collect($products->items())->map(function (Product $product): array {
            $branchProduct = $product->branchProducts->first();
            $image = $product->files->first()?->url;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'image' => $image,
                'max_limit' => $branchProduct?->maximum_order_quantity > $branchProduct?->quantity ? $branchProduct?->quantity : $branchProduct?->maximum_order_quantity,
                'min_limit' => $branchProduct?->minimum_order_quantity,
                'price' => $branchProduct->price,
                'discount' => $branchProduct?->discount,
                'discount_price' => $branchProduct?->discount_price,
                'expired_at' => $branchProduct->expires_at,
            ];
        }));
    }
}
