<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\UserNotification;
use Closure;
use Illuminate\Http\Request;

class MarkUserNotificationAsRead
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $ids = $request->input('ids', []);

        $count = UserNotification::whereIn('id', $ids)
            ->where('user_id', $request->user()->id)
            ->update(['mark_as_read' => true]);

        if ($count === 0) {
            throw new LogicalException(
                'Notification not found',
                'None of the provided notification IDs exist or belong to the current user.'
            );
        }

        return $next([]);
    }
}
