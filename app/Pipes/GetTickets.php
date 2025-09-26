<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class GetTickets
{
    public function __invoke(Request $request, Closure $next): array
    {
        $notifications = $request->user()->tickets()->select(['id', 'question', 'reply', 'created_at'])->latest()->simplePaginate();

        return $next($notifications->items());
    }
}
