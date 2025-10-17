<?php

namespace App\Pipes;

use App\Services\OrderService;
use Closure;
use Illuminate\Http\Request;

class CancelOrder
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        OrderService::userCancel(
            $request->route('id'),
            $request->user()->id,
            $request->input('cancel_reason'),
        );

        return $next([]);
    }
}
