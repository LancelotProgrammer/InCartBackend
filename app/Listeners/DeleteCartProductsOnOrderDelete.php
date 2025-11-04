<?php

namespace App\Listeners;

use App\Events\OrderDeleting;

class DeleteCartProductsOnOrderDelete
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderDeleting $event): void
    {
        $order = $event->order;
        foreach ($order->carts as $cart) {
            $cart->cartProducts->each->delete();
        }
    }
}
