<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Order;
use App\Models\Product;
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
            'carts.cartProducts.product.files',
            'carts.cartProducts.product.branchProducts',
        ])
            ->where('id', $orderId)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $order) {
            throw new LogicalException(
                'Order not found',
                'The order ID does not exist or does not belong to the current user.'
            );
        }

        $cartList = collect();
        $products = collect();

        foreach ($order->carts as $cart) {
            foreach ($cart->cartProducts as $cartProduct) {
                $product = $cartProduct->product;
                $branchProduct = $product->branchProducts->first();
                $image = $product->files->first()->url;
                
                $cartList->push([
                    'id' => $product->id,
                    'title' => $cartProduct->title,
                    'quantity' => $cartProduct->quantity,
                ]);

                if (!$product) {
                    continue;
                }

                $products->filter(function (Product $product) {
                    return $product->branchProducts->whereNotNull('published_at')->isNotEmpty();
                })->values()->push([
                    'id' => $product->id,
                    'title' => $product->title,
                    'image' => $image,
                    'max_limit' => $branchProduct->maximum_order_quantity,
                    'min_limit' => $branchProduct->minimum_order_quantity,
                    'price' => $branchProduct->price,
                    'discount' => $branchProduct->discount,
                    'discount_price' => $branchProduct->discount_price,
                    'expired_at' => $branchProduct->expires_at,
                ]);
            }
        }

        $details = [
            'order_number' => $order->order_number,
            'status' => $order->order_status,
            'cancelable' => $order->isCancelable(),
            'delivery_date' => $order->delivery_date->toDateTimeString(),
            'address_phone_number' => $order?->userAddress->phone ?? null,
            'cart_list' => $cartList->values(),
            'products' => $products->values(),
            'address_title' => $order->user_address_title,
            'payment_method_title' => $order->paymentMethod->title,
            'discount_price' => $order->discount_price,
            'total_price' => $order->total_price,
            'created_at' => $order->created_at,
        ];

        return $next($details);
    }
}
