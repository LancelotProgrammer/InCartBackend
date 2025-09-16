<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Package;
use Closure;
use Illuminate\Http\Request;

class DeletePackage
{
    public function __invoke(Request $request, Closure $next): array
    {
        $package = Package::where('id', $request->route('id'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$package) {
            throw new LogicalException('Package not found or does not belong to the user.');
        }

        $package->delete();

        return $next();
    }
}
