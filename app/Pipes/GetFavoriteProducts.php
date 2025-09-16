<?php

namespace App\Pipes;

use App\Models\Favorite;
use Closure;
use Illuminate\Http\Request;

class GetFavoriteProducts
{
    public function __invoke(Request $request, Closure $next): array
    {
        $favorites = Favorite::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        return $next($favorites);
    }
}
