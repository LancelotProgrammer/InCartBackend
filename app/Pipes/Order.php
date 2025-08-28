<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Order
{
    public function __invoke(Request $request, Closure $next)
    {
        $order = DB::transaction(function () use ($request) {
            OrderService::validateRequest($request);
            OrderService::setData($request);
            OrderService::generateOrderNumber();
            OrderService::setOrderDate();
            OrderService::calculateDestination();
            OrderService::calculateCartPrice();
            OrderService::createCart();
            OrderService::handleCoupon();
            // OrderService::calculateCouponItemCount();
            // OrderService::calculateCartWight();
            OrderService::calculateDeliveryPrice();
            OrderService::calculateCouponPriceDiscount();
            OrderService::calculateFeesAndTotals();
            OrderService::handlePaymentMethod();
            return OrderService::createOrder();
        });

        return $next([
            'id' => $order->id,
            'payment_token' => $order->payment_token ?? null,
        ]);
    }
}
