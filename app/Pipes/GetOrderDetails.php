<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Order;
use Closure;
use Illuminate\Http\Request;

class GetOrderDetails
{
    public function __invoke(Request $request, Closure $next): array
    {
        $orderId = $request->route('id');

        $order = Order::with([
            'userAddress',
            'paymentMethod',
            'carts.cartProducts.product',
        ])
            ->where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();


        if (! $order) {
            throw new LogicalException('Order not found', 'The order ID does not exist or does not belong to the current user.');
        }

        $cartList = $order->carts
            ->flatMap(fn($cart) => $cart->cartProducts)
            ->map(fn($cartProduct) => [
                'title'    => $cartProduct->product->title,
                'quantity' => $cartProduct->quantity,
            ])
            ->values();

        $details = [
            'order_number'         => $order->order_number,
            'status'               => $order->order_status,
            'delivery_date'        => $order->delivery_date?->toDateTimeString(),
            'address_phone_number' => $order->userAddress->phone,
            'cart_list'            => $cartList,
            'address_title'        => $order->userAddress->title,
            'payment_method_title' => $order->paymentMethod->title,
            'discount_price'       => $order->coupon_discount,
            'total_price'          => $order->total_price,
            'created_at'           => $order->created_at,
        ];

        return $next($details);
    }
}
