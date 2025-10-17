<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SessionService
{
    public static function deleteUserSessions(int $userId): void
    {
        DB::table('sessions')->where('user_id', '=', $userId)->delete();
    }
}
