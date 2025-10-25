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

class OrderProcess
{
    private OrderPayload $payload;

    public function __construct(OrderPayload $payload)
    {
        $this->payload = $payload;
    }

    public function generateOrderNumber(): self
    {
        do {
            $orderNumber = sprintf(
                'ORD-%s-%s',
                $this->payload->getTime()->format('YmdHis'),
                strtoupper(Str::random(6))
            );
        } while (Order::where('order_number', '=', $orderNumber)->exists());

        $this->payload->setOrderNumber($orderNumber);

        return $this;
    }

    public function setOrderDate(): self
    {
        $date = $this->payload->getDeliveryScheduledType() === DeliveryScheduledType::SCHEDULED
            ? Carbon::parse($this->payload->getDeliveryDate())
            : $this->payload->getTime();

        $this->payload->setDate($date);

        return $this;
    }

    public function calculateDestination(): self
    {
        $distance = DistanceService::validate(
            $this->payload->getBranchId(),
            $this->payload->getAddressId(),
        );

        $this->payload->setDistance($distance);

        return $this;
    }

    public function calculateCartPrice(): self
    {
        $subtotal = 0;

        $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))
            ->with(['branches' => fn($query) => $query->where('branches.id', $this->payload->getBranchId())->withPivot([
                'price',
                'quantity',
                'discount',
                'maximum_order_quantity',
                'minimum_order_quantity',
                'published_at',
            ])])
            ->get();

        foreach ($this->payload->getCartItems() as $item) {
            $product = $products->firstWhere('id', $item['id']);
            if (! $product || $product->branches->isEmpty()) {
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
            throw new LogicalException("Cart price is too high.", "Your cart price is {$subtotal} and the allowed amount is {$allowedSubtotal}");
        }

        $this->payload->setSubtotal($subtotal);

        return $this;
    }

    public function createCart(): self
    {
        $cart = Cart::create([
            'order_number' => $this->payload->getOrderNumber(),
        ]);

        $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))
            ->with([
                'branches' => fn($query) => $query
                    ->where('branches.id', $this->payload->getBranchId())
                    ->withPivot(['price', 'discount']),
            ])
            ->get();

        $cartProducts = [];
        foreach ($this->payload->getCartItems() as $item) {
            $product = $products->firstWhere('id', $item['id']);
            $branchProduct = $product?->branches->first()?->pivot;
            $price = $branchProduct ? BranchProduct::getDiscountPrice($branchProduct) : 0;
            $cartProducts[] = [
                'cart_id' => $cart->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'created_at' => $this->payload->getTime(),
                'updated_at' => $this->payload->getTime(),
            ];
        }

        CartProduct::insert($cartProducts);
        $this->payload->setCart($cart);

        return $this;
    }

    public function calculateCartWeight(): self
    {
        /*
            future: use this code to calculate the cart weight
            weight_per_unit: should be added to products table. The idea is to have the weight of one unit of the product in grams
            setTotalWeight / getTotalWeight / getAllowedWeight:  should be added to OrderPayload
        */
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
        /*
            future: use this code to calculate the deliveryFee based on distance, weight or branch
            getCartWeight / getPricePerKilogram / getBranchDeliveryPrice : should be added to OrderPayload
        */
        // $fee = $this->payload->getDistance() * $this->payload->getPricePerKilometer() + 
        //     $this->payload->getCartWeight() * $this->payload->getPricePerKilogram() + 
        //     $this->payload->getBranchDeliveryPrice();

        $fee = $this->payload->getDistance() * $this->payload->getPricePerKilometer();
        $this->payload->setDeliveryFee($fee);

        return $this;
    }

    public function handleCouponService(): self
    {
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
            $products->flatMap(fn($product) => $product->categories)->unique('id')->pluck('id')->toArray(),
        );

        // apply coupon if exists
        if (empty($this->payload->getCode())) {
            return $this;
        }
        $coupon = Coupon::published()->where('code', $this->payload->getCode())->first();
        if (! $coupon) {
            return $this;
        }

        // calculate discount
        $discount = $couponService->calculateDiscount($coupon);
        $this->payload->setCoupon($coupon);
        $this->payload->setDiscount($discount);

        return $this;
    }

    public function handleGiftRedemption(): self
    {
        // apply gift if exists
        if (empty($this->payload->getCode())) {
            return $this;
        }
        $gift = Gift::published()->where('code', $this->payload->getCode())->first();
        if (! $gift) {
            return $this;
        }

        // calculate discount
        $gift = LoyaltyService::validateCode($this->payload->getUser(), $this->payload->getCode(), $this->payload->getSubtotal());
        $this->payload->setGift($gift);
        $this->payload->setDiscount($gift->discount);

        return $this;
    }

    public function calculateFeesAndTotals(): self
    {
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

        return $this;
    }

    public function handlePaymentMethod(): self
    {
        $paymentMethod = PaymentMethod::find($this->payload->getPaymentMethodId());

        if (! $paymentMethod) {
            throw new LogicalException('Payment method not found', 'The selected payment method does not exist.');
        }

        $this->payload->setPaymentMethod($paymentMethod);

        if ($paymentMethod->code !== PaymentMethod::PAY_ON_DELIVERY_CODE) {
            $this->payload->setPaymentToken(BasePaymentGateway::make($paymentMethod->code)->generateToken());
        }

        return $this;
    }

    public function createOrder(): Order
    {
        $order = Order::create([
            'order_number' => $this->payload->getOrderNumber(),
            'notes' => $this->payload->getNotes(),
            'payment_token' => $this->payload->getPaymentToken(),

            'delivery_scheduled_type' => $this->payload->getDeliveryScheduledType()->value,
            'delivery_date' => $this->payload->getDate(),

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

        return $order;
    }

    private function createOrderAfterHook(): void
    {
        if ($this->payload->getGift() !== null) {
            LoyaltyService::applyGift($this->payload->getUser()->id, $this->payload->getGift()->id);
        }

        CacheService::deletePendingOrderCount();
    }

    public function createOrderBill(): array
    {
        return [
            'subtotal' => $this->payload->getSubtotal(),
            'discount' => $this->payload->getDiscount(),
            'delivery_fee' => $this->payload->getDeliveryFee() + $this->payload->getServiceFee(),
            'tax' => $this->payload->getTaxAmount(),
            'total' => $this->payload->getTotalPrice(),
            'coupon' => $this->payload->getCoupon()?->code,
        ];
    }
}
