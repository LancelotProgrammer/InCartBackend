<?php

namespace App\Pipes;

use App\Models\Favorite;
use Closure;
use Illuminate\Http\Request;

class DeleteProductFromFavorites
{
    public function __invoke(Request $request, Closure $next): array
    {
        $productId = $request->route('id');
        $userId = $request->user()->id;

        Favorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        return $next();
    }
}
