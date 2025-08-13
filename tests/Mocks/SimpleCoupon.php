<?php

namespace Tests\Mocks;

use Illuminate\Support\Collection;

class SimpleCoupon
{
    public $type;

    public $value; // not used in these cases

    public $is_active = true;

    public $free_product_id = null;

    public function apply(float $subtotal, object $user, Collection $cartItems, array &$order): float
    {
        if (! $this->is_active) {
            return 0;
        }

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
