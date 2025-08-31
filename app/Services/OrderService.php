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
use App\Models\User;
use App\Models\UserAddress;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    private static int $addressId;

    private static ?string $deliveryDate = null;

    private static int $paymentMethodId;

    private static ?string $couponCode = null;

    private static array $cartItems;

    private static ?string $notes = null;

    private static int $branchId;

    private static User $user;

    private static string $orderNumber;

    private static float $distance;

    private static ?Carbon $date = null;

    private static ?Coupon $coupon = null;

    private static CouponService $couponService;

    private static Cart $cart;

    private static PaymentMethod $paymentMethod;

    private static ?string $paymentToken = null;

    private static float $deliveryFee = 0;

    private static float $subtotal = 0;

    private static float $couponDiscount = 0;

    private static float $taxAmount = 0;

    private static float $totalPrice = 0;

    private static float $serviceFee = 1; // TODO: get from settings

    private static float $taxRate = 5; // TODO: get from settings

    private static float $minDistance = 1; // TODO: get from settings

    private static float $maxDistance = 1000; // TODO: get from settings

    private static float $pricePerKilometer = 1; // TODO: get from settings

    public static function validateRequest(Request $request): void
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'coupon' => 'nullable|string',
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);
    }

    public static function setData(Request $request): void
    {
        self::$addressId = $request->input('address_id');
        self::$deliveryDate = $request->input('delivery_date');
        self::$paymentMethodId = $request->input('payment_method_id');
        self::$couponCode = $request->input('coupon');
        self::$cartItems = $request->input('cart');
        self::$notes = $request->input('notes');
        self::$branchId = $request->attributes->get('currentBranchId');
        self::$user = $request->User();
    }

    public static function generateOrderNumber(): void
    {
        do {
            self::$orderNumber = sprintf(
                'ORD-%s-%s',
                now()->format('YmdHis'),
                strtoupper(Str::random(6))
            );
        } while (Order::where('order_number', '=', self::$orderNumber)->exists());
    }

    public static function setOrderDate(): void
    {
        self::$date = Carbon::parse(self::$deliveryDate) ?? now();
    }

    public static function calculateDestination(): void
    {
        $branch = Branch::find(self::$branchId);

        $address = UserAddress::where('id', '=', self::$addressId)->where('user_id', '=', self::$user->id);

        $distance = self::haversineDistance(
            $branch->latitude,
            $branch->longitude,
            $address->latitude,
            $address->longitude
        );

        if ($distance < self::$minDistance || $distance > self::$maxDistance) {
            throw new LogicalException('Distention is invalid');
        }

        self::$distance = $distance;
    }

    private static function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);

        return $earthRadius * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public static function calculateCartPrice(): void
    {
        $products = Product::whereIn('id', collect(self::$cartItems)->pluck('id'))
            ->with(['branches' => fn ($query) => $query->where('branches.id', self::$branchId)->withPivot([
                'price',
                'discount',
                'maximum_order_quantity',
                'minimum_order_quantity',
            ])])
            ->get();

        foreach (self::$cartItems as $item) {
            $product = $products->firstWhere('id', $item['id']);
            if (! $product || $product->branches->isEmpty()) {
                continue;
            }
            $branchProduct = $product->branches->first()->pivot;
            if ($item['quantity'] > $branchProduct->maximum_order_quantity) {
                throw new LogicalException("Quantity exceeds maximum allowed for product {$product->title}");
            }
            if ($item['quantity'] < $branchProduct->minimum_order_quantity) {
                throw new LogicalException("Quantity is below minimum allowed for product {$product->title}");
            }
            $price = BranchProduct::getDiscountPrice($branchProduct);
            self::$subtotal += $price * $item['quantity'];
        }
    }

    public static function createCart(): void
    {
        self::$cart = Cart::create([
            'title' => self::$orderNumber,
        ]);

        $cartProducts = [];
        foreach (self::$cartItems as $product) {
            $cartProducts[] = [
                'cart_id' => self::$cart->id,
                'product_id' => $product['quantity'],
                'count' => $product['quantity'],
            ];
        }

        CartProduct::insert($cartProducts);
    }

    public static function handleCoupon(): void
    {
        $products = Product::whereIn('id', collect(self::$cartItems)->pluck('id'))->with('categories')->get();
        self::$couponService = new CouponService(
            Carbon::now(),
            UserAddress::findOrFail(self::$addressId),
            self::$user->id,
            self::$branchId,
            $products->unique('id')->pluck('id')->toArray(),
            $products->flatMap(fn ($product) => $product->categories)->unique('id')->pluck('id')->toArray(),
        );

        if (empty(self::$couponCode)) {
            return;
        }
        $coupon = Coupon::published()->where('code', self::$couponCode)->first();
        if (! $coupon) {
            return;
        }

        self::$coupon = $coupon;
    }

    public static function calculateCouponItemCount(): void
    {
        // future: use this function to apply coupon and manipulate cart items
        throw new Exception('Not implemented yet');
    }

    public static function calculateCartWight(): void
    {
        // future: use this code to calculate the cart wight
        throw new Exception('Not implemented yet');
        // self::$totalWeight = 0;
        // $products = Product::whereIn('id', collect(self::$cartItems)->pluck('id'))
        //     ->with(['branches' => fn($query) => $query->where('branches.id', self::$branchId)])
        //     ->get();
        // foreach (self::$cartItems as $item) {
        //     $product = $products->firstWhere('id', $item['id']);
        //     if (!$product || $product->branches->isEmpty()) {
        //         continue;
        //     }
        //     $weight = $product->weight_per_unit * $item['quantity'];
        //     self::$totalWeight += $weight;
        // }
    }

    public static function calculateDeliveryPrice(): void
    {
        // future: use this code to calculate the deliveryFee based on branch or wight
        // if (true) {
        //     self::$deliveryFee = (self::$cartWight / 1000) * self::$pricePerKilogram;
        // }
        // if (true) {
        //     self::$deliveryFee = self::$branchDeliveryPrice;
        // }
        // if (true) {
        //     self::$deliveryFee = self::$distance * self::$pricePerKilometer;
        // }

        self::$deliveryFee = self::$distance * self::$pricePerKilometer;
    }

    public static function calculateCouponPriceDiscount(): void
    {
        if (self::$coupon === null) {
            return;
        }
        if (self::$coupon->type === CouponType::TIMED) {
            self::$couponDiscount = self::$couponService::calculateTimeCoupon(self::$coupon);
        }
    }

    public static function calculateFeesAndTotals(): void
    {
        self::$taxAmount = (self::$serviceFee + self::$subtotal) * self::$taxRate;
        self::$totalPrice = self::$subtotal - self::$couponDiscount + self::$deliveryFee + self::$serviceFee + self::$taxAmount;
    }

    public static function handlePaymentMethod(): void
    {
        self::$paymentMethod = PaymentMethod::findOrFail(self::$paymentMethodId);
        if (self::$paymentMethod->code !== 'pay-on-delivery') {
            self::resolvePaymentGateway(self::$paymentMethod);
        }
    }

    private static function resolvePaymentGateway(PaymentMethod $paymentMethod): void
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

        self::$paymentToken = app($class)->generateToken();
    }

    public static function createOrder(): Order
    {
        return Order::create([
            'order_number' => self::$orderNumber,
            'notes' => self::$notes,
            'payment_token' => self::$paymentToken,
            'delivery_date' => self::$date,

            'order_status' => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_status' => self::$date !== null ? DeliveryStatus::SCHEDULED->value : DeliveryStatus::NOT_SHIPPED->value,

            'subtotal_price' => self::$subtotal,
            'coupon_discount' => self::$couponDiscount,
            'delivery_fee' => self::$deliveryFee,
            'service_fee' => self::$serviceFee,
            'tax_amount' => self::$taxAmount,
            'total_price' => self::$totalPrice,

            'user_id' => self::$user->id,
            'branch_id' => self::$branchId,
            'cart_id' => self::$cart->id,
            'coupon_id' => self::$coupon?->id,
            'payment_method_id' => self::$paymentMethod->id,
            'user_address_id' => self::$addressId,
        ]);
    }
}
