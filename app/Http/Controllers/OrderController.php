<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function order(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }

    public function checkout(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
