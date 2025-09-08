<?php

namespace App\Pipes;

use App\Enums\UserAddressType;
use App\Models\UserAddress;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class UpdateUserAddress
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'type' => ['required', new Enum(UserAddressType::class)],
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ]);

        UserAddress::where('user_id', '=', $request->user()->id)
            ->where('city_id', '=', $request->user()->city_id)
            ->update($validated);

        return $next();
    }
}
