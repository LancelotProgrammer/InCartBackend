<?php

namespace App\Pipes;

use App\Enums\AdvertisementType;
use App\Models\Advertisement;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class GetHome
{
    public function __invoke(Request $request, Closure $next)
    {
        $statuses = Advertisement::with(['branchProducts', 'files'])
            ->where('type', '=', AdvertisementType::STATUS->value)
            ->limit(10)
            ->orderBy('order')
            ->get();
        $videos = Advertisement::with(['branchProducts', 'files'])
            ->where('type', '=', AdvertisementType::VIDEO->value)
            ->limit(10)
            ->orderBy('order')
            ->get();
        $offers = Advertisement::with(['branchProducts', 'files'])
            ->where('type', '=', AdvertisementType::OFFER->value)
            ->limit(10)
            ->orderBy('order')
            ->get();
        $cards = Advertisement::with(['branchProducts', 'files'])
            ->where('type', '=', AdvertisementType::CARD->value)
            ->limit(10)
            ->orderBy('order')
            ->get();

        $products = Product::with(['branchProducts', 'files'])
            ->limit(10)
            ->orderBy('order')
            ->get()
            ->map(function ($product) {
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
            });

        return $next([]);
    }
}
