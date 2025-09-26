<?php

namespace App\Pipes;

use App\Models\Package;
use Closure;
use Illuminate\Http\Request;

class CreatePackage
{
    public function __invoke(Request $request, Closure $next): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $package = Package::create([
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        return $next([
            'id' => $package->id,
            'title' => $package->title,
            'description' => $package->description,
            'created_at' => $package->created_at,
        ]);
    }
}
