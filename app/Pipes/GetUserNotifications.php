<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class GetUserNotifications
{
    public function __invoke(Request $request, Closure $next): Closure
    {
        $notifications = $request->user()->notifications()->latest()->simplePaginate();

        return $next($notifications->items());
    }
}
