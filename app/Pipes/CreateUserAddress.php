<?php

namespace App\Pipes;

use App\Enums\UserAddressType;
use App\Exceptions\LogicalException;
use App\Models\City;
use App\Models\UserAddress;
use App\Services\DistanceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CreateUserAddress
{
    public function __invoke(Request $request, Closure $next): UserAddress
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:4096',
            'phone' => 'required|phone:SA|unique:users,phone',
            'type' => ['required', new Enum(UserAddressType::class)],
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $city = City::published()->where('id', '=', $request->user()->city_id)->first();

        $isPointInsideRectangle = DistanceService::isPointInsideRectangle(
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ],
            $city->boundary,
        );

        if ($isPointInsideRectangle) {
            throw new LogicalException(
                'Address is far from the city boundary.',
                'The selected coordinates are outside the allowed city boundary.'
            );
        }

        $address = UserAddress::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'city_id' => $request->user()->city_id,
        ]);

        return $next($address);
    }
}
