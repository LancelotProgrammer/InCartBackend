<?php

namespace Tests\Mocks;

use Illuminate\Support\Collection;

class TestCouponUser
{
    public $type;

    public $value;

    public $is_active = true;

    public $user_id = null; // Specific user allowed

    public function apply(float $subtotal, object $user, Collection $cartItems): float
    {
        if (! $this->is_active) {
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
