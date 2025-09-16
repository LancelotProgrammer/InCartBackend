<?php

namespace App\Services;

use App\Models\Favorite;

class FavoriteService
{
    public static function isProductFavorite(int $productId, int $userId): false
    {
        return Favorite::where('product_id', $productId)
            ->where('user_id', $userId)
            ->exists();
    }
}
