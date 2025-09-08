<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class createOrderBill
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        $orderBill = DB::transaction(function () use ($request) {
            $orderService = new OrderService;

            return $orderService->initializeOrderPayload()
                ->validateRequest($request)
                ->setData($request)
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
