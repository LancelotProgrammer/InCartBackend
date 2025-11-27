<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;

class CreateOrderCheckout
{
    public function __invoke(Request $request, Closure $next): ?array
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'payload' => 'required|array',
        ]);

        $response = OrderService::userPay(
            $request->input('order_id'),
            $request->input('payload')
        );

        return $next($response);
    }
}
