<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Package;
use Closure;
use Illuminate\Http\Request;

class UpdatePackage
{
    public function __invoke(Request $request, Closure $next): array
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $package = Package::where('id', $request->route('id'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $package) {
            throw new LogicalException('Package not found or does not belong to the user.');
        }

        $package->update($data);

        return $next([
            'id' => $package->id,
            'title' => $package->title,
            'description' => $package->description,
            'created_at' => $package->created_at,
        ]);
    }
}
