<?php

namespace App\Pipes;

use App\Models\Gift;
use Closure;
use Illuminate\Http\Request;

class GetGiftDetail
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->merge(['id' => $request->route('id')]);

        $request->validate([
            'id' => 'required|integer|exists:products,id',
        ]);

        return $next(Gift::published()->where('id', '=', $request->route('id'))->first(
            [
                'title',
                'description',
                'points',
                'discount',
                'allowed_sub_total_price',
            ]
        ));
    }
}
