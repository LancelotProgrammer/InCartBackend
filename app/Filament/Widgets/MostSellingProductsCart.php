<?php

namespace App\Filament\Widgets;

use App\Constants\CacheKeys;
use App\Services\TranslationService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MostSellingProductsCart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 2;

    protected ?string $heading = 'Most Selling Products Cart';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? Carbon::now()->startOfYear();
        $endDate = $this->pageFilters['endDate'] ?? Carbon::now()->endOfYear();
        $branchId = $this->pageFilters['branch'] ?? null;

        $cacheKey = CacheKeys::MOST_SELLING_PRODUCTS_CHART.'_'.
            Carbon::parse($startDate)->format('Y-m-d').'_'.
            Carbon::parse($endDate)->format('Y-m-d').'_'.
            ($branchId ?? 'all');

        return Cache::remember($cacheKey, now()->addHour(), function () use ($startDate, $endDate, $branchId) {
            $query = DB::table('cart_product')
                ->select('product_id', 'title', DB::raw('SUM(quantity) as total_sold'))
                ->whereBetween('cart_product.created_at', [$startDate, $endDate]);
            
            if ($branchId) {
                $query->join('carts', 'cart_product.cart_id', '=', 'carts.id')
                    ->join('orders', 'carts.order_id', '=', 'orders.id')
                    ->where('orders.branch_id', $branchId);
            }
            
            $products = $query->groupBy('product_id', 'title')
                ->orderByDesc('total_sold')
                ->limit(10)
                ->get();

            $labels = [];
            $data = [];

            foreach ($products as $product) {
                $productName = $product->title ?? 'N/A';
                $labels[] = TranslationService::getTranslatableAttribute($productName);
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
        return 'bar';
    }
}
