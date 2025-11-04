<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class GetUserPreviousOrders
{
    public function __invoke(Request $request, Closure $next): array
    {
        $orders = $request->user()->customerOrders()->select([
            'id',
            'order_number',
            'total_price',
            'created_at',
            'order_status',
        ])->latest()->simplePaginate();

        return $next($orders->items());
    }
}
