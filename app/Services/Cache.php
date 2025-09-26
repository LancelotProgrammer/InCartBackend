<?php

namespace App\Services;

use App\Constants\CacheKeys;
use Illuminate\Support\Facades\DB;

class Cache
{
    public static function deleteHomeCache(): void
    {
        DB::table('cache')->whereLike('key', '%'.CacheKeys::HOME.'%')->delete();
    }

    public static function deleteSettingsCache(): void
    {
        DB::table('cache')->whereLike('key', '%'.CacheKeys::SETTINGS.'%')->delete();
    }

    public static function deleteTodaySupportCount(): void
    {
        DB::table('cache')->whereLike('key', '%'.CacheKeys::TODAY_SUPPORT_COUNT.'%')->delete();
    }

    public static function deletePendingOrderCount(): void
    {
        DB::table('cache')->whereLike('key', '%'.CacheKeys::PENDING_ORDER_COUNT.'%')->delete();
    }

    public static function deleteDashboardCache(): void
    {
        DB::table('cache')->whereLike('key', '%'.CacheKeys::MOST_CLICKED_ADVERTISEMENTS.'%')->delete();
        DB::table('cache')->whereLike('key', '%'.CacheKeys::BRANCH_ORDERS_CHART.'%')->delete();
        DB::table('cache')->whereLike('key', '%'.CacheKeys::GENERAL_STATS_OVERVIEW.'%')->delete();
        DB::table('cache')->whereLike('key', '%'.CacheKeys::ORDER_STATS_OVERVIEW.'%')->delete();
        DB::table('cache')->whereLike('key', '%'.CacheKeys::ORDER_STATUS_CHART.'%')->delete();
        DB::table('cache')->whereLike('key', '%'.CacheKeys::ORDER_TREND_CHART.'%')->delete();
        DB::table('cache')->whereLike('key', '%'.CacheKeys::MOST_SELLING_PRODUCTS_CHART.'%')->delete();
        DB::table('cache')->whereLike('key', '%'.CacheKeys::USERS_COUNT_CHART.'%')->delete();
    }
}
