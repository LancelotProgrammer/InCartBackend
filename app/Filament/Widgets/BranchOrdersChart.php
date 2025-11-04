<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BranchOrdersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'Branch Orders Chart';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfYear();

        $cacheKey = CacheKeys::BRANCH_ORDERS_CHART.'_'.
            Carbon::parse($startDate)->format('Y-m-d').'_'.
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            $orders = DB::table('orders')
                ->select('branch_id', DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('branch_id')
                ->orderByDesc('total')
                ->get();

            $labels = [];
            $data = [];

            foreach ($orders as $order) {
                $branchTitle = DB::table('branches')
                    ->where('id', $order->branch_id)
                    ->value('title');

                $labels[] = get_translatable_attribute($branchTitle);
                $data[] = $order->total;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Orders per Branch',
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
