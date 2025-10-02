<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use App\Models\Role;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GeneralStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        $cacheKey = CacheKeys::GENERAL_STATS_OVERVIEW.'_'.
            Carbon::parse($startDate)->format('Y-m-d').'_'.
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            $totalOrders = DB::table('orders')->whereBetween('created_at', [$startDate, $endDate])->count();
            $totalProducts = DB::table('products')->whereBetween('created_at', [$startDate, $endDate])->count();
            $totalUsers = DB::table('users')->whereBetween('created_at', [$startDate, $endDate])->where('role_id', '=', Role::where('code', '=', Role::ROLE_CUSTOMER_CODE)->first()->id)->count();
            $totalAds = DB::table('advertisements')->whereBetween('created_at', [$startDate, $endDate])->count();

            return [
                Stat::make('Total Orders', number_format($totalOrders)),
                Stat::make('Total Products', number_format($totalProducts)),
                Stat::make('Total Customers', number_format($totalUsers)),
                Stat::make('Total Ads', number_format($totalAds)),
            ];
        });
    }
}
