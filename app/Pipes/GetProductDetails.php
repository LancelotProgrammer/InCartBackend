<?php

namespace App\Pipes;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class GetProductDetails
{
    public function __invoke(Request $request, Closure $next)
    {
        $request->merge(['id' => $request->route('id')]);

        $request->validate([
            'id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::with(['branchProducts', 'files'])->where('id', '=', $request->route('id'))->first();
        $branchProduct = $product->branchProducts->first();
        $images = $product->files()->get();
        $imagesResult = [];
        foreach ($images as $image) {
            $imagesResult[] = $image->url;
        }

        $categoryIds = $product->categories->pluck('id')->toArray();
        $relatedProducts = Product::whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })
            ->where('id', '!=', $product->id)
            ->with(['branchProducts', 'files'])
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->map(function ($related) {
                $branchProduct = $related->branchProducts->first();
                $image = $related->files->first();

                return [
                    'id' => $related->id,
                    'title' => $related->title,
                    'image' => $image->url,
                    'price' => $branchProduct ? (string) $branchProduct->price : null,
                    'discount' => $branchProduct ? (string) $branchProduct->discount : null,
                    'discount_price' => $branchProduct ? (string) $branchProduct->discount_price : null,
                    'unit' => $branchProduct ? (string) $related->unit->value : null,
                    'expired_at' => $branchProduct ? $branchProduct->expires_at->format('Y') : null,
                    'limit' => $branchProduct ? (int) $branchProduct->maximum_order_quantity : null,
                ];
            });

        return $next([
            'id' => $product->id,
            'title' => $product->title,
            'description' => $product->description,
            'image' => $imagesResult,
            'price' => $branchProduct ? (string) $branchProduct->price : null,
            'discount' => $branchProduct ? (string) $branchProduct->discount : null,
            'discount_price' => $branchProduct ? (string) $branchProduct->discount_price : null,
            'unit' => $branchProduct ? (string) $product->unit->value : null,
            'expired_at' => $branchProduct ? $branchProduct->expires_at->format('Y') : null,
            'limit' => $branchProduct ? (int) $branchProduct->maximum_order_quantity : null,
            'related' => $relatedProducts,
        ]);
    }
}
