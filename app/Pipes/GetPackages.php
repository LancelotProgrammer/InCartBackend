<?php

namespace App\Pipes;

use App\Models\Package;
use Closure;
use Illuminate\Http\Request;

class GetPackages
{
    public function __invoke(Request $request, Closure $next): array
    {
        $user = $request->user();
        $productId = $request->query('product_id');

        $packages = Package::with('products')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($package) use ($productId) {
                $package->contains_product = $productId
                    ? $package->products->contains('id', $productId)
                    : false;
                return $package;
            });

        return $next($packages);
    }
}
