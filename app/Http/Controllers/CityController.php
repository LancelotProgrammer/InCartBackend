<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetCities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class CityController extends Controller
{
    /**
     * @unauthenticated
     *
     * @group Cities Actions
     */
    public function getCities(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetCities::class,
            ])
            ->thenReturn());
    }
}
