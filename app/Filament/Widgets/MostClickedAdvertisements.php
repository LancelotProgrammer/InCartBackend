<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MostClickedAdvertisements extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 2;

    protected ?string $heading = 'Most Clicked Advertisements';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate   = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        $cacheKey = CacheKeys::MOST_CLICKED_ADVERTISEMENTS . '_' .
            Carbon::parse($startDate)->format('Y-m-d') . '_' .
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            // Top clicked ads query
            $ads = DB::table('advertisements')
                ->select('title', DB::raw('COUNT(*) as total_clicks'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('title')
                ->orderByDesc('total_clicks')
                ->limit(10)
                ->pluck('total_clicks', 'title')
                ->toArray();

            $labels = [];
            $data   = [];

            foreach ($ads as $titleJson => $clicks) {
                $titleArray = json_decode($titleJson, true);
                $locale = App::getLocale();
                $label = $titleArray[$locale] ?? reset($titleArray) ?? 'Unknown';
                $labels[] = $label;
                $data[] = $clicks;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Clicks',
                        'data'  => $data,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar'; // bar chart is better for ranking ads
    }
}
