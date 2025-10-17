<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;

class CreateOrderCheckout
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'order_id' => 'required|int|exists:orders,id',
            'order_payment_token' => 'required|string|exists:orders,payment_token',
            'payload' => 'nullable|array',
        ]);

        OrderService::userPay(
            $request->input('order_id'),
            $request->input('order_payment_token'),
            $request->input('payload')
        );

        return $next([]);
    }
}
