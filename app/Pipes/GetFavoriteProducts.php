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
        $favorites = Favorite::with(['product.files', 'product.branchProducts'])
            ->where('user_id', $request->user()->id)
            ->get()
            ->filter(fn(Favorite $favorite) => $favorite->product && $favorite->product->isPublishedInBranches())
            ->map(fn(Favorite $favorite) => array_merge($favorite->product->toApiArray(), ['favorite_at' => $favorite->created_at]))
            ->values();

        return $next($favorites);
    }
}
