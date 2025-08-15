<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProducts(Request $request): SuccessfulResponseResourceWithMetadata
    {
        $products = Product::with(['branchProducts', 'files'])->simplePaginate();

        return new SuccessfulResponseResourceWithMetadata(
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
        );
    }

    public function getProductDetails(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
