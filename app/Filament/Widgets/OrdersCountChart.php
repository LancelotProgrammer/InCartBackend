<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrdersCountChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 2;

    protected ?string $heading = 'Orders Count Chart';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        // Default to current month if no filter provided
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate   = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        // Cache key with date range
        $cacheKey = CacheKeys::ORDERS_COUNT_CHART . '_' .
            Carbon::parse($startDate)->format('Y-m-d') . '_' .
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            // Query: count orders per day
            $orders = DB::table('orders')
                ->selectRaw("DATE(created_at) as date, COUNT(*) as total")
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray();

            // Build labels & dataset for each day in the period
            $labels = [];
            $data = [];

            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $day = $date->format('Y-m-d');
                $labels[] = $day;
                $data[] = $orders[$day] ?? 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Orders Count Chart',
                        'data'  => $data,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }
}
