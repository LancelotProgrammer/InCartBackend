<?php

namespace App\Listeners;

use App\Events\OrderDeleting;
use Illuminate\Support\Facades\Log;

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
        Log::channel('app_log')->info('Listeners: delete cart products on order delete', ['order' => $event->order]);

        $order = $event->order;
        foreach ($order->carts as $cart) {
            $cart->cartProducts->each->delete();
        }
        $order->forceApproveOrder()->delete();
    }
}
