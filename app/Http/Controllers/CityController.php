<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function getCities(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
