<?php

namespace App\Pipes;

use App\Models\Gift;
use Closure;
use Illuminate\Http\Request;

class GetGifts
{
    public function __invoke(Request $request, Closure $next): array
    {
        return $next(Gift::published()->get()->map(function (Gift $gift) {
            return [
                'title' => $gift->title,
                'points' => $gift->points,
                'discount' => $gift->discount,
                'allowed_sub_total_price' => $gift->allowed_sub_total_price,
            ];
        }));
    }
}
