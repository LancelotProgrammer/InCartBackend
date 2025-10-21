<?php

namespace App\Pipes;

use App\Enums\DeliveryScheduledType;
use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreateOrder
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'delivery_scheduled_type' => ['required', Rule::enum(DeliveryScheduledType::class)],
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'coupon' => 'nullable|string',
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:products,id|distinct',
            'cart.*.quantity' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $order = OrderService::userCreate(
            $request->input('address_id'),
            $request->input('delivery_scheduled_type'),
            $request->input('delivery_date'),
            $request->input('payment_method_id'),
            $request->input('coupon'),
            $request->input('cart'),
            $request->input('notes'),
            $request->attributes->get('currentBranchId'),
            $request->user()->id,
        );

        return $next([
            'id' => $order->id,
            'payment_token' => $order->payment_token ?? null,
        ]);
    }
}
