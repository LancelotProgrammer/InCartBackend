<?php

namespace App\Services;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\LogicalException;
use App\Models\BranchProduct;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Coupon;
use App\Models\Gift;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\UserAddress;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrderProcess
{
    private OrderPayload $payload;

    public function __construct(OrderPayload $payload)
    {
        $this->payload = $payload;
        Log::channel('app_log')->info('Services(OrderProcess): OrderProcess initialized', [
            'user_id' => $this->payload->getUser()->id ?? null,
            'branch_id' => $this->payload->getBranchId(),
        ]);
    }

    public function generateOrderNumber(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Starting order number generation');

        do {
            $orderNumber = sprintf(
                'ORD-%s-%s',
                $this->payload->getTime()->format('YmdHis'),
                strtoupper(Str::random(6))
            );
        } while (Order::where('order_number', '=', $orderNumber)->exists());

        $this->payload->setOrderNumber($orderNumber);

        Log::channel('app_log')->info('Services(OrderProcess): Order number generated successfully', [
            'order_number' => $orderNumber
        ]);

        return $this;
    }

    public function setOrderDate(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Setting order date', [
            'delivery_scheduled_type' => $this->payload->getDeliveryScheduledType()->value
        ]);

        if ($this->payload->getDeliveryScheduledType() === DeliveryScheduledType::SCHEDULED) {
            $maxScheduledDays = $this->payload->getMaxScheduledDays();
            $maxDate = Carbon::now(config('app.timezone_display'))->addDays($maxScheduledDays);
            if (Carbon::parse($this->payload->getDeliveryDate(), config('app.timezone_display'))->greaterThan($maxDate)) {
                throw new LogicalException("The delivery date cannot be more than $maxScheduledDays days from now.");
            }
        }

        $date = $this->payload->getDeliveryScheduledType() === DeliveryScheduledType::SCHEDULED
            ? Carbon::parse($this->payload->getDeliveryDate(), config('app.timezone_display'))->setTimezone('UTC')
            : $this->payload->getTime();

        $this->payload->setDate($date);

        Log::channel('app_log')->info('Services(OrderProcess): Order date set successfully', [
            'order_date' => $date->toISOString()
        ]);

        return $this;
    }

    public function calculateDestination(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Calculating destination distance', [
            'branch_id' => $this->payload->getBranchId(),
            'address_id' => $this->payload->getAddressId()
        ]);

        $distance = DistanceService::validate(
            $this->payload->getBranchId(),
            $this->payload->getAddressId(),
        );

        $this->payload->setDistance($distance);

        Log::channel('app_log')->info('Services(OrderProcess): Destination distance calculated', [
            'distance' => $distance
        ]);

        return $this;
    }

    public function calculateCartPrice(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Starting cart price calculation', [
            'cart_items_count' => count($this->payload->getCartItems()),
            'branch_id' => $this->payload->getBranchId()
        ]);

        $subtotal = 0;

        $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))
            ->with(['branches' => fn ($query) => $query->where('branches.id', $this->payload->getBranchId())->withPivot([
                'price',
                'quantity',
                'discount',
                'maximum_order_quantity',
                'minimum_order_quantity',
                'published_at',
            ])])
            ->get();

        Log::channel('app_log')->info('Services(OrderProcess): Products fetched for cart calculation', [
            'products_count' => $products->count()
        ]);

        foreach ($this->payload->getCartItems() as $item) {
            $product = $products->firstWhere('id', $item['id']);
            if (! $product || $product->branches->isEmpty()) {
                Log::channel('app_log')->warning('Services(OrderProcess): Product not found or not available in branch', [
                    'product_id' => $item['id'],
                    'branch_id' => $this->payload->getBranchId()
                ]);
                continue;
            }

            $branchProduct = $product->branches->first()->pivot;
            $productTitle = $product->title;

            if ($branchProduct->published_at === null) {
                throw new LogicalException("{$productTitle} is not active.", "You are trying to get a product which is not active for the selected branch, Product id: {$product->id}");
            }

            if ($item['quantity'] > $branchProduct->quantity) {
                throw new LogicalException("Quantity exceeds storage for {$productTitle}", "quantity allowed is: {$branchProduct->quantity}, Product id: {$product->id}");
            }

            if ($item['quantity'] < $branchProduct->minimum_order_quantity) {
                throw new LogicalException("Quantity is below minimum allowed for {$productTitle}", "minimum order quantity allowed is: {$branchProduct->minimum_order_quantity}, Product id: {$product->id}");
            }

            if ($item['quantity'] > $branchProduct->maximum_order_quantity) {
                throw new LogicalException("Quantity exceeds maximum allowed for {$productTitle}", "maximum order quantity allowed is: {$branchProduct->maximum_order_quantity}, Product id: {$product->id}");
            }

            $price = BranchProduct::getDiscountPrice($branchProduct);
            $subtotal += $price * $item['quantity'];
        }

        $allowedSubtotal = $this->payload->getMaxSubtotalPrice();
        if ($subtotal >= $allowedSubtotal) {
            throw new LogicalException('Cart price is too high.', "Your cart price is {$subtotal} and the allowed amount is {$allowedSubtotal}");
        }

        $this->payload->setSubtotal($subtotal);

        Log::channel('app_log')->info('Services(OrderProcess): Cart price calculated successfully', [
            'subtotal' => $subtotal
        ]);

        return $this;
    }

    public function createCart(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Creating cart', [
            'order_number' => $this->payload->getOrderNumber()
        ]);

        $cart = Cart::create([
            'order_number' => $this->payload->getOrderNumber(),
        ]);

        $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))
            ->with([
                'branches' => fn ($query) => $query
                    ->where('branches.id', $this->payload->getBranchId())
                    ->withPivot(['price', 'discount']),
            ])
            ->get();

        $cartProducts = [];
        foreach ($this->payload->getCartItems() as $item) {
            $product = $products->firstWhere('id', $item['id']);
            $cartProducts[] = [
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'title' => $product->getTranslations('title'),
                'price' => BranchProduct::getDiscountPrice($product->branches->first()->pivot),
                'quantity' => $item['quantity'],
                'created_at' => $this->payload->getTime(),
                'updated_at' => $this->payload->getTime(),
            ];
        }

        foreach ($cartProducts as $data) {
            CartProduct::create($data);
        }

        $this->payload->setCart($cart);

        Log::channel('app_log')->info('Services(OrderProcess): Cart created successfully', [
            'cart_id' => $cart->id,
            'cart_products_count' => count($cartProducts)
        ]);

        return $this;
    }

    public function calculateCartWeight(): self
    {
        /*
            future: use this code to calculate the cart weight
            weight_per_unit: should be added to products table. The idea is to have the weight of one unit of the product in grams
            setTotalWeight / getTotalWeight / getAllowedWeight:  should be added to OrderPayload
        */
        Log::channel('app_log')->critical('Services(OrderProcess): Cart weight calculation not implemented');
        throw new Exception('Not implemented yet');
        // $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))->get();
        // $totalWeight = 0;
        // foreach ($this->payload->getCartItems() as $item) {
        //     $totalWeight += $products->firstWhere('id', $item['id'])->weight_per_unit * $item['quantity'];
        // }
        // $allowedWeight = $this->payload->getAllowedWeight();
        // if ($totalWeight >= $allowedWeight) {
        //     throw new LogicalException("Cart weight is too high.", "Your cart weight is {$totalWeight} and the allowed weight is {$allowedWeight}");
        // }
        // $this->payload->setTotalWeight($totalWeight);
        // return $this;
    }

    public function calculateDeliveryPrice(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Calculating delivery price', [
            'distance' => $this->payload->getDistance(),
            'price_per_kilometer' => $this->payload->getPricePerKilometer()
        ]);

        /*
            future: use this code to calculate the deliveryFee based on distance, weight or branch
            getCartWeight / getPricePerKilogram / getBranchDeliveryPrice : should be added to OrderPayload
        */
        // $fee = $this->payload->getDistance() * $this->payload->getPricePerKilometer() +
        //     $this->payload->getCartWeight() * $this->payload->getPricePerKilogram() +
        //     $this->payload->getBranchDeliveryPrice();

        $fee = $this->payload->getDistance() * $this->payload->getPricePerKilometer();
        $this->payload->setDeliveryFee($fee);

        Log::channel('app_log')->info('Services(OrderProcess): Delivery price calculated', [
            'delivery_fee' => $fee
        ]);

        return $this;
    }

    public function handleDiscountCode(): self
    {
        $code = $this->payload->getCode();

        if (empty($code)) {
            Log::channel('app_log')->info('Services(OrderProcess): No coupon code provided, skipping coupon/gift application');
            return $this;
        }

        Log::channel('app_log')->info("Services(OrderProcess): Checking discount code: {$code}");

        $coupon = Coupon::published()
            ->where('code', $code)
            ->first();

        $gift = Gift::published()
            ->where('code', $code)
            ->first();

        // none found → throw exception
        if (! $coupon && ! $gift) {
            throw new LogicalException('Code error', 'unavailable code.');
        }

        // If coupon exists
        if ($coupon) {
            Log::channel('app_log')->info("Services(OrderProcess): Coupon founded successfully", [
                'code' => $code,
                'coupon_id' => $coupon->id,
            ]);
            $this->payload->setIsDiscountFromCoupon(true);
            $this->payload->setCoupon($coupon);
            return $this;
        }

        // If gift exists
        if ($gift) {
            Log::channel('app_log')->info("Services(OrderProcess): Gift founded successfully", [
                'code' => $code,
                'gift_id' => $gift->id,
            ]);
            $this->payload->setIsDiscountFromGift(true);
            $this->payload->setGift($gift);
            return $this;
        }

        return $this;
    }

    public function handleCouponService(): self
    {
        if (! $this->payload->getIsDiscountFromCoupon()) {
            return $this;
        }

        Log::channel('app_log')->info('Services(OrderProcess): Handling coupon service', [
            'has_coupon_code' => !empty($this->payload->getCode())
        ]);

        // prepare coupon service dependencies
        $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))
            ->with('categories')
            ->get();
        $userAddress = UserAddress::find($this->payload->getAddressId());
        if (! $userAddress) {
            throw new LogicalException('User address is invalid', 'The address does not exist or does not belong to you.');
        }
        $couponService = new CouponService(
            $this->payload->getTime(),
            $userAddress,
            $this->payload->getCart(),
            $this->payload->getUser()->id,
            $this->payload->getBranchId(),
            $products->unique('id')->pluck('id')->toArray(),
            $products->flatMap(fn ($product) => $product->categories)->unique('id')->pluck('id')->toArray(),
        );

        // calculate discount
        $coupon = $this->payload->getCoupon();
        $discount = $couponService->calculateDiscount($coupon);
        $this->payload->setCoupon($coupon);
        $this->payload->setDiscount($discount);

        Log::channel('app_log')->info('Services(OrderProcess): Coupon applied successfully', [
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'discount_amount' => $discount
        ]);

        return $this;
    }

    public function handleGiftRedemption(): self
    {
        if (! $this->payload->getIsDiscountFromGift()) {
            return $this;
        }

        Log::channel('app_log')->info('Services(OrderProcess): Handling gift redemption', [
            'has_gift_code' => !empty($this->payload->getCode())
        ]);

        // calculate discount
        $gift = LoyaltyService::validateCode($this->payload->getUser(), $this->payload->getCode(), $this->payload->getSubtotal());
        $this->payload->setGift($gift);
        $this->payload->setDiscount($gift->discount);

        Log::channel('app_log')->info('Services(OrderProcess): Gift applied successfully', [
            'gift_id' => $gift->id,
            'gift_code' => $gift->code,
            'discount_amount' => $gift->discount
        ]);

        return $this;
    }

    public function calculateFeesAndTotals(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Calculating fees and totals', [
            'subtotal' => $this->payload->getSubtotal(),
            'service_fee' => $this->payload->getServiceFee(),
            'tax_rate' => $this->payload->getTaxRate(),
            'discount' => $this->payload->getDiscount(),
            'delivery_fee' => $this->payload->getDeliveryFee()
        ]);

        $subtotal = $this->payload->getSubtotal();
        $serviceFee = $this->payload->getServiceFee();
        $taxRate = $this->payload->getTaxRate();

        $taxAmount = PriceService::calculateTaxAmount($subtotal, $serviceFee, $taxRate);
        $totalPrice = PriceService::calculateTotalPrice(
            $subtotal,
            $this->payload->getDiscount(),
            $this->payload->getDeliveryFee(),
            $serviceFee,
            $taxRate
        );

        $this->payload->setTaxAmount($taxAmount);
        $this->payload->setTotalPrice($totalPrice);

        Log::channel('app_log')->info('Services(OrderProcess): Fees and totals calculated', [
            'tax_amount' => $taxAmount,
            'total_price' => $totalPrice
        ]);

        return $this;
    }

    public function handlePaymentMethod(): self
    {
        Log::channel('app_log')->info('Services(OrderProcess): Handling payment method', [
            'payment_method_id' => $this->payload->getPaymentMethodId()
        ]);

        $paymentMethod = PaymentMethod::published()->find($this->payload->getPaymentMethodId());

        if (! $paymentMethod) {
            throw new LogicalException('Payment method not found', 'The selected payment method does not exist.');
        }

        $this->payload->setPaymentMethod($paymentMethod);

        if ($paymentMethod->code !== PaymentMethod::PAY_ON_DELIVERY_CODE) {
            Log::channel('app_log')->info('Services(OrderProcess): Generating payment token for non-COD method', [
                'payment_method_code' => $paymentMethod->code
            ]);
            $this->payload->setPaymentToken(BasePaymentGateway::make($paymentMethod->code)->generateToken());
        } else {
            Log::channel('app_log')->info('Services(OrderProcess): COD payment method, no token required');
        }

        Log::channel('app_log')->info('Services(OrderProcess): Payment method handled successfully', [
            'payment_method_id' => $paymentMethod->id,
            'payment_method_code' => $paymentMethod->code
        ]);

        return $this;
    }

    public function createOrder(): Order
    {
        Log::channel('app_log')->info('Services(OrderProcess): Creating order in database', [
            'order_number' => $this->payload->getOrderNumber(),
            'user_id' => $this->payload->getUser()->id
        ]);

        $order = Order::create([
            'order_number' => $this->payload->getOrderNumber(),
            'notes' => $this->payload->getNotes(),
            'payment_token' => $this->payload->getPaymentToken(),

            'delivery_scheduled_type' => $this->payload->getDeliveryScheduledType()->value,
            'delivery_date' => $this->payload->getDate(),

            'user_address_title' => UserAddress::where('id', '=', $this->payload->getAddressId())->first()->title,

            'order_status' => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_status' => $this->payload->getDeliveryScheduledType() === DeliveryScheduledType::SCHEDULED ? DeliveryStatus::SCHEDULED->value : DeliveryStatus::NOT_DELIVERED->value,

            'subtotal_price' => $this->payload->getSubtotal(),
            'discount_price' => $this->payload->getDiscount(),
            'delivery_fee' => $this->payload->getDeliveryFee(),
            'service_fee' => $this->payload->getServiceFee(),
            'tax_amount' => $this->payload->getTaxAmount(),
            'total_price' => $this->payload->getTotalPrice(),

            'customer_id' => $this->payload->getUser()->id,
            'branch_id' => $this->payload->getBranchId(),
            'coupon_id' => $this->payload->getCoupon()?->id,
            'payment_method_id' => $this->payload->getPaymentMethod()->id,
            'user_address_id' => $this->payload->getAddressId(),
        ]);

        $this->payload->getCart()->update([
            'order_id' => $order->id,
        ]);

        $this->createOrderAfterHook();

        Log::channel('app_log')->info('Services(OrderProcess): Order created successfully', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total_price' => $order->total_price
        ]);

        return $order;
    }

    private function createOrderAfterHook(): void
    {
        Log::channel('app_log')->info('Services(OrderProcess): Running post-order creation hooks');

        if ($this->payload->getGift() !== null) {
            Log::channel('app_log')->info('Services(OrderProcess): Applying gift to user loyalty', [
                'user_id' => $this->payload->getUser()->id,
                'gift_id' => $this->payload->getGift()->id
            ]);
            LoyaltyService::applyGift($this->payload->getUser()->id, $this->payload->getGift()->id);
        }

        CacheService::deletePendingOrderCount();
        Log::channel('app_log')->info('Services(OrderProcess): Pending order count cache cleared');
    }

    public function createOrderBill(): array
    {
        Log::channel('app_log')->info('Services(OrderProcess): Generating order bill');

        $bill = [
            'subtotal' => round($this->payload->getSubtotal(), 2),
            'discount' => round($this->payload->getDiscount(), 2),
            'delivery_fee' => round(
                $this->payload->getDeliveryFee() + $this->payload->getServiceFee(),
                2
            ),
            'tax' => round($this->payload->getTaxAmount(), 2),
            'total' => round($this->payload->getTotalPrice(), 2),
            'coupon' => $this->payload->getCode(),
        ];

        Log::channel('app_log')->info('Services(OrderProcess): Order bill generated', $bill);

        return $bill;
    }
}
