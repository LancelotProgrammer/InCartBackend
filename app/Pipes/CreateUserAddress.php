<?php

namespace App\Pipes;

use App\Enums\UserAddressType;
use App\Exceptions\LogicalException;
use App\Models\City;
use App\Models\UserAddress;
use App\Services\DistanceService;
use App\Services\SettingsService;
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

        $minDistance = SettingsService::getMinDistance();
        $maxDistance = SettingsService::getMaxDistance();
        if (
            $distance < $minDistance
            || $distance > $maxDistance
        ) {
            throw new LogicalException(
                'Address is too far from the city center.',
                "The total destination is {$distance} km, which is outside the allowed range of {$minDistance} km to {$maxDistance} km."
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
