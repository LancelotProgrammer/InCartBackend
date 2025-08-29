<?php

namespace App\Pipes;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class GetProducts
{
    public function __invoke(Request $request, Closure $next)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Product::with(['branchProducts', 'files']);

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($query) use ($request) {
                $query->where('categories.id', $request->input('category_id'));
            });
        }
        
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ar', 'like', "%{$search}%")
                    ->orWhere('description->en', 'like', "%{$search}%")
                    ->orWhere('description->ar', 'like', "%{$search}%");
            });
        }

        $products = $query->simplePaginate();

        return $next([
            collect($products->items())->map(function ($product) {
                $branchProduct = $product->branchProducts->first();
                $image = $product->files->first()?->url;

                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'image' => $image,
                    'price' => $branchProduct?->price,
                    'discount' => $branchProduct?->discount,
                    'discount_price' => $branchProduct?->discount_price,
                    'unit' => $product?->unit->value,
                    'expired_at' => $branchProduct?->expires_at?->format('Y'),
                    'limit' => $branchProduct?->maximum_order_quantity,
                ];
            }),
            [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
            ],
        ]);
    }
}
