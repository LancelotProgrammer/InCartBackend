<?php

namespace App\Pipes;

use App\Services\CheckoutService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Checkout
{
    public function __invoke(Request $request, Closure $next)
    {
        DB::transaction(function () use ($request) {
            CheckoutService::validateRequest($request);
            CheckoutService::checkout($request);
        });

        return $next([]);
    }
}
