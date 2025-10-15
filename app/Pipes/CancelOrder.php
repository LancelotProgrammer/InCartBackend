<?php

namespace App\Pipes;

use App\Services\OrderManager;
use Closure;
use Illuminate\Http\Request;

class CancelOrder
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        OrderManager::userCancel(
            $request->route('id'),
            $request->user()->id,
            $request->input('cancel_reason'),
        );

        return $next([]);
    }
}
