<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use App\Models\Role;
use App\Services\TranslationService;
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
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfYear();

        $cacheKey = CacheKeys::GENERAL_STATS_OVERVIEW.'_'.
            Carbon::parse($startDate)->format('Y-m-d').'_'.
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            $totalUsers = DB::table('users')->whereBetween('created_at', [$startDate, $endDate])->where('role_id', '=', Role::where('code', '=', Role::ROLE_CUSTOMER_CODE)->first()->id)->count();
            $totalProducts = DB::table('products')->whereBetween('created_at', [$startDate, $endDate])->count();
            $totalAds = DB::table('advertisements')->whereBetween('created_at', [$startDate, $endDate])->count();

            $mostActiveBranchQuery = DB::table('branches')
                ->select('title', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.branch_id', '=', 'branches.id');
            $mostActiveBranchResult = $mostActiveBranchQuery->groupBy('branches.id', 'branches.title')
                ->orderByDesc('total')
                ->first();
            $mostActiveBranch = $mostActiveBranchResult
                ? TranslationService::getTranslatableAttribute($mostActiveBranchResult->title)
                : 'N/A';

            return [
                Stat::make('Total Customers', number_format($totalUsers)),
                Stat::make('Total Products', number_format($totalProducts)),
                Stat::make('Total Ads', number_format($totalAds)),
                Stat::make('Most Active Branch Per Orders', $mostActiveBranch),
            ];
        });
    }
}
