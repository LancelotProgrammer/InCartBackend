<?php

namespace Tests\Mocks;

use DateTimeImmutable;
use Illuminate\Support\Collection;

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
        if (! $this->is_active) {
            return 0;
        }

        $now = new DateTimeImmutable;
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
        $eligibleItems = $cartItems->filter(fn ($item) => in_array($item->product_id, $this->products)
        );

        $discountBase = $eligibleItems->sum(fn ($item) => $item->price * $item->quantity
        );

        return $discountBase * ($this->value / 100);
    }

    protected function calculateCategoryDiscount(Collection $cartItems): float
    {
        $eligibleItems = $cartItems->filter(fn ($item) => in_array($item->product->category_id, $this->categories)
        );

        $discountBase = $eligibleItems->sum(fn ($item) => $item->price * $item->quantity
        );

        return $discountBase * ($this->value / 100);
    }

    protected function calculateBOGO(Collection $cartItems): float
    {
        $eligibleItems = $cartItems->filter(fn ($item) => in_array($item->product_id, $this->products)
        );

        $discount = 0;

        foreach ($eligibleItems as $item) {
            $freeQty = intdiv($item->quantity, 2);
            $discount += $freeQty * $item->price;
        }

        return $discount;
    }
}
