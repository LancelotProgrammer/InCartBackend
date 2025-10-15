<?php

namespace App\Services;

use App\Models\BranchProduct;
use App\Models\CartProduct;
use App\Models\Order;
use Filament\Notifications\Notification;

class CartManager
{
    public static function addProduct(array $data, Order $order): void
    {
        $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $branchProduct) {
            Notification::make()
                ->title('Product is not available for branch.')
                ->warning()
                ->send();

            return;
        }

        CartProduct::create([
            'cart_id' => $data['cart_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'price' => BranchProduct::getDiscountPrice($branchProduct),
        ]);

        OrderManager::recalculateOrderTotals($order);
    }

    public static function editProduct(array $data, CartProduct $record, Order $order): void
    {
        $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $branchProduct) {
            Notification::make()
                ->title('Product is not available for branch.')
                ->warning()
                ->send();

            return;
        }

        $record->update([
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'price' => BranchProduct::getDiscountPrice($branchProduct),
        ]);

        OrderManager::recalculateOrderTotals($order);
    }

    public static function removeProduct(CartProduct $record, Order $order): void
    {
        $cart = $record->cart;
        if ($cart->cartProducts()->count() <= 1) {
            Notification::make()
                ->title('Warning')
                ->body('You cannot delete the last product in the cart.')
                ->warning()
                ->send();

            return;
        }
        $record->delete();
        OrderManager::recalculateOrderTotals($order);
    }
}
