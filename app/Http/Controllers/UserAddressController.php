<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function getUserAddresses(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function addAddress(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function deleteAddress(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
