<?php

namespace App\Pipes;

use App\Models\Gift;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetGifts
{
    public function __invoke(Request $request, Closure $next): array
    {
        return $next([
            'points' => $request->user()->loyalty->points ?? 0,
            'gifts' => Gift::published()->get()->map(function (Gift $gift) {
                return [
                    'id' => $gift->id,
                    'title' => $gift->title,
                    'points' => $gift->points,
                    'discount' => $gift->discount,
                    'allowed_sub_total_price' => $gift->allowed_sub_total_price,
                ];
            })
        ]);
    }
}
