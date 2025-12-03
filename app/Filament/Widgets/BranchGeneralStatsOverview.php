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

class BranchGeneralStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfYear();
        $branchId = $this->pageFilters['branch'] ?? null;

        // Return empty if no branch selected
        if (!$branchId) {
            return [];
        }

        $cacheKey = CacheKeys::BRANCH_GENERAL_STATS_OVERVIEW.'_'.
            Carbon::parse($startDate)->format('Y-m-d').'_'.
            Carbon::parse($endDate)->format('Y-m-d').'_'.
            $branchId;

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate, $branchId) {
            $totalOrders = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('branch_id', $branchId)
                ->count();

            $totalProducts = DB::table('branch_product')
                ->join('products', 'branch_product.product_id', '=', 'products.id')
                ->whereBetween('products.created_at', [$startDate, $endDate])
                ->where('branch_product.branch_id', $branchId)
                ->distinct()
                ->count('products.id');

            $totalUsers = DB::table('users')
                ->join('orders', 'orders.customer_id', '=', 'users.id')
                ->whereBetween('users.created_at', [$startDate, $endDate])
                ->where('orders.branch_id', $branchId)
                ->where('users.role_id', '=', Role::where('code', '=', Role::ROLE_CUSTOMER_CODE)->first()->id)
                ->distinct()
                ->count('users.id');

            $totalAds = DB::table('advertisements')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('branch_id', $branchId)
                ->count();

            return [
                Stat::make('Total Orders Per Selected Branch', number_format($totalOrders)),
                Stat::make('Total Products Per Selected Branch', number_format($totalProducts)),
                Stat::make('Total Customers Per Selected Branch', number_format($totalUsers)),
                Stat::make('Total Ads Per Selected Branch', number_format($totalAds)),
            ];
        });
    }
}
