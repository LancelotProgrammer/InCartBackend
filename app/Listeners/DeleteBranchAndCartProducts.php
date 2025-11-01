<?php

namespace App\Listeners;

use App\Events\ProductDeleting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
