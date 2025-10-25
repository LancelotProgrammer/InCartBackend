<?php

namespace App\Pipes;

use App\Models\Favorite;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetFavoriteProducts
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        $favorites = Favorite::with(['product.files', 'product.branchProducts'])
            ->where('user_id', $request->user()->id)
            ->get()
            ->filter(function (Favorite $favorite) {
                return $favorite->product->branchProducts->whereNotNull('published_at')->isNotEmpty();
            })
            ->map(function (Favorite $favorite) {
                $product = $favorite->product;
                $branchProduct = $product->branchProducts->first();
                $image = $product->files->first()->url;

                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'image' => $image,
                    'favorite_at' => $favorite->created_at,
                    'max_limit' => $branchProduct->maximum_order_quantity,
                    'min_limit' => $branchProduct->minimum_order_quantity,
                    'price' => $branchProduct->price,
                    'discount' => $branchProduct->discount,
                    'discount_price' => $branchProduct->discount_price,
                    'expired_at' => $branchProduct->expires_at,
                ];
            });

        return $next($favorites);
    }
}
