<?php

namespace App\Pipes;

use App\Models\Package;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetPackages
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        $user = $request->user();
        $productId = $request->query('product_id');

        $packages = Package::with('products')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($package) use ($productId) {
                $package->contains_product = $productId ? $package->products->contains('id', $productId) : false;
                unset($package->products);
                return $package;
            });

        return $next($packages);
    }
}
