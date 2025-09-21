<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use App\Enums\OrderStatus;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 8;

    protected ?string $heading = 'Order Status Chart';

    protected int | string | array $columnSpan = 2;

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        $cacheKey = CacheKeys::ORDER_STATUS_CHART . '_' .
            Carbon::parse($startDate)->format('Y-m-d') . '_' .
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            // Get counts grouped by status
            $statusCounts = DB::table('orders')
                ->select('order_status', DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('order_status')
                ->pluck('total', 'order_status')
                ->toArray();

            // Prepare labels and data using OrderStatus enum
            $labels = [];
            $data = [];

            foreach (OrderStatus::cases() as $status) {
                $labels[] = $status->getLabel();
                $data[] = $statusCounts[$status->value] ?? 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Order Status Chart',
                        'data' => $data,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
