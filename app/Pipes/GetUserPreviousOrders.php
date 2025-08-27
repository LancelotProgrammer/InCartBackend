<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class GetUserPreviousOrders
{
    public function __invoke(Request $request, Closure $next)
    {
        $orders = $request->user()->orders()->latest()->simplePaginate();

        if (! $orders) {
            throw new LogicalException("No orders found", "User has no previous orders", 404);
        }

        return $next([
            $orders->items(),
            [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
            ]
        ]);
    }
}
