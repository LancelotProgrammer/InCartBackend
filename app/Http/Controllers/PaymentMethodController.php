<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use App\Pipes\GetPaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class PaymentMethodController extends Controller
{
    /**
     * @unauthenticated
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
