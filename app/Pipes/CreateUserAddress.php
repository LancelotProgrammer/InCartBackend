<?php

namespace App\Pipes;

use App\Enums\UserAddressType;
use App\Models\UserAddress;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CreateUserAddress
{
    public function __invoke(Request $request, Closure $next)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone'       => 'required|string|max:20',
            'type'        => ['required', new Enum(UserAddressType::class)],
            'longitude'   => 'required|numeric',
            'latitude'    => 'required|numeric',
        ]);

        $address = UserAddress::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'city_id' => $request->user()->city_id,
        ]);

        return $next($address);
    }
}
