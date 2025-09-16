<?php

namespace App\Pipes;

use App\Models\Favorite;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetFavoriteProducts
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        $favorites = Favorite::with('product.files')
            ->where('user_id', $request->user()->id)
            ->get()
            ->map(function (Favorite $favorite) {
                $product = $favorite->product;
                $image = $product->files->first()?->url;
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'image' => $image,
                    'favorite_at' => $favorite->created_at,
                ];
            });

        return $next($favorites);
    }
}
