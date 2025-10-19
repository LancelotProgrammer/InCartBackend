<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\LogicalException;
use App\ExternalServices\FirebaseFCM;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use App\Notifications\DeliveryOrderNotification;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private static function makeOrderProcess(
        int $addressId,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): OrderProcess {
        return new OrderProcess((new OrderPayload)->fromRequest(
            now(),
            $addressId,
            $deliveryDate,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            User::findOrFail($customerId),
            BranchSettingsService::getServiceFee($branchId),
            BranchSettingsService::getTaxRate($branchId),
            BranchSettingsService::getMinDistance($branchId),
            BranchSettingsService::getMaxDistance($branchId),
            BranchSettingsService::getPricePerKilometer($branchId),
            BranchSettingsService::getMaxSubtotalPrice($branchId),
        ));
    }

    private static function processOrderCreation(OrderProcess $service, string $process): mixed
    {
        return DB::transaction(
            fn() =>
            $service
                ->generateOrderNumber()
                ->setOrderDate()
                ->calculateDestination()
                ->calculateCartPrice()
                ->createCart()
                // ->calculateCartWeight()
                ->calculateDeliveryPrice()
                ->handleGiftRedemption()
                ->handleCouponService()
                ->calculateFeesAndTotals()
                ->handlePaymentMethod()
                ->{$process}()
        );
    }

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
        $order = self::processOrderCreation(self::makeOrderProcess(
            $addressId,
            $deliveryDate,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            $customerId,
        ), 'createOrder');
        DatabaseManagerNotification::sendCreatedOrderNotification($order);
        return $order;
    }

    public static function userPay(int $orderId, string $orderPaymentToken, ?array $payload): void
    {
        $order = Order::where('id', '=', $orderId)
            ->where('payment_token', '=', $orderPaymentToken)
            ->first();

        DB::transaction(function () use ($order, $payload) {
            self::ensureValidPayment($order);
            BasePaymentGateway::make($order->paymentMethod->code)->pay($order, $payload);
        });
    }

    public static function userInvoice(int $orderId, int $userId): Response
    {
        $order = Order::where('id', $orderId)->where('customer_id', '=', $userId)->first();

        return InvoiceService::generateInvoice($order);
    }

    public static function userCreateBill(
        int $addressId,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): array {
        return self::processOrderCreation(self::makeOrderProcess(
            $addressId,
            $deliveryDate,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            $customerId,
        ), 'createOrderBill');
    }

    public static function userCancel(int $orderId, int $userId, ?string $reason): void
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

        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'order_status' => OrderStatus::CANCELLED,
                'delivery_status' => DeliveryStatus::NOT_DELIVERED,
                'cancel_reason' => $reason,
            ]);
            $order->save();
            if ($order->paymentMethod->code !== PaymentMethod::PAY_ON_DELIVERY_CODE) {
                self::ensureValidPayment($order, forRefund: true);
                BasePaymentGateway::make($order->paymentMethod->code)->refund($order);
            }
        });

        DatabaseManagerNotification::sendCancelledOrderNotification($order);
        CacheService::deletePendingOrderCount();
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
        self::processOrderCreation(self::makeOrderProcess(
            $addressId,
            $deliveryDate,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            $customerId,
        ), 'createOrder');
    }

    public static function managerInvoice(int $orderId): Response
    {
        $order = Order::where('id', $orderId)->first();

        return InvoiceService::generateInvoice($order);
    }

    public static function managerCancel(Order $order, array $data): void
    {
        DB::transaction(function () use ($order, $data) {
            $order->update([
                'order_status' => OrderStatus::CANCELLED,
                'delivery_status' => DeliveryStatus::NOT_DELIVERED,
                'manager_id' => auth()->user()->id,
                'cancel_reason' => $data['cancel_reason'],
            ]);
            $order->save();
            if ($order->paymentMethod->code !== PaymentMethod::PAY_ON_DELIVERY_CODE) {
                self::ensureValidPayment($order, forRefund: true);
                BasePaymentGateway::make($order->paymentMethod->code)->refund($order);
            }
        });

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} has been cancelled.")
            ->success()
            ->send();
    }

    public static function managerApprove(Order $order): void
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
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} is approved and currently processing.")
            ->success()
            ->send();
    }

    public static function managerForceApprove(Order $order): void
    {
        $order->update([
            'order_status' => OrderStatus::PROCESSING,
            'manager_id' => auth()->user()->id,
        ]);
        $order->save();

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} is approved and currently processing.")
            ->success()
            ->send();
    }

    public static function managerSelectDelivery(Order $order, array $data): void
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
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} is out for delivery.")
            ->info()
            ->send();
    }

    public static function managerFinish(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => OrderStatus::FINISHED,
                'delivery_status' => DeliveryStatus::DELIVERED,
                'payment_status' => PaymentStatus::PAID,
                'manager_id' => auth()->user()->id,
            ]);
            foreach ($order->carts->first()->cartProducts as $cartProduct) {
                $branchId = $order->branch_id;
                $productId = $cartProduct->product_id;
                BranchProduct::where('branch_id', $branchId)
                    ->where('product_id', $productId)
                    ->decrement('quantity', $cartProduct->quantity);
            }
            $order->save();
            if ((float)$order->discount_price == 0.0) {
                LoyaltyService::addPoints($order->customer, (int) $order->subtotal_price);
            }
        });

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} has been completed.")
            ->success()
            ->send();
    }

    public static function managerArchive(Order $order): void
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

    public static function getDeliveryUsers(int $branchId): Collection
    {
        return User::getUsersWhoCanBeAssignedToTakeOrders()->unblock()->where(function (Builder $query) use ($branchId) {
            $branch = Branch::find($branchId);
            $query->whereHas('branches', function (Builder $q) use ($branch) {
                $q->where('branch_id', '=', $branch->id);
            });
        })->pluck('name', 'id');
    }

    public static function getUsers(string $search, int $branchId): array
    {
        $cityId = Branch::find($branchId)->city_id;

        return User::query()
            ->unblock()
            ->where('city_id', '=', $cityId)
            ->where('role_id', '=', Role::where('code', '=', Role::ROLE_CUSTOMER_CODE)->first()->id)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
            ->limit(50)
            ->pluck('name', 'id')
            ->all();
    }

    public static function recalculateOrderTotals(Order $order): void
    {
        $subtotal = $order->cartProducts->sum(fn($item) => $item->price * $item->quantity);

        $taxAmount = PriceService::calculateTaxAmount(
            $subtotal,
            $order->service_fee,
            $order->tax_rate
        );

        $totalPrice = PriceService::calculateTotalPrice(
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

    private static function ensureValidPayment(Order $order, bool $forRefund = false): void
    {
        if (! $order->paymentMethod) {
            throw new LogicalException('Payment method not found');
        }

        if ($order->paymentMethod->published_at === null) {
            throw new LogicalException('Payment method is not active');
        }

        if ($order->paymentMethod->code === PaymentMethod::PAY_ON_DELIVERY_CODE) {
            $context = $forRefund ? 'Refund error' : 'Checkout error';
            throw new LogicalException($context, 'Payment method is pay-on-delivery');
        }

        if ($forRefund) {
            if ($order->payment_status === PaymentStatus::UNPAID) {
                throw new LogicalException('Refund error', 'Order is unpaid to be refunded');
            }
            if ($order->payment_status === PaymentStatus::REFUNDED) {
                throw new LogicalException('Refund error', 'Order is already refunded');
            }
        } else {
            if ($order->payment_status === PaymentStatus::PAID) {
                throw new LogicalException('Checkout error', 'Order is already paid');
            }
            if ($order->payment_status === PaymentStatus::REFUNDED) {
                throw new LogicalException('Checkout error', 'Order is refunded and cannot be paid');
            }
        }
    }
}
