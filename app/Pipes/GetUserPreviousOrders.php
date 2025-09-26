<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class GetUserPreviousOrders
{
    public function __invoke(Request $request, Closure $next): array
    {
        $orders = $request->user()->customerOrders()->select(['id', 'order_number', 'total_price', 'created_at'])->latest()->simplePaginate();

        return $next($orders->items());
    }
}
