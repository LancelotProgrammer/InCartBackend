<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class GetUserNotifications
{
    public function __invoke(Request $request, Closure $next)
    {
        $notifications = $request->user()->notifications()->latest()->paginate();

        if (! $notifications) {
            throw new LogicalException("No notifications found", "User has no notifications", 404);
        }

        return $next([$notifications->items(), [
            'current_page' => $notifications->currentPage(),
            'per_page' => $notifications->perPage(),
            'total' => $notifications->total(),
            'last_page' => $notifications->lastPage(),
        ]]);
    }
}
