<?php

namespace App\Pipes;

use App\Models\Gift;
use Closure;
use Illuminate\Http\Request;

class GetGifts
{
    public function __invoke(Request $request, Closure $next): array
    {
        $user = $request->user();

        $userGiftIds = $user->gifts()->pluck('gift_id')->toArray();

        return $next([
            'points' => $user->loyalty->points ?? 0,
            'gifts' => Gift::published()->get()->map(function (Gift $gift) use ($userGiftIds) {
                return [
                    'id' => $gift->id,
                    'title' => $gift->title,
                    'description' => $gift->description,
                    'points' => $gift->points,
                    'discount' => $gift->discount,
                    'allowed_sub_total_price' => $gift->allowed_sub_total_price,
                    'code' => in_array($gift->id, $userGiftIds) ? $gift->code : null,
                ];
            }),
        ]);
    }
}
