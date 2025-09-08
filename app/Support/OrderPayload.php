<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Carbon;

class OrderPayload
{
    private CouponService $couponService;

    private int $addressId;

    private ?string $deliveryDate = null;

    private int $paymentMethodId;

    private ?string $couponCode = null;

    private array $cartItems;

    private ?string $notes = null;

    private int $branchId;

    private User $user;

    private string $orderNumber;

    private float $distance;

    private ?Carbon $date = null;

    private ?Coupon $coupon = null;

    private Cart $cart;

    private PaymentMethod $paymentMethod;

    private ?string $paymentToken = null;

    private float $deliveryFee = 0;

    private float $subtotal = 0;

    private float $couponDiscount = 0;

    private float $taxAmount = 0;

    private float $totalPrice = 0;

    private float $serviceFee = 1; // TODO: get from settings

    private float $taxRate = 5; // TODO: get from settings

    private float $minDistance = 1; // TODO: get from settings

    private float $maxDistance = 1000; // TODO: get from settings

    private float $pricePerKilometer = 1; // TODO: get from settings

    // -------------------------------
    // Getters & Setters
    // -------------------------------

    public function getCouponService(): CouponService
    {
        return $this->couponService;
    }

    public function setCouponService(CouponService $couponService): void
    {
        $this->couponService = $couponService;
    }

    public function getAddressId(): int
    {
        return $this->addressId;
    }

    public function setAddressId(int $id): void
    {
        $this->addressId = $id;
    }

    public function getDeliveryDate(): ?string
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?string $date): void
    {
        $this->deliveryDate = $date;
    }

    public function getPaymentMethodId(): int
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(int $id): void
    {
        $this->paymentMethodId = $id;
    }

    public function getCouponCode(): ?string
    {
        return $this->couponCode;
    }

    public function setCouponCode(?string $code): void
    {
        $this->couponCode = $code;
    }

    public function getCartItems(): array
    {
        return $this->cartItems;
    }

    public function setCartItems(array $items): void
    {
        $this->cartItems = $items;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    public function getBranchId(): int
    {
        return $this->branchId;
    }

    public function setBranchId(int $id): void
    {
        $this->branchId = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getDistance(): float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): void
    {
        $this->distance = $distance;
    }

    public function getDate(): ?Carbon
    {
        return $this->date;
    }

    public function setDate(?Carbon $date): void
    {
        $this->date = $date;
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }

    public function setCoupon(?Coupon $coupon): void
    {
        $this->coupon = $coupon;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function setCart(Cart $cart): void
    {
        $this->cart = $cart;
    }

    public function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(PaymentMethod $method): void
    {
        $this->paymentMethod = $method;
    }

    public function getPaymentToken(): ?string
    {
        return $this->paymentToken;
    }

    public function setPaymentToken(?string $token): void
    {
        $this->paymentToken = $token;
    }

    public function getDeliveryFee(): float
    {
        return $this->deliveryFee;
    }

    public function setDeliveryFee(float $fee): void
    {
        $this->deliveryFee = $fee;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    public function setSubtotal(float $subtotal): void
    {
        $this->subtotal = $subtotal;
    }

    public function getCouponDiscount(): float
    {
        return $this->couponDiscount;
    }

    public function setCouponDiscount(float $discount): void
    {
        $this->couponDiscount = $discount;
    }

    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount(float $tax): void
    {
        $this->taxAmount = $tax;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(float $total): void
    {
        $this->totalPrice = $total;
    }

    public function getServiceFee(): float
    {
        return $this->serviceFee;
    }

    public function setServiceFee(float $fee): void
    {
        $this->serviceFee = $fee;
    }

    public function getTaxRate(): float
    {
        return $this->taxRate;
    }

    public function setTaxRate(float $rate): void
    {
        $this->taxRate = $rate;
    }

    public function getMinDistance(): float
    {
        return $this->minDistance;
    }

    public function setMinDistance(float $min): void
    {
        $this->minDistance = $min;
    }

    public function getMaxDistance(): float
    {
        return $this->maxDistance;
    }

    public function setMaxDistance(float $max): void
    {
        $this->maxDistance = $max;
    }

    public function getPricePerKilometer(): float
    {
        return $this->pricePerKilometer;
    }

    public function setPricePerKilometer(float $price): void
    {
        $this->pricePerKilometer = $price;
    }
}
