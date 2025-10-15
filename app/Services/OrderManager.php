<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\LogicalException;
use App\ExternalServices\FirebaseFCM;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\DeliveryOrderNotification;
use App\Support\OrderPayload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class OrderManager
{
    public static function userCreate(
        int $addressId,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): Order {
        $orderService = new OrderService((new OrderPayload)->fromRequest(
            now(),
            $addressId,
            $deliveryDate ?? null,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            User::where('id', '=', $customerId)->first(),
            SettingsService::getServiceFee(),
            SettingsService::getTaxRate(),
            SettingsService::getMinDistance(),
            SettingsService::getMaxDistance(),
            SettingsService::getPricePerKilometer(),
        ));
        $order = DB::transaction(function () use ($orderService) {
            return $orderService
                ->generateOrderNumber()
                ->setOrderDate()
                ->calculateDestination()
                ->calculateCartPrice()
                ->createCart()
                // ->calculateCartWight()
                ->calculateDeliveryPrice()
                ->handleGiftRedemption()
                ->handleCouponService()
                ->calculateFeesAndTotals()
                ->handlePaymentMethod()
                ->createOrder();
        });

        DatabaseManagerNotification::sendCreatedOrderNotification($order);

        return $order;
    }

    public static function managerCreate(
        int $addressId,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): void {
        $orderService = new OrderService((new OrderPayload)->fromRequest(
            now(),
            $addressId,
            $deliveryDate ?? null,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            User::where('id', '=', $customerId)->first(),
            SettingsService::getServiceFee(),
            SettingsService::getTaxRate(),
            SettingsService::getMinDistance(),
            SettingsService::getMaxDistance(),
            SettingsService::getPricePerKilometer(),
        ));
        DB::transaction(function () use ($orderService) {
            $orderService
                ->generateOrderNumber()
                ->setOrderDate()
                ->calculateDestination()
                ->calculateCartPrice()
                ->createCart()
                // ->calculateCartWight()
                ->calculateDeliveryPrice()
                ->handleGiftRedemption()
                ->handleCouponService()
                ->calculateFeesAndTotals()
                ->handlePaymentMethod()
                ->createOrder();
        });
    }

    public static function createBill(
        int $addressId,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): array {
        $orderService = new OrderService((new OrderPayload)->fromRequest(
            now(),
            $addressId,
            $deliveryDate ?? null,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            User::where('id', '=', $customerId)->first(),
            SettingsService::getServiceFee(),
            SettingsService::getTaxRate(),
            SettingsService::getMinDistance(),
            SettingsService::getMaxDistance(),
            SettingsService::getPricePerKilometer(),
        ));

        return DB::transaction(function () use ($orderService) {
            return $orderService
                ->generateOrderNumber()
                ->setOrderDate()
                ->calculateDestination()
                ->calculateCartPrice()
                ->createCart()
                // ->calculateCartWight()
                ->calculateDeliveryPrice()
                ->handleGiftRedemption()
                ->handleCouponService()
                ->calculateFeesAndTotals()
                ->handlePaymentMethod()
                ->createOrderBill();
        });
    }

    public static function recalculateOrderTotals(Order $order): void
    {
        $subtotal = $order->cartProducts->sum(fn ($item) => $item->price * $item->quantity);

        $taxAmount = PriceService::calculateTaxAmount(
            $subtotal,
            $order->service_fee,
            $order->tax_rate
        );

        $totalPrice = PriceService::calculateTotal(
            $subtotal,
            $order->discount_price,
            $order->delivery_fee,
            $order->service_fee,
            $order->tax_rate
        );

        $order->update([
            'subtotal_price' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice,
        ]);
    }

    public static function userCancel(int $orderId, int $userId, string $reason): void
    {
        $order = Order::where('id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (! $order) {
            throw new LogicalException('Order not found', 'The order ID does not exist or does not belong to the current user.');
        }
        if (! $order->isCancelable()) {
            throw new LogicalException('Order can not be canceled');
        }

        $order->update([
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => $order->payment_status === PaymentStatus::PAID ? PaymentStatus::REFUNDED : PaymentStatus::UNPAID,
            'delivery_status' => DeliveryStatus::NOT_DELIVERED,
            'cancel_reason' => $reason,
        ]);
        $order->save();

        DatabaseManagerNotification::sendCancelledOrderNotification($order);
        Cache::deletePendingOrderCount();
    }

    public static function managerCancel(Order $order, array $data): void
    {
        $order->update([
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => $order->payment_status === PaymentStatus::PAID ? PaymentStatus::REFUNDED : PaymentStatus::UNPAID,
            'delivery_status' => DeliveryStatus::NOT_DELIVERED,
            'manager_id' => auth()->user()->id,
            'cancel_reason' => $data['cancel_reason'],
        ]);
        $order->save();

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        Cache::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} has been cancelled.")
            ->success()
            ->send();
    }

    public static function approve(Order $order): void
    {
        if (
            PaymentMethod::where('id', '=', $order->payment_method_id)->value('code') !== PaymentMethod::PAY_ON_DELIVERY_CODE &&
            $order->payment_status === PaymentStatus::UNPAID
        ) {
            Notification::make()
                ->title("Order #{$order->order_number} cannot be approved.")
                ->body('Order is not checked out')
                ->warning()
                ->send();

            return;
        }
        if (! $order->delivery_date->isSameDay(now())) {
            Notification::make()
                ->title("Order #{$order->order_number} cannot be approved.")
                ->body('Order cannot be approved because it was not created today.')
                ->warning()
                ->send();

            return;
        }

        $order->update([
            'order_status' => OrderStatus::PROCESSING,
            'manager_id' => auth()->user()->id,
        ]);

        $order->save();
        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        Cache::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} is approved and currently processing.")
            ->success()
            ->send();
    }

    public static function selectDelivery(Order $order, array $data): void
    {
        $order->update([
            'order_status' => OrderStatus::DELIVERING,
            'delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY,
            'delivery_id' => $data['delivery_id'],
            'manager_id' => auth()->user()->id,
        ]);
        $order->save();

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        User::where('id', '=', $data['delivery_id'])->first()->notify(new DeliveryOrderNotification($order));
        Cache::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} is out for delivery.")
            ->info()
            ->send();
    }

    public static function finish(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => OrderStatus::FINISHED,
                'delivery_status' => DeliveryStatus::DELIVERED,
                'payment_status' => PaymentStatus::PAID,
                'manager_id' => auth()->user()->id,
            ]);
            $order->save();
            if ($order->discount_price === 0) {
                LoyaltyService::addPoints($order->customer, (int) $order->subtotal_price);
            }
        });

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        Cache::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} has been completed.")
            ->success()
            ->send();
    }

    public static function archive(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->archive();
            $order->delete();
        });

        Notification::make()
            ->title("Order #{$order->order_number} has been archived.")
            ->warning()
            ->send();
    }
}
