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

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'Most Clicked Advertisements';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfYear();

        $cacheKey = CacheKeys::MOST_CLICKED_ADVERTISEMENTS.'_'.
            Carbon::parse($startDate)->format('Y-m-d').'_'.
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            $ads = DB::table('advertisement_user')
                ->join('advertisements', 'advertisement_user.advertisement_id', '=', 'advertisements.id')
                ->select('advertisements.title as title', DB::raw('COUNT(advertisement_user.id) as total_clicks'))
                ->whereBetween('advertisement_user.created_at', [$startDate, $endDate])
                ->groupBy('advertisements.id', 'advertisements.title')
                ->orderByDesc('total_clicks')
                ->limit(10)
                ->pluck('total_clicks', 'title')
                ->toArray();

            $labels = $data = [];
            $locale = App::getLocale();

            foreach ($ads as $titleJson => $clicks) {
                $titleArray = json_decode($titleJson, true);
                $label = is_array($titleArray)
                    ? ($titleArray[$locale] ?? reset($titleArray) ?? $titleJson)
                    : $titleJson;
                $labels[] = $label;
                $data[] = (int) $clicks;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Clicks',
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
