<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetPaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class PaymentMethodController extends Controller
{
    /**
     * @unauthenticated
     *
     * @header Authorization Bearer {YOUR_AUTH_KEY}
     *
     * @group Payment Method Actions
     */
    public function getPaymentMethods(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(Pipeline::send($request)
            ->through([
                GetPaymentMethods::class,
            ])
            ->thenReturn());
    }
}
