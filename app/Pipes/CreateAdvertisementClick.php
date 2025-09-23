<?php

namespace App\Pipes;

use App\Models\AdvertisementUser;
use Closure;
use Illuminate\Http\Request;

class CreateAdvertisementClick
{
    public function __invoke(Request $request, Closure $next): array
    {
        $adId = $request->route('id');
        $userId = $request->user()->id;

        AdvertisementUser::updateOrCreate(
            [
                'advertisement_id' => $adId,
                'user_id' => $userId
            ]
        );

        return $next([
            'advertisement_id' => $adId,
            'user_id' => $userId,
        ]);
    }
}
