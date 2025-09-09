<?php

namespace App\Pipes;

use App\Services\CheckoutService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateOrderCheckout
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        $request->validate([
            'order_id' => 'required|int|exists:orders,id',
            'payment_method_id' => 'required|int|exists:payment_methods,id',
            'token' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            $checkoutService = new CheckoutService;
            $checkoutService->checkout($request);
        });

        return $next([]);
    }
}
