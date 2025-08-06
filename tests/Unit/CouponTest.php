<?php

use Illuminate\Support\Collection;

beforeEach(function () {
    $this->user = (object)[
        'id' => 1,
    ];

    $this->categoryA = (object)['id' => 10];
    $this->categoryB = (object)['id' => 20];

    $this->product1 = (object)[
        'id' => 100,
        'category_id' => $this->categoryA->id,
    ];
    $this->product2 = (object)[
        'id' => 200,
        'category_id' => $this->categoryB->id,
    ];

    // Cart items as simple objects
    $this->cartItems = new Collection([
        (object)[
            'product_id' => $this->product1->id,
            'price' => 100,
            'quantity' => 2,
            'product' => $this->product1,
        ],
        (object)[
            'product_id' => $this->product2->id,
            'price' => 50,
            'quantity' => 3,
            'product' => $this->product2,
        ],
    ]);
});

// Create a simple Coupon class for testing
class TestCoupon
{
    public $type;
    public $value;
    public $max_discount;
    public $is_active = true;
    public $user_id = null;
    public $min_cart_value = null;
    public $products = [];
    public $categories = [];
    public $start_date = null;
    public $end_date = null;

    public function apply(float $subtotal, $user, Collection $cartItems): float
    {
        if (!$this->is_active) {
            return 0;
        }

        $now = new DateTimeImmutable();
        if ($this->start_date && $now < $this->start_date) {
            return 0;
        }
        if ($this->end_date && $now > $this->end_date) {
            return 0;
        }

        if ($this->user_id && $this->user_id !== $user->id) {
            return 0;
        }

        if ($this->min_cart_value && $subtotal < $this->min_cart_value) {
            return 0;
        }

        switch ($this->type) {
            case 'fixed':
                return min($this->value, $subtotal);

            case 'percent':
                $discount = $subtotal * ($this->value / 100);
                return min($discount, $this->max_discount ?? $discount);

            case 'product':
                return $this->calculateProductDiscount($cartItems);

            case 'category':
                return $this->calculateCategoryDiscount($cartItems);

            case 'bogo':
                return $this->calculateBOGO($cartItems);

            case 'free_shipping':
            case 'free_item':
                return 0;

            default:
                return 0;
        }
    }

    protected function calculateProductDiscount(Collection $cartItems): float
    {
        $eligibleItems = $cartItems->filter(fn($item) =>
            in_array($item->product_id, $this->products)
        );

        $discountBase = $eligibleItems->sum(fn($item) =>
            $item->price * $item->quantity
        );

        return $discountBase * ($this->value / 100);
    }

    protected function calculateCategoryDiscount(Collection $cartItems): float
    {
        $eligibleItems = $cartItems->filter(fn($item) =>
            in_array($item->product->category_id, $this->categories)
        );

        $discountBase = $eligibleItems->sum(fn($item) =>
            $item->price * $item->quantity
        );

        return $discountBase * ($this->value / 100);
    }

    protected function calculateBOGO(Collection $cartItems): float
    {
        $eligibleItems = $cartItems->filter(fn($item) =>
            in_array($item->product_id, $this->products)
        );

        $discount = 0;

        foreach ($eligibleItems as $item) {
            $freeQty = intdiv($item->quantity, 2);
            $discount += $freeQty * $item->price;
        }

        return $discount;
    }
}

it('applies fixed discount correctly', function () {
    $coupon = new TestCoupon();
    $coupon->type = 'fixed';
    $coupon->value = 50;
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(50);
});

it('applies percentage discount correctly', function () {
    $coupon = new TestCoupon();
    $coupon->type = 'percent';
    $coupon->value = 10;
    $coupon->max_discount = 40;
    $coupon->is_active = true;

    $discount = $coupon->apply(500, $this->user, $this->cartItems);
    expect($discount)->toBe(40);
});

it('applies product-based discount correctly', function () {
    $coupon = new TestCoupon();
    $coupon->type = 'product';
    $coupon->value = 20; // 20%
    $coupon->products = [$this->product1->id];
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(40); // 2 * 100 * 0.2 = 40
});

it('applies category-based discount correctly', function () {
    $coupon = new TestCoupon();
    $coupon->type = 'category';
    $coupon->value = 10; // 10%
    $coupon->categories = [$this->categoryB->id];
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(15); // 3 * 50 * 0.1 = 15
});

it('applies BOGO discount correctly', function () {
    $coupon = new TestCoupon();
    $coupon->type = 'bogo';
    $coupon->products = [$this->product2->id];
    $coupon->is_active = true;

    $discount = $coupon->apply(300, $this->user, $this->cartItems);
    expect($discount)->toBe(50); // floor(3/2) = 1 free * 50 = 50
});
















class TestCouponUser
{
    public $type;
    public $value;
    public $is_active = true;
    public $user_id = null; // Specific user allowed

    public function apply(float $subtotal, object $user, Collection $cartItems): float
    {
        if (!$this->is_active) {
            return 0;
        }

        if ($this->user_id !== null && $this->user_id !== $user->id) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        return 0;
    }
}

beforeEach(function () {
    $this->user1 = (object)['id' => 1];
    $this->user2 = (object)['id' => 2];

    $this->cartItems = new Collection([
        (object)['product_id' => 1, 'price' => 50, 'quantity' => 2], // total = 100
    ]);
});

it('applies user-specific coupon to allowed user', function () {
    $coupon = new TestCouponUser();
    $coupon->type = 'fixed';
    $coupon->value = 20;
    $coupon->user_id = 1; // Only user 1 can use it

    $discount = $coupon->apply(100, $this->user1, $this->cartItems);

    expect($discount)->toBe(20); // Allowed
});

it('does not apply user-specific coupon to other users', function () {
    $coupon = new TestCouponUser();
    $coupon->type = 'fixed';
    $coupon->value = 20;
    $coupon->user_id = 1; // Only user 1 can use it

    $discount = $coupon->apply(100, $this->user2, $this->cartItems);

    expect($discount)->toBe(0); // Not allowed
});









class TimeBasedCoupon
{
    public $type;
    public $value;
    public $is_active = true;

    public ?DateTimeImmutable $start_date = null;
    public ?DateTimeImmutable $end_date = null;
    public ?DateTimeImmutable $fixed_date = null;
    public bool $weekends_only = false;
    public ?int $only_weekday = null; // 0 (Sun) to 6 (Sat)

    public function apply(float $subtotal, object $user, Collection $cartItems, DateTimeImmutable $now): float
    {
        if (!$this->is_active) return 0;

        if ($this->start_date && $now < $this->start_date) return 0;
        if ($this->end_date && $now > $this->end_date) return 0;

        if ($this->fixed_date && $now->format('Y-m-d') !== $this->fixed_date->format('Y-m-d')) return 0;

        if ($this->weekends_only && !in_array($now->format('N'), [6, 7])) return 0; // Sat=6, Sun=7

        if (!is_null($this->only_weekday) && intval($now->format('w')) !== $this->only_weekday) return 0;

        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        return 0;
    }
}

beforeEach(function () {
    $this->user = (object)['id' => 1];
    $this->cartItems = new Collection([
        (object)['product_id' => 1, 'price' => 50, 'quantity' => 2], // total 100
    ]);
});

it('applies coupon only during date range', function () {
    $coupon = new TimeBasedCoupon();
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
    $coupon = new TimeBasedCoupon();
    $coupon->type = 'fixed';
    $coupon->value = 15;
    $coupon->fixed_date = new DateTimeImmutable('2025-08-10');

    $matchDate = new DateTimeImmutable('2025-08-10');
    $wrongDate = new DateTimeImmutable('2025-08-11');

    expect($coupon->apply(100, $this->user, $this->cartItems, $matchDate))->toBe(15);
    expect($coupon->apply(100, $this->user, $this->cartItems, $wrongDate))->toBe(0);
});

it('applies coupon only on weekends', function () {
    $coupon = new TimeBasedCoupon();
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
    $coupon = new TimeBasedCoupon();
    $coupon->type = 'fixed';
    $coupon->value = 12;
    $coupon->only_weekday = 1; // Monday (0=Sun, 1=Mon...)

    $monday = new DateTimeImmutable('2025-08-11'); // Monday
    $tuesday = new DateTimeImmutable('2025-08-12');

    expect($coupon->apply(100, $this->user, $this->cartItems, $monday))->toBe(12);
    expect($coupon->apply(100, $this->user, $this->cartItems, $tuesday))->toBe(0);
});













class SimpleCoupon
{
    public $type;
    public $value; // not used in these cases
    public $is_active = true;
    public $free_product_id = null;

    public function apply(float $subtotal, object $user, Collection $cartItems, array &$order): float
    {
        if (!$this->is_active) return 0;

        switch ($this->type) {
            case 'free_item':
                $order['free_items'][] = $this->free_product_id;
                return 0;

            case 'free_shipping':
                $order['delivery_fee'] = 0;
                return 0;

            default:
                return 0;
        }
    }
}

beforeEach(function () {
    $this->user = (object)['id' => 1];
    $this->cartItems = new Collection([
        (object)['product_id' => 1, 'price' => 100, 'quantity' => 1],
    ]);

    // Basic order template
    $this->order = [
        'delivery_fee' => 10,
        'free_items' => [],
    ];
});

it('adds free item to order', function () {
    $coupon = new SimpleCoupon();
    $coupon->type = 'free_item';
    $coupon->free_product_id = 999;

    $discount = $coupon->apply(100, $this->user, $this->cartItems, $this->order);

    expect($discount)->toBe(0);
    expect($this->order['free_items'])->toContain(999);
});

it('sets delivery fee to zero for free shipping', function () {
    $coupon = new SimpleCoupon();
    $coupon->type = 'free_shipping';

    $discount = $coupon->apply(100, $this->user, $this->cartItems, $this->order);

    expect($discount)->toBe(0);
    expect($this->order['delivery_fee'])->toBe(0);
});