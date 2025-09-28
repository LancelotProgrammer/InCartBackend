<?php

namespace App\Pipes;

use App\Models\Product;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetProducts
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'discounted' => 'nullable|boolean',
        ]);

        $query = Product::query();

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function (Builder $query) use ($request): void {
                $query->where('categories.id', $request->input('category_id'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $query) use ($search) {
                $query->where('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ar', 'like', "%{$search}%")
                    ->orWhere('description->en', 'like', "%{$search}%")
                    ->orWhere('description->ar', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('discounted')) {
            $query->whereHas('branchProducts', function ($query) {
                $query->where('discount', '>', 0);
            });
        }

        $products = $query->simplePaginate();

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
