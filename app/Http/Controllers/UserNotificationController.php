<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResourceWithMetadata;
use App\Pipes\AuthorizeUser;
use App\Pipes\GetUserNotifications;
use App\Pipes\MarkUserNotificationAsRead;
use App\Pipes\ValidateUser;
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
                ValidateUser::class,
                new AuthorizeUser('get-user-notifications'),
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
                ValidateUser::class,
                new AuthorizeUser('mark-user-notification-as-read'),
                MarkUserNotificationAsRead::class,
            ])
            ->thenReturn();
        return new EmptySuccessfulResponseResource();
    }
}
