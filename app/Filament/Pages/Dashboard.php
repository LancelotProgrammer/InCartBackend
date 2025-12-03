<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Services\CacheService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static string $routePath = 'statistics';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->action(function () {
                    CacheService::deleteDashboardCache();
                }),
            FilterAction::make()
                ->schema([
                    DatePicker::make('startDate'),
                    DatePicker::make('endDate')->after('startDate'),
                    Select::make('branch')->options(Branch::all()->pluck('title', 'id')),
                ]),
        ];
    }

    public function getColumns(): int|array
    {
        return 4;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->canViewDashboard();
    }

    public static function getNavigationLabel(): string
    {
        return 'statistics';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Statistics';
    }
}
