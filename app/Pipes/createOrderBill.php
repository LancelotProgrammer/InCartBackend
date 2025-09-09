<?php

namespace App\Pipes;

use App\Services\OrderPayload;
use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateOrderBill
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'coupon' => 'nullable|string',
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $orderService = new OrderService((new OrderPayload())->fromRequest(
            $request->input('address_id'),
            $request->input('delivery_date'),
            $request->input('payment_method_id'),
            $request->input('coupon'),
            $request->input('cart'),
            $request->input('notes'),
            $request->attributes->get('currentBranchId'),
            $request->user(),
        ));

        $orderBill = DB::transaction(function () use ($request, $orderService) {

            return $orderService
                ->generateOrderNumber()
                ->setOrderDate()
                ->calculateDestination()
                ->calculateCartPrice()
                ->createCart()
                ->handleCouponService()
                // ->calculateCouponItemCount()
                // ->calculateCartWight()
                ->calculateDeliveryPrice()
                ->calculateCouponPriceDiscount()
                ->calculateFeesAndTotals()
                ->handlePaymentMethod()
                ->createOrderBill();
        });

        return $next($orderBill);
    }
}
