<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class AdvertisementController extends Controller
{
    public function getAdvertisements(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
