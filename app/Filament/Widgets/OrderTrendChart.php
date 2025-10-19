<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Order Trend Chart';

    protected int|string|array $columnSpan = 2;

    protected ?string $pollingInterval = null;

    public ?string $filter = 'per_day';

    protected function getFilters(): ?array
    {
        return [
            'per_day' => 'Per Day',
            'per_week' => 'Per Week',
            'per_month' => 'Per Month',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfYear();

        $cacheKey = CacheKeys::ORDER_TREND_CHART.'_'.
            $activeFilter.'_'.
            Carbon::parse($startDate)->format('Y-m-d').'_'.
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate, $activeFilter) {
            $labels = [];
            $data = [];

            switch ($activeFilter) {
                case 'per_week':
                    $orders = DB::table('orders')
                        ->selectRaw('YEAR(delivery_date) as year, WEEK(delivery_date, 1) as week, COUNT(*) as total')
                        ->whereBetween('delivery_date', [$startDate, $endDate])
                        ->groupBy('year', 'week')
                        ->orderBy('year')
                        ->orderBy('week')
                        ->pluck('total', 'week')
                        ->toArray();

                    $period = CarbonPeriod::create($startDate, '1 week', $endDate);
                    foreach ($period as $date) {
                        $weekNum = $date->format('W');
                        $labels[] = 'Week '.$weekNum;
                        $data[] = $orders[$weekNum] ?? 0;
                    }
                    break;

                case 'per_month':
                    $orders = DB::table('orders')
                        ->selectRaw("DATE_FORMAT(delivery_date, '%Y-%m') as month, COUNT(*) as total")
                        ->whereBetween('delivery_date', [$startDate, $endDate])
                        ->groupBy('month')
                        ->orderBy('month')
                        ->pluck('total', 'month')
                        ->toArray();

                    $period = CarbonPeriod::create($startDate, '1 month', $endDate);
                    foreach ($period as $date) {
                        $month = $date->format('Y-m');
                        $labels[] = $date->format('M Y');
                        $data[] = $orders[$month] ?? 0;
                    }
                    break;

                default:
                    $ordersPerHour = DB::table('orders')
                        ->selectRaw('HOUR(delivery_date) as hour, COUNT(*) as total')
                        ->whereBetween('delivery_date', [$startDate, $endDate])
                        ->groupBy('hour')
                        ->orderBy('hour')
                        ->pluck('total', 'hour')
                        ->toArray();

                    $labels = [];
                    $data = [];

                    for ($hour = 0; $hour <= 23; $hour++) {
                        $label = sprintf('%02d:00', $hour);
                        $labels[] = $label;
                        $data[] = isset($ordersPerHour[$hour]) ? (int) $ordersPerHour[$hour] : 0;
                    }
                    break;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Order Trend Chart',
                        'data' => $data,
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
