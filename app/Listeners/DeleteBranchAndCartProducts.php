<?php

namespace App\Listeners;

use App\Events\ProductDeleting;
use Illuminate\Support\Facades\Log;

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
        Log::info('Listeners: delete branch and cart products', ['product' => $event->product]);

        $product = $event->product;
        $product->branches()->detach();
    }
}
