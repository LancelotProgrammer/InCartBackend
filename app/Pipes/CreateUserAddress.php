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
            'description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'type' => ['required', new Enum(UserAddressType::class)],
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $city = City::where('id', '=', $request->user()->city_id)->first();

        $distance = DistanceService::haversineDistance(
            $validated['latitude'],
            $validated['longitude'],
            $city->latitude,
            $city->longitude
        );

        if ($distance > 100) {
            throw new LogicalException(
                'Address is too far from the city center.',
                'The address must be within 100 km of the city center. The total distance is ' . round($distance, 2) . ' km.'
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
