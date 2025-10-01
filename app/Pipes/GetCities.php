<?php

namespace App\Pipes;

use App\Models\City;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetCities
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        return $next(City::all()->map(function (City $city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'boundary' => $city->boundary,
            ];
        }));
    }
}
