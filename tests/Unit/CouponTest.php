<?php

use Illuminate\Support\Collection;
use Tests\Mocks\SimpleCoupon;
use Tests\Mocks\TestCoupon;
use Tests\Mocks\TestCouponUser;
use Tests\Mocks\TimeBasedCoupon;

beforeEach(function () {
    $this->user = (object) [
        'id' => 1,
    ];

    $this->categoryA = (object) ['id' => 10];
    $this->categoryB = (object) ['id' => 20];

    $this->product1 = (object) [
        'id' => 100,
        'category_id' => $this->categoryA->id,
    ];
    $this->product2 = (object) [
        'id' => 200,
        'category_id' => $this->categoryB->id,
    ];

    // Cart items as simple objects
    $this->cartItems = new Collection([
        (object) [
            'product_id' => $this->product1->id,
            'price' => 100,
            'quantity' => 2,
            'product' => $this->product1,
        ],
        (object) [
            'product_id' => $this->product2->id,
            'price' => 50,
            'quantity' => 3,
            'product' => $this->product2,
        ],
    ]);
});

it('applies fixed discount correctly', function () {
    $coupon = new TestCoupon;
    $coupon->type = 'fixed';
    $coupon->value = 50;
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(50);
});

it('applies percentage discount correctly', function () {
    $coupon = new TestCoupon;
    $coupon->type = 'percent';
    $coupon->value = 10;
    $coupon->max_discount = 40;
    $coupon->is_active = true;

    $discount = $coupon->apply(500, $this->user, $this->cartItems);
    expect($discount)->toBe(40);
});

it('applies product-based discount correctly', function () {
    $coupon = new TestCoupon;
    $coupon->type = 'product';
    $coupon->value = 20; // 20%
    $coupon->products = [$this->product1->id];
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(40); // 2 * 100 * 0.2 = 40
});

it('applies category-based discount correctly', function () {
    $coupon = new TestCoupon;
    $coupon->type = 'category';
    $coupon->value = 10; // 10%
    $coupon->categories = [$this->categoryB->id];
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(15); // 3 * 50 * 0.1 = 15
});

it('applies BOGO discount correctly', function () {
    $coupon = new TestCoupon;
    $coupon->type = 'bogo';
    $coupon->products = [$this->product2->id];
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(50); // floor(3/2) = 1 free * 50 = 50
});

beforeEach(function () {
    $this->user1 = (object) ['id' => 1];
    $this->user2 = (object) ['id' => 2];

    $this->cartItems = new Collection([
        (object) ['product_id' => 1, 'price' => 50, 'quantity' => 2], // total = 100
    ]);
});

it('applies user-specific coupon to allowed user', function () {
    $coupon = new TestCouponUser;
    $coupon->type = 'fixed';
    $coupon->value = 20;
    $coupon->user_id = 1; // Only user 1 can use it

    $discount = $coupon->apply(100, $this->user1, $this->cartItems);

    expect($discount)->toBe(20); // Allowed
});

it('does not apply user-specific coupon to other users', function () {
    $coupon = new TestCouponUser;
    $coupon->type = 'fixed';
    $coupon->value = 20;
    $coupon->user_id = 1; // Only user 1 can use it

    $discount = $coupon->apply(100, $this->user2, $this->cartItems);

    expect($discount)->toBe(0); // Not allowed
});

beforeEach(function () {
    $this->user = (object) ['id' => 1];
    $this->cartItems = new Collection([
        (object) ['product_id' => 1, 'price' => 50, 'quantity' => 2], // total 100
    ]);
});

it('applies coupon only during date range', function () {
    $coupon = new TimeBasedCoupon;
    $coupon->type = 'fixed';
    $coupon->value = 10;
    $coupon->start_date = new DateTimeImmutable('2025-08-01');
    $coupon->end_date = new DateTimeImmutable('2025-08-31');

    $now = new DateTimeImmutable('2025-08-10');
    $discount = $coupon->apply(100, $this->user, $this->cartItems, $now);
    expect($discount)->toBe(10);

    $tooEarly = new DateTimeImmutable('2025-07-31');
    $discount = $coupon->apply(100, $this->user, $this->cartItems, $tooEarly);
    expect($discount)->toBe(0);

    $tooLate = new DateTimeImmutable('2025-09-01');
    $discount = $coupon->apply(100, $this->user, $this->cartItems, $tooLate);
    expect($discount)->toBe(0);
});

it('applies coupon only on a fixed date', function () {
    $coupon = new TimeBasedCoupon;
    $coupon->type = 'fixed';
    $coupon->value = 15;
    $coupon->fixed_date = new DateTimeImmutable('2025-08-10');

    $matchDate = new DateTimeImmutable('2025-08-10');
    $wrongDate = new DateTimeImmutable('2025-08-11');

    expect($coupon->apply(100, $this->user, $this->cartItems, $matchDate))->toBe(15);
    expect($coupon->apply(100, $this->user, $this->cartItems, $wrongDate))->toBe(0);
});

it('applies coupon only on weekends', function () {
    $coupon = new TimeBasedCoupon;
    $coupon->type = 'fixed';
    $coupon->value = 20;
    $coupon->weekends_only = true;

    $saturday = new DateTimeImmutable('2025-08-09'); // Sat
    $sunday = new DateTimeImmutable('2025-08-10');   // Sun
    $monday = new DateTimeImmutable('2025-08-11');   // Mon

    expect($coupon->apply(100, $this->user, $this->cartItems, $saturday))->toBe(20);
    expect($coupon->apply(100, $this->user, $this->cartItems, $sunday))->toBe(20);
    expect($coupon->apply(100, $this->user, $this->cartItems, $monday))->toBe(0);
});

it('applies coupon only on specific weekday', function () {
    $coupon = new TimeBasedCoupon;
    $coupon->type = 'fixed';
    $coupon->value = 12;
    $coupon->only_weekday = 1; // Monday (0=Sun, 1=Mon...)

    $monday = new DateTimeImmutable('2025-08-11'); // Monday
    $tuesday = new DateTimeImmutable('2025-08-12');

    expect($coupon->apply(100, $this->user, $this->cartItems, $monday))->toBe(12);
    expect($coupon->apply(100, $this->user, $this->cartItems, $tuesday))->toBe(0);
});

beforeEach(function () {
    $this->user = (object) ['id' => 1];
    $this->cartItems = new Collection([
        (object) ['product_id' => 1, 'price' => 100, 'quantity' => 1],
    ]);

    // Basic order template
    $this->order = [
        'delivery_fee' => 10,
        'free_items' => [],
    ];
});

it('adds free item to order', function () {
    $coupon = new SimpleCoupon;
    $coupon->type = 'free_item';
    $coupon->free_product_id = 999;

    $discount = $coupon->apply(100, $this->user, $this->cartItems, $this->order);

    expect($discount)->toBe(0);
    expect($this->order['free_items'])->toContain(999);
});

it('sets delivery fee to zero for free shipping', function () {
    $coupon = new SimpleCoupon;
    $coupon->type = 'free_shipping';

    $discount = $coupon->apply(100, $this->user, $this->cartItems, $this->order);

    expect($discount)->toBe(0);
    expect($this->order['delivery_fee'])->toBe(0);
});
