<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Package;
use Closure;
use Illuminate\Http\Request;

class GetPackageProducts
{
    public function __invoke(Request $request, Closure $next): array
    {
        $package = Package::with('products')
            ->where('id', $request->route('id'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$package) {
            throw new LogicalException('Package not found or does not belong to the user.');
        }

        return $next([
            'package_id' => $package->id,
            'title' => $package->title,
            'products' => $package->products,
        ]);
    }
}
