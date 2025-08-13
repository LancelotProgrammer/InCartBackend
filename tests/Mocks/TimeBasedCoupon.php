<?php

namespace Tests\Mocks;

use DateTimeImmutable;
use Illuminate\Support\Collection;

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
        if (! $this->is_active) {
            return 0;
        }

        if ($this->start_date && $now < $this->start_date) {
            return 0;
        }
        if ($this->end_date && $now > $this->end_date) {
            return 0;
        }

        if ($this->fixed_date && $now->format('Y-m-d') !== $this->fixed_date->format('Y-m-d')) {
            return 0;
        }

        if ($this->weekends_only && ! in_array($now->format('N'), [6, 7])) {
            return 0;
        } // Sat=6, Sun=7

        if (! is_null($this->only_weekday) && intval($now->format('w')) !== $this->only_weekday) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        return 0;
    }
}
