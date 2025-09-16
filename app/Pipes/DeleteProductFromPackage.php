<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Package;
use Closure;
use Illuminate\Http\Request;

class DeleteProductFromPackage
{
    public function __invoke(Request $request, Closure $next): array
    {
        $packageId = $request->route('package_id');
        $productId = $request->route('product_id');

        $package = Package::where('id', $packageId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$package) {
            throw new LogicalException('Package not found or does not belong to the user.');
        }

        $package->products()->detach($productId);

        return $next();
    }
}
