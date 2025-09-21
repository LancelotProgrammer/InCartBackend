<?php

namespace App\Filament\Pages;

use App\Constants\CacheKeys;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->action(function () {
                    // NOTE: Change this if you are using a cache drive other than the database
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::MOST_CLICKED_ADVERTISEMENTS . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::MOST_CLICKED_ADVERTISEMENTS . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::ORDERS_COUNT_CHART . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::GENERAL_STATS_OVERVIEW . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::ORDER_STATS_OVERVIEW . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::ORDER_STATUS_CHART . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::ORDER_TREND_CHART . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::MOST_SELLING_PRODUCTS_CHART . '%')->delete();
                    DB::table('cache')->whereLike('key', '%' . CacheKeys::USERS_COUNT_CHART . '%')->delete();
                }),
            FilterAction::make()
                ->schema([
                    DatePicker::make('startDate'),
                    DatePicker::make('endDate')->after('startDate'),
                ]),
        ];
    }

    public function getColumns(): int | array
    {
        return 4;
    }
}
