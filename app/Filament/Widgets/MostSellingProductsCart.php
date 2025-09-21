<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MostSellingProductsCart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 9;

    protected int | string | array $columnSpan = 2;

    protected ?string $heading = 'Most Selling Products Cart';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        // Default to current month if no filter provided
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfMonth();
        $endDate   = $this->pageFilters['endDate'] ?? Carbon::now()->endOfMonth();

        // Cache key with date range
        $cacheKey = CacheKeys::MOST_SELLING_PRODUCTS_CHART . '_' .
            Carbon::parse($startDate)->format('Y-m-d') . '_' .
            Carbon::parse($endDate)->format('Y-m-d');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate) {
            // Query top-selling products in the given period
            $products = DB::table('cart_product')
                ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            $labels = [];
            $data = [];

            foreach ($products as $product) {
                $productName = DB::table('products')->where('id', $product->product_id)->value('title') ?? 'N/A';
                $labels[] = get_translatable_attribute($productName);
                $data[] = $product->total_sold;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Most Selling Products',
                        'data' => $data,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar'; // better for product ranking
    }
}
