<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuccessfulResponseResource;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function getUserNotifications(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource;
    }
}
