<?php

namespace App\Pipes;

use Closure;
use Illuminate\Http\Request;

class GetUserNotifications
{
    public function __invoke(Request $request, Closure $next): array
    {
        $notifications = $request->user()->userNotifications()->where('mark_as_read', '=', false)->latest()->simplePaginate();

        return $next($notifications->items());
    }
}
