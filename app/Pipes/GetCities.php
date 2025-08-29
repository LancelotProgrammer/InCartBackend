<?php

namespace App\Pipes;

use App\Models\City;
use Closure;
use Illuminate\Http\Request;

class GetCities
{
    public function __invoke(Request $request, Closure $next)
    {
        return $next(City::all()->map(function ($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
            ];
        }));
    }
}
