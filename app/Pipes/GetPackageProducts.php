<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Package;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class GetPackageProducts
{
    public function __invoke(Request $request, Closure $next): array
    {
        $package = Package::with(['products.files', 'products.branchProducts'])
            ->where('id', $request->route('id'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$package) {
            throw new LogicalException('Package not found or does not belong to the user.');
        }

        $products = $package->products->map(function (Product $product) {
            $branchProduct = $product->branchProducts->first();
            $image = $product->files->first()?->url;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'image' => $image,
                'created_at' => $product->created_at,
                'max_limit' => $branchProduct?->maximum_order_quantity > $branchProduct?->quantity ? $branchProduct?->quantity : $branchProduct?->maximum_order_quantity,
                'min_limit' => $branchProduct?->minimum_order_quantity,
                'price' => $branchProduct->price,
                'discount' => $branchProduct?->discount,
                'discount_price' => $branchProduct?->discount_price,
                'expired_at' => $branchProduct->expires_at,
            ];
        });

        return $next([
            'package_id' => $package->id,
            'title' => $package->title,
            'products' => $products,
        ]);
    }
}
