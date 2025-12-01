<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Package;
use Closure;
use Illuminate\Http\Request;

class AddProductToPackage
{
    public function __invoke(Request $request, Closure $next): array
    {
        $packageId = $request->route('package_id');
        $productId = $request->route('product_id');

        $package = Package::where('id', $packageId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $package) {
            throw new LogicalException('Package not found or does not belong to the user.');
        }

        if ($package->products()->where('product_id', $productId)->exists()) {
            throw new LogicalException('Product already exists in this package.');
        }

        if ($package->products()->count() >= 50) {
            throw new LogicalException('A package cannot contain more than 50 products.');
        }

        $package->products()->attach($productId);

        return $next([]);
    }
}
