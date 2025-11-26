<?php

namespace App\Services;

use App\Enums\DeliveryScheduledType;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Gift;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Carbon;

class OrderPayload
{
    private Carbon $time;

    private int $addressId;

    private ?string $deliveryDate = null;

    private DeliveryScheduledType $deliveryScheduledType;

    private int $paymentMethodId;

    private ?string $couponCode = null;

    private array $cartItems;

    private ?string $notes = null;

    private int $branchId;

    private User $user;

    private string $orderNumber;

    private float $distance;

    private Carbon $date;

    private ?Coupon $coupon = null;

    private ?Gift $gift = null;

    private bool $isDiscountFromCoupon = false;
    
    private bool $isDiscountFromGift = false;

    private Cart $cart;

    private PaymentMethod $paymentMethod;

    private float $deliveryFee = 0;

    private float $subtotal = 0;

    private float $discount = 0;

    private float $taxAmount = 0;

    private float $totalPrice = 0;

    private float $serviceFee;

    private float $taxRate;

    private float $minDistance;

    private float $maxDistance;

    private float $pricePerKilometer;

    private float $maxSubtotalPrice;

    private float $minSubtotalPrice;

    private int $maxScheduledDays;

    // -------------------------------
    // Getters & Setters
    // -------------------------------

    public function getTime(): Carbon
    {
        return $this->time;
    }

    public function setTime(Carbon $time): void
    {
        $this->time = $time;
    }

    public function getAddressId(): int
    {
        return $this->addressId;
    }

    public function setAddressId(int $id): void
    {
        $this->addressId = $id;
    }

    public function getDeliveryScheduledType(): DeliveryScheduledType
    {
        return $this->deliveryScheduledType;
    }

    public function setDeliveryScheduledType(DeliveryScheduledType $deliveryScheduledType): void
    {
        $this->deliveryScheduledType = $deliveryScheduledType;
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

    public function getCode(): ?string
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

    public function getDate(): Carbon
    {
        return $this->date;
    }

    public function setDate(Carbon $date): void
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

    public function getGift(): ?Gift
    {
        return $this->gift;
    }

    public function setGift(?Gift $gift): void
    {
        $this->gift = $gift;
    }

    public function getIsDiscountFromCoupon(): bool
    {
        return $this->isDiscountFromCoupon;
    }

    public function setIsDiscountFromCoupon(bool $isDiscountFromCoupon): void
    {
        $this->isDiscountFromCoupon = $isDiscountFromCoupon;
    }

    public function getIsDiscountFromGift(): bool
    {
        return $this->isDiscountFromGift;
    }

    public function setIsDiscountFromGift(bool $isDiscountFromGift): void
    {
        $this->isDiscountFromGift = $isDiscountFromGift;
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

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
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

    public function getMaxSubtotalPrice(): float
    {
        return $this->maxSubtotalPrice;
    }

    public function setMaxSubtotalPrice(float $price): void
    {
        $this->maxSubtotalPrice = $price;
    }

    public function getMinSubtotalPrice(): float
    {
        return $this->minSubtotalPrice;
    }

    public function setMinSubtotalPrice(float $price): void
    {
        $this->minSubtotalPrice = $price;
    }

    public function getMaxScheduledDays(): int
    {
        return $this->maxScheduledDays;
    }

    public function setMaxScheduledDays(int $days): void
    {
        $this->maxScheduledDays = $days;
    }

    public function fromRequest(
        Carbon $time,
        int $addressId,
        DeliveryScheduledType $deliveryScheduledType,
        ?string $deliveryDate,
        int $paymentMethodId,
        ?string $couponCode,
        array $cartItems,
        ?string $notes,
        int $branchId,
        User $user,
        float $serviceFee,
        float $taxRate,
        float $minDistance,
        float $maxDistance,
        float $pricePerKilometer,
        float $maxSubtotalPrice,
        float $minSubtotalPrice,
        int $maxScheduledDays,
    ): self {
        $this->setTime($time);
        $this->setAddressId($addressId);
        $this->setDeliveryScheduledType($deliveryScheduledType);
        $this->setDeliveryDate($deliveryDate);
        $this->setPaymentMethodId($paymentMethodId);
        $this->setCouponCode($couponCode);
        $this->setCartItems($cartItems);
        $this->setNotes($notes);
        $this->setBranchId($branchId);
        $this->setUser($user);
        $this->setServiceFee($serviceFee);
        $this->setTaxRate($taxRate);
        $this->setMinDistance($minDistance);
        $this->setMaxDistance($maxDistance);
        $this->setPricePerKilometer($pricePerKilometer);
        $this->setMaxSubtotalPrice($maxSubtotalPrice);
        $this->setMinSubtotalPrice($minSubtotalPrice);
        $this->setMaxScheduledDays($maxScheduledDays);

        return $this;
    }
}
