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
            'carts.cartProducts.product.files',
            'carts.cartProducts.product.branchProducts',
        ])
            ->where('id', $orderId)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $order) {
            throw new LogicalException('Order not found', 'The order ID does not exist or does not belong to the current user.');
        }

        $cartList = $order->carts
            ->flatMap(fn ($cart) => $cart->cartProducts)
            ->map(fn ($cartProduct) => [
                'id' => $cartProduct->product->id,
                'title' => $cartProduct->product->title,
                'quantity' => $cartProduct->quantity,
            ])
            ->values();

        $products = $order->carts
            ->flatMap(fn ($cart) => $cart->cartProducts)
            ->map(function ($cartProduct): array {
                $product = $cartProduct->product;
                $branchProduct = $product->branchProducts->first();
                $image = $product->files->first()?->url;
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'image' => $image,
                    'max_limit' => $branchProduct
                        ? min($branchProduct->maximum_order_quantity, $branchProduct->quantity)
                        : null,
                    'min_limit' => $branchProduct?->minimum_order_quantity,
                    'price' => $branchProduct?->price,
                    'discount' => $branchProduct?->discount,
                    'discount_price' => $branchProduct?->discount_price,
                    'expired_at' => $branchProduct?->expires_at,
                ];
            })
            ->values();

        $details = [
            'order_number' => $order->order_number,
            'status' => $order->order_status,
            'cancelable' => $order->isCancelable(),
            'delivery_date' => $order->delivery_date?->toDateTimeString(),
            'address_phone_number' => $order->userAddress->phone,
            'cart_list' => $cartList,
            'products' => $products,
            'address_title' => $order->userAddress->title,
            'payment_method_title' => $order->paymentMethod->title,
            'discount_price' => $order->coupon_discount,
            'total_price' => $order->total_price,
            'created_at' => $order->created_at,
        ];

        return $next($details);
    }
}
