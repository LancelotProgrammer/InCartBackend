<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Favorite;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class AddProductToFavorites
{
    public function __invoke(Request $request, Closure $next): array
    {
        $productId = $request->route('id');
        $userId = $request->user()->id;

        $product = Product::find($productId);

        if (!$product) {
            throw new LogicalException('Product not found.');
        }

        if (Favorite::where('user_id', $userId)->where('product_id', $productId)->exists()) {
            throw new LogicalException('Product is already in user favorites.');
        }

        Favorite::create([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        return $next([]);
    }
}
