<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CreateOrderInvoice
{
    public function __invoke(Request $request, Closure $next): Response
    {
        $request->merge(['id' => $request->route('id')]);

        $request->validate([
            'id' => 'integer|required|exists:orders,id',
        ]);

        return $next(OrderService::userInvoice($request->id, auth()->user()->id));
    }
}
