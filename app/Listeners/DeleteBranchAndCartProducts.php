<?php

namespace App\Listeners;

use App\Events\ProductDeleting;

class DeleteBranchAndCartProducts
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
    public function handle(ProductDeleting $event): void
    {
        $product = $event->product;
        $product->branches()->detach();
        $product->carts()->detach();
    }
}
