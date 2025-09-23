<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class UsersCountChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 2;

    protected ?string $heading = 'Users Count Chart';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate   = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        $cacheKey = CacheKeys::USERS_COUNT_CHART . '_' .
            Carbon::parse($startDate)->format('Y-m-d') . '_' .
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            $users = DB::table('users')
                ->selectRaw("DATE(created_at) as date, COUNT(*) as total")
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date')
                ->toArray();

            $labels = [];
            $data = [];

            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $day = $date->format('Y-m-d');
                $labels[] = $day;
                $data[] = $users[$day] ?? 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Users Count Chart',
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
