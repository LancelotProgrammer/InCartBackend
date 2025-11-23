<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Order;
use App\Models\Scopes\BranchScope;
use Closure;
use Illuminate\Http\Request;

class GetOrderDetails
{
    public function __invoke(Request $request, Closure $next): array
    {
        $order = Order::withoutGlobalScope(BranchScope::class)
            ->with([
                'userAddress',
                'paymentMethod',
                'carts.cartProducts.product.files',
                'carts.cartProducts.product.branchProducts',
            ])
            ->where('id', $request->route('id'))
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
                $cartList->push([
                    'title' => $cartProduct->title,
                    'quantity' => $cartProduct->quantity,
                ]);
                $product = $cartProduct->product;
                if ($product && $product->isPublishedInBranches()) {
                    $products->push($product->toApiArray());
                }
            }
        }

        $details = [
            'order_number' => $order->order_number,
            'status' => $order->order_status,
            'cancelable' => $order->isCancelable(),
            'delivery_date' => $order->delivery_date->toDateTimeString(),
            'address_phone_number' => $order?->userAddress->phone,
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
