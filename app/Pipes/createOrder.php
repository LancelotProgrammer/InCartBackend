<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class createOrder
{
    public function __invoke(Request $request, Closure $next)
    {
        $order = DB::transaction(function () use ($request) {
            $orderService = new OrderService();
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
                ->createOrder();
        });

        return $next([
            'id' => $order->id,
            'payment_token' => $order->payment_token ?? null,
        ]);
    }
}
