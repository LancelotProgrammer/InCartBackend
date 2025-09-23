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
        $notificationId = $request->route('id');

        $notification = UserNotification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $notification) {
            throw new LogicalException('Notification not found', 'The notification ID does not exist or does not belong to the current user.');
        }

        $notification->update(['mark_as_read' => true]);

        return $next([]);
    }
}
