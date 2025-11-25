<?php

namespace App\Services;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\ForceApproveOrderType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\LogicalException;
use App\ExternalServices\FirebaseFCM;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\CartProduct;
use App\Models\ForceApproveOrder;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Notifications\DeliveryOrderNotification;
use App\Notifications\ForcedApprovedOrdersCountNotification;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    private static function makeOrderProcess(
        int $addressId,
        DeliveryScheduledType $deliveryScheduledType,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): OrderProcess {
        Log::channel('app_log')->info('Services(OrderService): Creating order process', [
            'address_id' => $addressId,
            'delivery_scheduled_type' => $deliveryScheduledType->value,
            'delivery_date' => $deliveryDate,
            'payment_method_id' => $paymentMethodId,
            'has_coupon' => !empty($coupon),
            'cart_items_count' => count($cart),
            'branch_id' => $branchId,
            'customer_id' => $customerId
        ]);

        return new OrderProcess((new OrderPayload)->fromRequest(
            now(),
            $addressId,
            $deliveryScheduledType,
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
            BranchSettingsService::getMinSubtotalPrice($branchId),
            BranchSettingsService::getMaxScheduledDays($branchId),
        ));
    }

    private static function processOrderCreation(OrderProcess $service, string $process): mixed
    {
        Log::channel('app_log')->info('Services(OrderService): Processing order creation', ['process' => $process]);

        return DB::transaction(
            fn() => $service
                ->generateOrderNumber()
                ->setOrderDate()
                ->calculateDestination()
                ->calculateCartPrice()
                ->createCart()
                // ->calculateCartWeight()
                ->calculateDeliveryPrice()
                ->handleDiscountCode()
                ->handleCouponService()
                ->handleGiftRedemption()
                ->calculateFeesAndTotals()
                ->handlePaymentMethod()
                ->{$process}()
        );
    }

    public static function userCreate(
        int $addressId,
        int $deliveryScheduledType,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): Order {
        Log::channel('app_log')->info('Services(OrderService): User creating order', [
            'customer_id' => $customerId,
            'branch_id' => $branchId,
            'cart_items_count' => count($cart)
        ]);

        $order = self::processOrderCreation(self::makeOrderProcess(
            $addressId,
            DeliveryScheduledType::from($deliveryScheduledType),
            $deliveryDate,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            $customerId,
        ), 'createOrder');
        
        DatabaseManagerNotification::sendCreatedOrderNotification($order);

        Log::channel('app_log')->info('Services(OrderService): User order created successfully', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer_id' => $customerId
        ]);

        return $order;
    }

    public static function userPay(int $orderId, string $orderPaymentToken, ?array $payload): void
    {
        Log::channel('app_log')->info('Services(OrderService): User processing payment', [
            'order_id' => $orderId,
            'has_payload' => !empty($payload)
        ]);

        $order = Order::where('id', '=', $orderId)
            ->where('payment_token', '=', $orderPaymentToken)
            ->first();

        if (!$order) {
            Log::channel('app_log')->warning('Services(OrderService): Order not found for payment', [
                'order_id' => $orderId,
                'payment_token' => $orderPaymentToken
            ]);
        }

        DB::transaction(function () use ($order, $payload) {
            self::ensureValidPayment($order);
            BasePaymentGateway::make($order->paymentMethod->code)->pay($order, $payload);
        });

        Log::channel('app_log')->info('Services(OrderService): User payment processed successfully', [
            'order_id' => $orderId,
            'payment_method' => $order->paymentMethod->code
        ]);
    }

    public static function userInvoice(int $orderId, int $userId): Response
    {
        Log::channel('app_log')->info('Services(OrderService): Generating user invoice', [
            'order_id' => $orderId,
            'user_id' => $userId
        ]);

        $order = Order::where('id', $orderId)->where('customer_id', '=', $userId)->first();

        if (!$order) {
            Log::channel('app_log')->warning('Services(OrderService): Order not found for invoice generation', [
                'order_id' => $orderId,
                'user_id' => $userId
            ]);
        }

        return InvoiceService::generateInvoice($order);
    }

    public static function userCreateBill(
        int $addressId,
        int $deliveryScheduledType,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): array {
        Log::channel('app_log')->info('Services(OrderService): User creating order bill', [
            'customer_id' => $customerId,
            'branch_id' => $branchId
        ]);

        $bill = self::processOrderCreation(self::makeOrderProcess(
            $addressId,
            DeliveryScheduledType::from($deliveryScheduledType),
            $deliveryDate,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            $customerId,
        ), 'createOrderBill');

        Log::channel('app_log')->info('Services(OrderService): Order bill created successfully', [
            'customer_id' => $customerId,
            'bill_total' => $bill['total'] ?? null
        ]);

        return $bill;
    }

    public static function userCancel(int $orderId, int $userId, ?string $reason): void
    {
        Log::channel('app_log')->info('Services(OrderService): User cancelling order', [
            'order_id' => $orderId,
            'user_id' => $userId,
            'has_reason' => !empty($reason)
        ]);

        $order = Order::where('id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (! $order) {
            throw new LogicalException('Order not found', 'The order ID does not exist or does not belong to the current user.');
        }
        if (! $order->isCancelable()) {
            throw new LogicalException('Order can not be canceled');
        }

        DB::transaction(function () use ($order, $reason, $userId) {
            $order->update([
                'order_status' => OrderStatus::CANCELLED,
                'delivery_status' => DeliveryStatus::NOT_DELIVERED,
                'cancel_reason' => $reason,
                'cancelled_by_id' => $userId,
            ]);
            $order->save();
            if (! $order->isPayOnDelivery()) {
                self::ensureValidPayment($order, forRefund: true);
                BasePaymentGateway::make($order->paymentMethod->code)->refund($order);
            }
        });

        DatabaseManagerNotification::sendCancelledOrderNotification($order);
        CacheService::deletePendingOrderCount();

        Log::channel('app_log')->info('Services(OrderService): User order cancelled successfully', [
            'order_id' => $orderId,
            'user_id' => $userId
        ]);
    }

    public static function managerCreate(
        int $addressId,
        DeliveryScheduledType $deliveryScheduledType,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $coupon,
        array $cart,
        ?string $notes,
        int $branchId,
        int $customerId,
    ): void {
        Log::channel('app_log')->info('Services(OrderService): Manager creating order', [
            'manager_id' => auth()->user()->id,
            'branch_id' => $branchId,
            'customer_id' => $customerId
        ]);

        self::processOrderCreation(self::makeOrderProcess(
            $addressId,
            $deliveryScheduledType,
            $deliveryDate,
            $paymentMethodId,
            $coupon,
            $cart,
            $notes,
            $branchId,
            $customerId,
        ), 'createOrder');

        Log::channel('app_log')->info('Services(OrderService): Manager order created successfully', [
            'manager_id' => auth()->user()->id,
            'branch_id' => $branchId
        ]);
    }

    public static function managerInvoice(int $orderId): Response
    {
        Log::channel('app_log')->info('Services(OrderService): Generating manager invoice', ['order_id' => $orderId]);

        $order = Order::where('id', $orderId)->first();

        if (!$order) {
            Log::channel('app_log')->warning('Services(OrderService): Order not found for manager invoice', ['order_id' => $orderId]);
        }

        return InvoiceService::generateInvoice($order);
    }

    public static function managerCancel(Order $order, array $data): void
    {
        Log::channel('app_log')->info('Services(OrderService): Manager cancelling order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'manager_id' => auth()->user()->id,
            'has_reason' => !empty($data['cancel_reason'])
        ]);

        DB::transaction(function () use ($order, $data) {
            $userId = auth()->user()->id;
            $order->update([
                'order_status' => OrderStatus::CANCELLED,
                'delivery_status' => DeliveryStatus::NOT_DELIVERED,
                'manager_id' => $userId,
                'cancel_reason' => $data['cancel_reason'],
                'cancelled_by_id' => $userId,
            ]);
            $order->save();
            if (! $order->isPayOnDelivery()) {
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

        Log::channel('app_log')->info('Services(OrderService): Manager order cancellation completed', [
            'order_id' => $order->id,
            'manager_id' => auth()->user()->id
        ]);
    }

    public static function managerApprove(Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Manager approving order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'manager_id' => auth()->user()->id
        ]);

        if (! $order->isPayOnDelivery() && $order->payment_status === PaymentStatus::UNPAID) {
            Log::channel('app_log')->warning('Services(OrderService): Order cannot be approved - not checked out', [
                'order_id' => $order->id,
                'payment_status' => $order->payment_status
            ]);
            Notification::make()
                ->title("Order #{$order->order_number} cannot be approved.")
                ->body('Order is not checked out')
                ->warning()
                ->send();

            return;
        }
        if (! $order->delivery_date->inApplicationTimezone()->isSameDay(now()->inApplicationTimezone())) {
            Log::channel('app_log')->warning('Services(OrderService): Order cannot be approved - wrong date', [
                'order_id' => $order->id,
                'delivery_date' => $order->delivery_date
            ]);
            Notification::make()
                ->title("Order #{$order->order_number} cannot be approved.")
                ->body('Order cannot be approved because it was not created today.')
                ->warning()
                ->send();

            return;
        }
        if (! SettingsService::isSystemOnline()) {
            Log::channel('app_log')->warning('Services(OrderService): Order cannot be approved - system offline', ['order_id' => $order->id]);
            Notification::make()
                ->title("Order #{$order->order_number} cannot be approved.")
                ->body('Order cannot be approved because the system is offline.')
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

        Log::channel('app_log')->info('Services(OrderService): Manager order approval completed', [
            'order_id' => $order->id,
            'manager_id' => auth()->user()->id
        ]);
    }

    public static function managerForceApprove(Order $order, array $data): void
    {
        Log::channel('app_log')->info('Services(OrderService): Manager force approving order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'manager_id' => auth()->user()->id,
            'has_reason' => !empty($data['reason'])
        ]);

        if (! SettingsService::isSystemOnline()) {
            Log::channel('app_log')->warning('Services(OrderService): Order cannot be force approved - system offline', ['order_id' => $order->id]);
            Notification::make()
                ->title("Order #{$order->order_number} cannot be approved.")
                ->body('Order cannot be force approved because the system is offline.')
                ->warning()
                ->send();

            return;
        }

        $forcedApprovedOrdersCountInThisWeek = ForceApproveOrder::where('created_at', '>=', now()->startOfWeek()->inApplicationTimezone())->count();
        if (! $forcedApprovedOrdersCountInThisWeek > BranchSettingsService::getForceApproveOrdersLimit($order->branch_id)) {
            Log::channel('app_log')->warning('Services(OrderService): Force approve limit exceeded', [
                'current_count' => $forcedApprovedOrdersCountInThisWeek,
                'limit' => BranchSettingsService::getForceApproveOrdersLimit($order->branch_id)
            ]);
            User::where('role_id', '=', Role::where('code', '=', Role::ROLE_SUPER_ADMIN_CODE)->first()->id)
                ->first()
                ->notify(new ForcedApprovedOrdersCountNotification($forcedApprovedOrdersCountInThisWeek));
        }

        $types = [];
        if (! $order->isPayOnDelivery() && $order->payment_status === PaymentStatus::UNPAID) {
            $types[] = ForceApproveOrderType::PASSING_CHECKOUT->value;
        }
        if (! $order->delivery_date->inApplicationTimezone()->isSameDay(now()->inApplicationTimezone())) {
            $types[] = ForceApproveOrderType::PASSING_DATE->value;
        }

        DB::transaction(function () use ($order, $data, $types) {
            $order->createForceApproveOrder($data['reason'], auth()->user()->id, $types);
            $order->update([
                'order_status' => OrderStatus::PROCESSING,
                'manager_id' => auth()->user()->id,
            ]);
            $order->save();
        });

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} is approved and currently processing.")
            ->success()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Manager force approval completed', [
            'order_id' => $order->id,
            'force_approve_types' => $types
        ]);
    }

    public static function managerSelectDelivery(Order $order, array $data): void
    {
        Log::channel('app_log')->info('Services(OrderService): Manager selecting delivery for order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'delivery_id' => $data['delivery_id'],
            'manager_id' => auth()->user()->id
        ]);

        $order->update([
            'order_status' => OrderStatus::DELIVERING,
            'delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY,
            'delivery_id' => $data['delivery_id'],
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

        Log::channel('app_log')->info('Services(OrderService): Delivery selection completed', [
            'order_id' => $order->id,
            'delivery_id' => $data['delivery_id']
        ]);
    }

    public static function deliveryFinish(Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Delivery finishing order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'delivery_id' => auth()->user()->id
        ]);

        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => OrderStatus::FINISHED,
                'delivery_status' => DeliveryStatus::DELIVERED,
            ]);
        });

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} has been completed.")
            ->success()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Delivery finish completed', ['order_id' => $order->id]);
    }

    public static function managerFinish(Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Manager finishing order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'manager_id' => auth()->user()->id
        ]);

        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => OrderStatus::FINISHED,
                'delivery_status' => DeliveryStatus::DELIVERED,
            ]);
        });

        FirebaseFCM::sendOrderStatusNotification($order);
        DatabaseUserNotification::sendOrderStatusNotification($order);
        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} has been completed.")
            ->success()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Manager finish completed', ['order_id' => $order->id]);
    }

    public static function managerClose(Order $order, array $data): void
    {
        Log::channel('app_log')->info('Services(OrderService): Manager closing order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'manager_id' => auth()->user()->id,
            'is_pay_on_delivery' => $order->isPayOnDelivery(),
            'has_payed_price' => isset($data['payed_price'])
        ]);

        DB::transaction(function () use ($order, $data) {
            $payedPrice = $order->isPayOnDelivery() ?
                $data['payed_price'] :
                $order->payed_price;
            $order->update([
                'order_status' => OrderStatus::CLOSED,
                'payment_status' => PaymentStatus::PAID,
                'payed_price' => $payedPrice,
            ]);
            foreach ($order->carts->first()->cartProducts as $cartProduct) {
                $branchId = $order->branch_id;
                $productId = $cartProduct->product_id;
                if ($productId) {
                    BranchProduct::where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->decrement('quantity', $cartProduct->quantity);
                }
            }
            $order->save();
            if ((float) $order->discount_price == 0.0) {
                LoyaltyService::addPoints($order->customer, (int) $order->subtotal_price);
            }
        });

        CacheService::deletePendingOrderCount();
        Notification::make()
            ->title("Order #{$order->order_number} has been closed.")
            ->success()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Manager close order completed', [
            'order_id' => $order->id,
            'total_price' => $order->total_price,
            'payed_price' => $order->payed_price
        ]);
    }

    public static function managerArchive(Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Manager archiving order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'manager_id' => auth()->user()->id
        ]);

        DB::transaction(function () use ($order) {
            $order->archive();
            $order->delete();
        });

        Notification::make()
            ->title("Order #{$order->order_number} has been archived.")
            ->warning()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Manager archive completed', ['order_id' => $order->id]);
    }

    public static function getPaymentMethods(?int $branchId = null): Collection
    {
        Log::channel('app_log')->info('Services(OrderService): Getting payment methods', ['branch_id' => $branchId]);

        $paymentMethod = PaymentMethod::published();
        if ($branchId) {
            $paymentMethod->where('branch_id', '=', $branchId);
        }

        $result = $paymentMethod->get();

        Log::channel('app_log')->info('Services(OrderService): Payment methods retrieved', [
            'branch_id' => $branchId,
            'count' => $result->count()
        ]);

        return $result;
    }

    public static function getDeliveryUsers(int $branchId): Collection
    {
        Log::channel('app_log')->info('Services(OrderService): Getting delivery users', ['branch_id' => $branchId]);

        $result = User::getUsersWhoCanBeAssignedToTakeOrders()->unblock()->where(function (Builder $query) use ($branchId) {
            $branch = Branch::find($branchId);
            $query->whereHas('branches', function (Builder $q) use ($branch) {
                $q->where('branch_id', '=', $branch->id);
            });
        })->pluck('name', 'id');

        Log::channel('app_log')->info('Services(OrderService): Delivery users retrieved', [
            'branch_id' => $branchId,
            'count' => $result->count()
        ]);

        return $result;
    }

    public static function getUsers(string $search, int $branchId): array
    {
        Log::channel('app_log')->info('Services(OrderService): Searching users', [
            'search_term' => $search,
            'branch_id' => $branchId
        ]);

        $cityId = Branch::find($branchId)->city_id;

        $result = User::query()
            ->unblock()
            ->where('city_id', '=', $cityId)
            ->where('role_id', '=', Role::where('code', '=', Role::ROLE_CUSTOMER_CODE)->first()->id)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
            ->limit(50)
            ->pluck('name', 'id')
            ->all();

        Log::channel('app_log')->info('Services(OrderService): Users search completed', [
            'search_term' => $search,
            'result_count' => count($result)
        ]);

        return $result;
    }

    public static function recalculateOrderTotals(Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Recalculating order totals', [
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);

        $subtotal = $order->cartProducts->sum(fn($item) => $item->price * $item->quantity);
        $taxRate = BranchSettingsService::getTaxRate($order->branch_id);

        $taxAmount = PriceService::calculateTaxAmount(
            $subtotal,
            $order->service_fee,
            $taxRate
        );

        $totalPrice = PriceService::calculateTotalPrice(
            $subtotal,
            $order->discount_price,
            $order->delivery_fee,
            $order->service_fee,
            $taxRate
        );

        $order->update([
            'subtotal_price' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice,
        ]);

        Log::channel('app_log')->info('Services(OrderService): Order totals recalculated', [
            'order_id' => $order->id,
            'new_subtotal' => $subtotal,
            'new_total' => $totalPrice
        ]);
    }

    private static function ensureValidPayment(Order $order, bool $forRefund = false): void
    {
        Log::channel('app_log')->info('Services(OrderService): Validating payment', [
            'order_id' => $order->id,
            'for_refund' => $forRefund,
            'payment_method_id' => $order->paymentMethod->id ?? null
        ]);

        if (! $order->paymentMethod) {
            throw new LogicalException('Payment method not found');
        }

        if ($order->paymentMethod->published_at === null) {
            throw new LogicalException('Payment method is not active');
        }

        if ($order->isPayOnDelivery()) {
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

        Log::channel('app_log')->info('Services(OrderService): Payment validation successful', ['order_id' => $order->id]);
    }

    public static function addProduct(array $data, Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Adding product to order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity']
        ]);

        $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $branchProduct) {
            Log::channel('app_log')->warning('Services(OrderService): Product not available for branch', [
                'product_id' => $data['product_id'],
                'branch_id' => $order->branch_id
            ]);
            Notification::make()
                ->title('Product is not available for branch.')
                ->warning()
                ->send();

            return;
        }

        $cartProduct = CartProduct::where('cart_id', $data['cart_id'])
            ->where('product_id', $data['product_id'])
            ->first();

        if ($cartProduct) {
            Log::channel('app_log')->warning('Services(OrderService): Product already in cart', [
                'product_id' => $data['product_id'],
                'cart_id' => $data['cart_id']
            ]);
            Notification::make()
                ->title('Product is already in the cart.')
                ->warning()
                ->send();

            return;
        }

        DB::transaction(function () use ($branchProduct, $data) {
            $product = Product::where('id', '=', $data['product_id'])->first();
            $cartProduct = new CartProduct([
                'cart_id' => $data['cart_id'],
                'product_id' => $data['product_id'],
                'price' => BranchProduct::getDiscountPrice($branchProduct),
                'quantity' => $data['quantity'],
            ]);
            $cartProduct->setTranslations('title', $product->getTranslations('title'));
            $cartProduct->save();
        });

        self::recalculateOrderTotals($order);

        Notification::make()
            ->title('Cart updated successfully.')
            ->success()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Product added to order successfully', [
            'order_id' => $order->id,
            'product_id' => $data['product_id']
        ]);
    }

    public static function editProduct(array $data, CartProduct $record, Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Editing product in order', [
            'order_id' => $order->id,
            'cart_product_id' => $record->id,
            'product_id' => $data['product_id'],
            'new_quantity' => $data['quantity']
        ]);

        $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
            ->where('product_id', $data['product_id'])
            ->first();

        if (! $branchProduct) {
            Log::channel('app_log')->warning('Services(OrderService): Product not available for branch during edit', [
                'product_id' => $data['product_id'],
                'branch_id' => $order->branch_id
            ]);
            Notification::make()
                ->title('Product is not available for branch.')
                ->warning()
                ->send();

            return;
        }

        $record->update([
            'price' => BranchProduct::getDiscountPrice($branchProduct),
            'quantity' => $data['quantity'],
        ]);

        self::recalculateOrderTotals($order);

        Notification::make()
            ->title('Cart updated successfully.')
            ->success()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Product edited successfully', [
            'order_id' => $order->id,
            'cart_product_id' => $record->id
        ]);
    }

    public static function removeProduct(CartProduct $record, Order $order): void
    {
        Log::channel('app_log')->info('Services(OrderService): Removing product from order', [
            'order_id' => $order->id,
            'cart_product_id' => $record->id,
            'product_id' => $record->product_id
        ]);

        $cart = $record->cart;

        if ($cart->cartProducts()->count() <= 1) {
            Log::channel('app_log')->warning('Services(OrderService): Cannot remove last product from cart', [
                'cart_id' => $cart->id,
                'remaining_products' => $cart->cartProducts()->count()
            ]);
            Notification::make()
                ->title('Warning')
                ->body('You cannot delete the last product in the cart.')
                ->warning()
                ->send();

            return;
        }

        $record->delete();

        self::recalculateOrderTotals($order);

        Notification::make()
            ->title('Cart updated successfully.')
            ->success()
            ->send();

        Log::channel('app_log')->info('Services(OrderService): Product removed successfully', [
            'order_id' => $order->id,
            'cart_product_id' => $record->id
        ]);
    }
}
