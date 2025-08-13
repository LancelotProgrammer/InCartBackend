<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function getPaymentMethods(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
