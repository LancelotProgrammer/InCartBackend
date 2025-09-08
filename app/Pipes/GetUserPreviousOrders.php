<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class GetUserPreviousOrders
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        $orders = $request->user()->orders()->latest()->simplePaginate();

        return $next($orders->items());
    }
}
