<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use App\Enums\DeliveryScheduledType;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected int | array | null $columns = 4;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Default to current month if no filter provided
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate   = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        // Cache key with date range
        $cacheKey = CacheKeys::ORDER_STATS_OVERVIEW . '_' .
            Carbon::parse($startDate)->format('Y-m-d') . '_' .
            Carbon::parse($endDate)->format('Y-m-d');


        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            // Average order subtotal
            $avgSubtotal = DB::table('orders')->avg('subtotal_price');
            $avgSubtotal = number_format($avgSubtotal, 2);

            // Most used payment method
            $mostPayment = get_translatable_attribute(DB::table('orders')
                ->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                ->select('payment_methods.title', DB::raw('COUNT(*) as total'))
                ->groupBy('payment_methods.id', 'payment_methods.title')
                ->orderByDesc('total')
                ->value('payment_methods.title')) ?? 'N/A';

            // Most used delivery type
            $mostDeliveryType = DeliveryScheduledType::tryFrom(DB::table('orders')
                ->select('delivery_scheduled_type', DB::raw('COUNT(*) as total'))
                ->groupBy('delivery_scheduled_type')
                ->orderByDesc('total')
                ->value('delivery_scheduled_type'))->getLabel() ?? 'N/A';

            // Most common cancel reason
            // $mostCancelReason = DB::table('orders')
            //     ->select('cancel_reason', DB::raw('COUNT(*) as total'))
            //     ->whereNotNull('cancel_reason')
            //     ->groupBy('cancel_reason')
            //     ->orderByDesc('total')
            //     ->value('cancel_reason') ?? 'N/A';

            // Most active delivery
            $mostActiveDelivery = DB::table('users')
                ->select('name', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.delivery_id', '=', 'users.id')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->value('name') ?? 'N/A';

            // Most active manager
            $mostActiveManager = DB::table('users')
                ->select('name', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.manager_id', '=', 'users.id')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->value('name') ?? 'N/A';

            // Most active branch
            $mostActiveBranch = get_translatable_attribute(DB::table('branches')
                ->select('title', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.branch_id', '=', 'branches.id')
                ->groupBy('branches.id', 'branches.title')
                ->orderByDesc('total')
                ->value('title')) ?? 'N/A';

            // Most active customer
            $mostActiveCustomer = DB::table('users')
                ->select('name', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.customer_id', '=', 'users.id')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->value('name') ?? 'N/A';

            return [
                Stat::make('Average Order Subtotal', $avgSubtotal),
                Stat::make('Most Payment Used', $mostPayment),
                Stat::make('Most Delivery Type', $mostDeliveryType),
                Stat::make('Most Cancel Reason', 'N/A'),
                Stat::make('Most Active Delivery', $mostActiveDelivery),
                Stat::make('Most Active Manager', $mostActiveManager),
                Stat::make('Most Active Branch', $mostActiveBranch),
                Stat::make('Most Active Customer', $mostActiveCustomer),
            ];
        });
    }
}
