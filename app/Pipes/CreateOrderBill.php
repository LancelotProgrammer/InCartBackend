<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;

class CreateOrderBill
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'coupon' => 'nullable|string',
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:products,id|distinct',
            'cart.*.quantity' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $orderBill = OrderService::userCreateBill(
            $request->input('address_id'),
            $request->input('delivery_date'),
            $request->input('payment_method_id'),
            $request->input('coupon'),
            $request->input('cart'),
            $request->input('notes'),
            $request->attributes->get('currentBranchId'),
            $request->user()->id,
        );

        return $next($orderBill);
    }
}
