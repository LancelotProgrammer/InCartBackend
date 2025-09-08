<?php

namespace App\Pipes;

use App\Services\CheckoutService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class createOrderCheckout
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        DB::transaction(function () use ($request) {
            $checkoutService = new CheckoutService;
            $checkoutService->validateRequest($request)
                ->checkout($request);
        });

        return $next([]);
    }
}
