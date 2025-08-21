<?php

namespace App\Pipes;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class GetProducts
{
    public function __invoke(Request $request, Closure $next)
    {
        $products = Product::with(['branchProducts', 'files'])->simplePaginate();

        return $next([
            collect($products->items())->map(function ($product) {
                $branchProduct = $product->branchProducts->first();
                $image = $product->files->first() ? $product->files->first()->url : null;

                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $branchProduct ? (string) $branchProduct->price : null,
                    'discount' => $branchProduct ? (string) $branchProduct->discount : null,
                    'discount_price' => $branchProduct ? (string) $branchProduct->discount_price : null,
                    'unit' => $branchProduct ? (string) $branchProduct->unit->value : null,
                    'expired_at' => $branchProduct ? $branchProduct->expires_at->format('Y') : null,
                    'limit' => $branchProduct ? (int) $branchProduct->maximum_order_quantity : null,
                    'image' => $image,
                ];
            }),
            [
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
            ],
        ]);
    }
}
