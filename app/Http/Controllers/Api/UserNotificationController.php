<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\GetUserNotifications;
use App\Pipes\MarkUserNotificationAsRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Pipeline;

class UserNotificationController extends Controller
{
    /**
     * @authenticated
     *
     * @group Profile Actions
     */
    public function getUserNotifications(Request $request): SuccessfulResponseResourceWithMetadata
    {
        return new SuccessfulResponseResourceWithMetadata(Pipeline::send($request)
            ->through([
                GetUserNotifications::class,
            ])
            ->thenReturn());
    }

    /**
     * @authenticated
     *
     * @group Profile Actions
     */
    public function markUserNotificationAsRead(Request $request): EmptySuccessfulResponseResource
    {
        Pipeline::send($request)
            ->through([
                MarkUserNotificationAsRead::class,
            ])
            ->thenReturn();

        return new EmptySuccessfulResponseResource;
    }
}
