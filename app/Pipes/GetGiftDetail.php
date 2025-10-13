<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
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

        $gift = Gift::published()->where('id', '=', $request->route('id'))->first(
            [
                'title',
                'description',
                'points',
                'discount',
                'allowed_sub_total_price',
            ]
        );

        if (! $gift) {
            throw new LogicalException(
                'Gift not found',
                'The gift ID does not exist'
            );
        }

        return $next([
            'title' => $gift->title,
            'description' => $gift->description,
            'points' => $gift->points,
            'discount' => $gift->discount,
            'allowed_sub_total_price' => $gift->allowed_sub_total_price,
        ]);
    }
}
