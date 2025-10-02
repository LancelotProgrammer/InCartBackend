<?php

namespace App\Pipes;

use App\Enums\UserAddressType;
use App\Exceptions\LogicalException;
use App\Models\City;
use App\Models\UserAddress;
use App\Services\DistanceService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserAddress
{
    public function __invoke(Request $request, Closure $next)
    {
        Rule::exists('user_addresses', 'id')->where('user_id', $request->user()->id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'required|string|max:20',
            'type' => ['required', new Enum(UserAddressType::class)],
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $city = City::where('id', '=', $request->user()->city_id)->first();

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

        $updated = UserAddress::where('id', '=', $request->route('id'))
            ->where('user_id', '=', $request->user()->id)
            ->where('city_id', '=', $request->user()->city_id)
            ->update($validated);

        if (! $updated) {
            throw new LogicalException('Failed to update address', 'The reasons for this error could be: the address does not exist, it does not belong to you, or it is not in your city.');
        }

        return $next([]);
    }
}
