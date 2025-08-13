<?php

use Illuminate\Support\Collection;

beforeEach(function () {
    // Fake user
    $this->user = (object) [
        'id' => 1,
    ];

    // Fake products
    $this->cartItems = new Collection([
        (object) [
            'price' => 100,
            'quantity' => 2,
        ],
        (object) [
            'price' => 50,
            'quantity' => 3,
        ],
    ]);

    // Example coupon discount (e.g., $20 off)
    $this->couponDiscount = 20;

    // Delivery fee (fixed)
    $this->deliveryFee = 5;

    // Service fee (fixed)
    $this->serviceFee = 2;

    // Tax rate (5%)
    $this->taxRate = 0.05;
});

function calculateOrderPrice(Collection $cartItems, float $couponDiscount, float $deliveryFee, float $serviceFee, float $taxRate): array
{
    $subtotal = $cartItems->sum(fn ($item) => $item->price * $item->quantity);

    $detailPrice = $subtotal + $deliveryFee + $serviceFee;

    $taxAmount = $detailPrice * $taxRate;

    $totalPrice = $detailPrice + $taxAmount - $couponDiscount;

    return [
        'subtotal' => $subtotal,
        'coupon_discount' => $couponDiscount,
        'delivery_fee' => $deliveryFee,
        'service_fee' => $serviceFee,
        'tax_amount' => round($taxAmount, 2),
        'total_price' => round($totalPrice, 2),
    ];
}

it('calculates order price correctly', function () {
    $result = calculateOrderPrice($this->cartItems, $this->couponDiscount, $this->deliveryFee, $this->serviceFee, $this->taxRate);

    expect($result['subtotal'])->toBe(2 * 100 + 3 * 50); // 200 + 150 = 350
    expect($result['coupon_discount'])->toBe(20);
    expect($result['delivery_fee'])->toBe(5);
    expect($result['service_fee'])->toBe(2);
    expect($result['tax_amount'])->toBe(18.85); // (350 + 5 + 2)*0.05 = 18.85
    expect($result['total_price'])->toBe(355.85); // 350 + 5 + 2 + 18.85 - 20 = 355.85
});
