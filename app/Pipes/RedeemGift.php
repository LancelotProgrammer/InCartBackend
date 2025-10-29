<?php

namespace App\Pipes;

use App\Services\LoyaltyService;
use Closure;
use Illuminate\Http\Request;

class RedeemGift
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->merge(['id' => $request->route('id')]);

        $request->validate([
            'id' => 'required|integer|exists:gifts,id',
        ]);

        $gift = LoyaltyService::redeemGift($request->user(), $request->route('id'));

        return $next(['code' => $gift->code]);
    }
}
