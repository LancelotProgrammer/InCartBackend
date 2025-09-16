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
        $package = Package::with('products.files')
            ->where('id', $request->route('id'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$package) {
            throw new LogicalException('Package not found or does not belong to the user.');
        }

        $products = $package->products->map(function (Product $product) {
            $image = $product->files->first()?->url;

            return [
                'id' => $product->id,
                'title' => $product->title,
                'image' => $image,
                'created_at' => $product->created_at,
            ];
        });

        return $next([
            'package_id' => $package->id,
            'title' => $package->title,
            'products' => $products,
        ]);
    }
}
