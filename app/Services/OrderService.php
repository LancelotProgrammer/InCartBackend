<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\DeliveryStatus;
use App\Enums\DeliveryScheduledType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\LogicalException;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\UserAddress;
use App\Support\OrderPayload;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
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
        $date = $this->payload->getDeliveryDate() !== null
            ? Carbon::parse($this->payload->getDeliveryDate())
            : $this->payload->getTime();

        $this->payload->setDate($date);

        return $this;
    }

    public function calculateDestination(): self
    {
        $branch = Branch::find($this->payload->getBranchId());

        if (!$branch) {
            throw new LogicalException('Branch is invalid', 'The selected branch does not exist.');
        }

        $address = UserAddress::where('id', '=', $this->payload->getAddressId())
            ->where('user_id', '=', $this->payload->getUser()->id)
            ->first();

        if (!$address) {
            throw new LogicalException('User address is invalid', 'The address does not exist or does not belong to you.');
        }

        $distance = DistanceService::haversineDistance(
            $branch->latitude,
            $branch->longitude,
            $address->latitude,
            $address->longitude
        );

        if (
            $distance < $this->payload->getMinDistance()
            || $distance > $this->payload->getMaxDistance()
        ) {
            throw new LogicalException('Destination is invalid', "The total destination is {$distance} km, which is outside the allowed range of {$this->payload->getMinDistance()} km to {$this->payload->getMaxDistance()} km.");
        }

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
            ])])
            ->get();

        foreach ($this->payload->getCartItems() as $item) {
            $product = $products->firstWhere('id', $item['id']);
            if (! $product || $product->branches->isEmpty()) {
                continue;
            }

            $branchProduct = $product->branches->first()->pivot;

            if ($item['quantity'] > $branchProduct->quantity) {
                throw new LogicalException("Quantity exceeds storage for product {$product->id}, quantity allowed is: {$branchProduct->quantity}");
            }

            if ($item['quantity'] > $branchProduct->maximum_order_quantity) {
                throw new LogicalException("Quantity exceeds maximum allowed for product {$product->id}, maximum order quantity allowed is: {$branchProduct->maximum_order_quantity}");
            }

            if ($item['quantity'] < $branchProduct->minimum_order_quantity) {
                throw new LogicalException("Quantity is below minimum allowed for product {$product->id}, minimum order quantity allowed is: {$branchProduct->minimum_order_quantity}");
            }

            $price = BranchProduct::getDiscountPrice($branchProduct);
            $subtotal += $price * $item['quantity'];
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
                    ->withPivot(['price', 'discount'])
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
            ];
        }

        CartProduct::insert($cartProducts);
        $this->payload->setCart($cart);

        return $this;
    }


    public function calculateCartWight(): self
    {
        /*
            future: use this code to calculate the cart wight
            weight_per_unit: should be added to products table. The idea is to have the weight of one unit of the product in grams (g)
            setTotalWeight / getTotalWeight: should be added to OrderPayload
        */
        throw new Exception('Not implemented yet');
        // $this->payload->setTotalWeight(0);
        // $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))
        //     ->with(['branches' => fn($query) => $query->where('branches.id', $this->payload->getBranchId())])
        //     ->get();
        // foreach ($this->payload->getCartItems() as $item) {
        //     $product = $products->firstWhere('id', $item['id']);
        //     if (!$product || $product->branches->isEmpty()) {
        //         continue;
        //     }
        //     $weight = $product->weight_per_unit * $item['quantity'];
        //     $this->payload->setTotalWeight(
        //         $this->payload->getTotalWeight() + $weight
        //     );
        // }
        // return $this;
    }

    public function calculateDeliveryPrice(): self
    {
        /*
            future: use this code to calculate the deliveryFee based on distance, wight or branch
        */
        // if (true) {
        //     $this->payload->setDeliveryFee(
        //         ($this->payload->getCartWeight() / 1000) * $this->payload->getPricePerKilogram()
        //     );
        // }
        // if (true) {
        //     $this->payload->setDeliveryFee($this->payload->getBranchDeliveryPrice());
        // }
        // if (true) {
        //     $this->payload->setDeliveryFee(
        //         $this->payload->getDistance() * $this->payload->getPricePerKilometer()
        //     );
        // }

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
        if (empty($this->payload->getCouponCode())) {
            return $this;
        }
        $coupon = Coupon::published()
            ->where('code', $this->payload->getCouponCode())
            ->first();
        if (! $coupon) {
            return $this;
        }

        // calculate discount
        $this->payload->setCoupon($coupon);
        $discount = $couponService->calculateDiscount($coupon);
        $this->payload->setCouponDiscount($discount);

        return $this;
    }

    public function calculateFeesAndTotals(): self
    {
        $taxAmount = ($this->payload->getServiceFee() + $this->payload->getSubtotal()) * $this->payload->getTaxRate();
        $totalPrice = $this->payload->getSubtotal()
            - $this->payload->getCouponDiscount()
            + $this->payload->getDeliveryFee()
            + $this->payload->getServiceFee()
            + $taxAmount;

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

        if ($paymentMethod->code !== 'pay-on-delivery') {
            $this->resolvePaymentGateway($paymentMethod);
        }

        return $this;
    }

    private function resolvePaymentGateway(PaymentMethod $paymentMethod): void
    {
        $class = BasePaymentGateway::$map[$paymentMethod->code] ?? null;

        if (! $class || ! in_array(PaymentGatewayInterface::class, class_implements($class))) {
            throw new InvalidArgumentException("Payment gateway not found for {$paymentMethod->code}");
        }

        $token = app($class)->generateToken();
        $this->payload->setPaymentToken($token);
    }

    public function createOrder(): Order
    {
        $order = Order::create([
            'order_number' => $this->payload->getOrderNumber(),
            'notes' => $this->payload->getNotes(),
            'payment_token' => $this->payload->getPaymentToken(),

            'delivery_scheduled_type' => $this->payload->getDeliveryDate() !== null ? DeliveryScheduledType::SCHEDULED->value : DeliveryScheduledType::IMMEDIATE->value,
            'delivery_date' => $this->payload->getDate(),

            'order_status' => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_status' => $this->payload->getDate() !== null ? DeliveryStatus::SCHEDULED->value : DeliveryStatus::NOT_SHIPPED->value,

            'subtotal_price' => $this->payload->getSubtotal(),
            'coupon_discount' => $this->payload->getCouponDiscount(),
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
        foreach ($this->payload->getCart()->cartProducts as $cartProduct) {
            $branchId = $this->payload->getBranchId();
            $productId = $cartProduct->product_id;

            BranchProduct::where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->decrement('quantity', $cartProduct->quantity);
        }

        Cache::deletePendingOrderCount();
    }

    public function createOrderBill(): array
    {
        return [
            'subtotal' => $this->payload->getSubtotal(),
            'discount' => $this->payload->getCouponDiscount(),
            'delivery_fee' => $this->payload->getDeliveryFee() + $this->payload->getServiceFee(),
            'tax' => $this->payload->getTaxAmount(),
            'total' => $this->payload->getTotalPrice(),
            'coupon' => $this->payload->getCoupon()?->code,
        ];
    }
}
