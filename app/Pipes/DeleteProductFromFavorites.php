<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\Favorite;
use Closure;
use Illuminate\Http\Request;

class DeleteProductFromFavorites
{
    public function __invoke(Request $request, Closure $next): array
    {
        $productId = $request->route('id');
        $userId = $request->user()->id;

        $deleted = Favorite::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        if ($deleted === 0) {
            throw new LogicalException('Favorite not found or does not belong to the user.');
        }

        return $next([]);
    }
}
