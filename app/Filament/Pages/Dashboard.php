<?php

namespace App\Filament\Pages;

use App\Services\Cache;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->action(function () {
                    Cache::deleteDashboardCache();
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
