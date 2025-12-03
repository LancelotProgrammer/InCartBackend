<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use App\Enums\DeliveryScheduledType;
use App\Services\TranslationService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected int|array|null $columns = 4;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Default to current month if no filter provided
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfYear();
        $branchId = $this->pageFilters['branch'] ?? null;

        // Cache key with date range
        $cacheKey = CacheKeys::ORDER_STATS_OVERVIEW . '_' .
            Carbon::parse($startDate)->format('Y-m-d') . '_' .
            Carbon::parse($endDate)->format('Y-m-d') . '_' .
            ($branchId ?? 'all');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($branchId) {
            $totalOrdersQuery = DB::table('orders');
            if ($branchId) {
                $totalOrdersQuery->where('branch_id', $branchId);
            }
            $totalOrdersResult = $totalOrdersQuery->count();

            $ordersQuery = DB::table('orders');
            if ($branchId) {
                $ordersQuery->where('branch_id', $branchId);
            }
            $avgSubtotal = $ordersQuery->avg('subtotal_price');
            $avgSubtotal = number_format($avgSubtotal, 2);

            $mostPaymentQuery = DB::table('orders')
                ->join('payment_methods', 'orders.payment_method_id', '=', 'payment_methods.id')
                ->select('payment_methods.title', DB::raw('COUNT(*) as total'));
            if ($branchId) {
                $mostPaymentQuery->where('orders.branch_id', $branchId);
            }
            $mostPaymentResult = $mostPaymentQuery->groupBy('payment_methods.id', 'payment_methods.title')
                ->orderByDesc('total')
                ->first();
            $mostPayment = $mostPaymentResult
                ? TranslationService::getTranslatableAttribute($mostPaymentResult->title)
                : 'N/A';

            $mostDeliveryTypeQuery = DB::table('orders')
                ->select('delivery_scheduled_type', DB::raw('COUNT(*) as total'));
            if ($branchId) {
                $mostDeliveryTypeQuery->where('branch_id', $branchId);
            }
            $mostDeliveryTypeResult = $mostDeliveryTypeQuery->groupBy('delivery_scheduled_type')
                ->orderByDesc('total')
                ->value('delivery_scheduled_type');
            $mostDeliveryType = $mostDeliveryTypeResult
                ? DeliveryScheduledType::tryFrom($mostDeliveryTypeResult)->getLabel()
                : 'N/A';

            $mostCouponQuery = DB::table('orders')
                ->join('coupons', 'orders.coupon_id', '=', 'coupons.id')
                ->select('coupons.title', DB::raw('COUNT(*) as total'));
            if ($branchId) {
                $mostCouponQuery->where('orders.branch_id', $branchId);
            }
            $mostCouponResult = $mostCouponQuery->groupBy('coupons.id', 'coupons.title')
                ->orderByDesc('total')
                ->first();
            $mostCoupon = $mostCouponResult
                ? TranslationService::getTranslatableAttribute($mostCouponResult->title)
                : 'N/A';

            $mostActiveDeliveryQuery = DB::table('users')
                ->select('name', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.delivery_id', '=', 'users.id');
            if ($branchId) {
                $mostActiveDeliveryQuery->where('orders.branch_id', $branchId);
            }
            $mostActiveDelivery = $mostActiveDeliveryQuery->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->value('name') ?? 'N/A';

            $mostActiveManagerQuery = DB::table('users')
                ->select('name', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.manager_id', '=', 'users.id');
            if ($branchId) {
                $mostActiveManagerQuery->where('orders.branch_id', $branchId);
            }
            $mostActiveManager = $mostActiveManagerQuery->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->value('name') ?? 'N/A';

            $mostActiveCustomerQuery = DB::table('users')
                ->select('name', DB::raw('COUNT(*) as total'))
                ->join('orders', 'orders.customer_id', '=', 'users.id');
            if ($branchId) {
                $mostActiveCustomerQuery->where('orders.branch_id', $branchId);
            }
            $mostActiveCustomer = $mostActiveCustomerQuery->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->value('name') ?? 'N/A';

            return [
                Stat::make('Total Orders', $totalOrdersResult),
                Stat::make('Average Order Subtotal', $avgSubtotal),
                Stat::make('Most Payment Used', $mostPayment),
                Stat::make('Most Coupon used', $mostCoupon),
                Stat::make('Most Delivery Type', $mostDeliveryType),
                Stat::make('Most Active Delivery', $mostActiveDelivery),
                Stat::make('Most Active Manager', $mostActiveManager),
                Stat::make('Most Active Customer', $mostActiveCustomer),
            ];
        });
    }
}
