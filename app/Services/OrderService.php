<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\CouponType;
use App\Enums\DeliveryStatus;
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
                now()->format('YmdHis'),
                strtoupper(Str::random(6))
            );
        } while (Order::where('order_number', '=', $orderNumber)->exists());

        $this->payload->setOrderNumber($orderNumber);

        return $this;
    }

    public function setOrderDate(): self
    {
        $date = $this->payload->getDeliveryDate()
            ? Carbon::parse($this->payload->getDeliveryDate())
            : now();

        $this->payload->setDate($date);

        return $this;
    }

    public function calculateDestination(): self
    {
        $branch = Branch::findOrFail($this->payload->getBranchId()); // TODO remove OrFail

        $address = UserAddress::where('id', '=', $this->payload->getAddressId())
            ->where('user_id', '=', $this->payload->getUser()->id)
            ->first();

        if (!$address) {
            throw new LogicalException('User address is invalid', 'The address does not exist or does not belong to you.');
        }

        $distance = $this->haversineDistance(
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

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
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

        $cartProducts = [];
        foreach ($this->payload->getCartItems() as $product) {
            $cartProducts[] = [
                'cart_id' => $cart->id,
                'product_id' => $product['id'],
                'quantity' => $product['quantity'],
            ];
        }

        CartProduct::insert($cartProducts);
        $this->payload->setCart($cart);

        return $this;
    }

    public function handleCouponService(): self
    {
        $products = Product::whereIn('id', collect($this->payload->getCartItems())->pluck('id'))
            ->with('categories')
            ->get();

        $couponService = new CouponService(
            Carbon::now(),
            UserAddress::findOrFail($this->payload->getAddressId()), // TODO remove OrFail
            $this->payload->getUser()->id,
            $this->payload->getBranchId(),
            $products->unique('id')->pluck('id')->toArray(),
            $products->flatMap(fn($product) => $product->categories)->unique('id')->pluck('id')->toArray(),
        );

        if (empty($this->payload->getCouponCode())) {
            return $this;
        }

        $coupon = Coupon::published()
            ->where('code', $this->payload->getCouponCode())
            ->first();

        if (! $coupon) {
            return $this;
        }

        $this->payload->setCoupon($coupon);
        $this->payload->setCouponService($couponService);

        return $this;
    }

    public function calculateCouponItemCount(): self
    {
        // future: use this function to apply coupon and manipulate cart items
        throw new Exception('Not implemented yet');
        // return $this;
    }

    public function calculateCartWight(): self
    {
        // future: use this code to calculate the cart wight
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
        // future: use this code to calculate the deliveryFee based on branch or wight
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

    public function calculateCouponPriceDiscount(): self
    {
        $coupon = $this->payload->getCoupon();
        if (! $coupon) {
            return $this;
        }

        $discount = match ($coupon->type) {
            CouponType::TIMED => $this->payload->getCouponService()->calculateTimeCoupon($coupon)
        };

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
        $paymentMethod = PaymentMethod::findOrFail($this->payload->getPaymentMethodId()); // TODO remove OrFail
        $this->payload->setPaymentMethod($paymentMethod);

        if ($paymentMethod->code !== 'pay-on-delivery') {
            $this->resolvePaymentGateway($paymentMethod);
        }

        return $this;
    }

    private function resolvePaymentGateway(PaymentMethod $paymentMethod): void
    {
        $map = [
            'apple-pay' => MoyasarPaymentGateway::class,
            'google-pay' => MoyasarPaymentGateway::class,
            'mada-pay' => MoyasarPaymentGateway::class,
            'stc-pay' => MoyasarPaymentGateway::class,
        ];

        $class = $map[$paymentMethod->code] ?? null;

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

            'user_id' => $this->payload->getUser()->id,
            'branch_id' => $this->payload->getBranchId(),
            'coupon_id' => $this->payload->getCoupon()?->id,
            'payment_method_id' => $this->payload->getPaymentMethod()->id,
            'user_address_id' => $this->payload->getAddressId(),
        ]);

        $this->payload->getCart()->update([
            'order_id' => $order->id,
        ]);

        return $order;
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
