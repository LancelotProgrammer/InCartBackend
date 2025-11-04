<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Advertisements\AdvertisementResource;
use App\Filament\Resources\Audits\AuditResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Widgets\GeneralStatsOverview;
use App\Filament\Widgets\OrderStatsOverview;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\AccountWidget;

class Welcome extends Page
{
    protected string $view = 'filament.pages.welcome';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PaperAirplane;

    protected static ?int $navigationSort = -3;

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getHeaderWidgets(): array
    {
        return auth()->user()->canViewDashboard() ? [
            AccountWidget::class,
            GeneralStatsOverview::class,
            OrderStatsOverview::class,
        ] : [
            AccountWidget::class,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Quick Actions')
                    ->visible(fn () => auth()->user()->canViewDashboard())
                    ->columns(6)
                    ->schema([
                        Action::make('open_statistics')
                            ->color('primary')
                            ->icon(Heroicon::DocumentChartBar)
                            ->url(route('filament.admin.pages.dashboard')),

                        Action::make('open_settings')
                            ->color('primary')
                            ->icon(Heroicon::Cog8Tooth)
                            ->url(Settings::getUrl()),

                        Action::make('open_products')
                            ->color('primary')
                            ->icon(Heroicon::Cube)
                            ->url(ProductResource::getUrl('index')),

                        Action::make('open_categories')
                            ->color('primary')
                            ->icon(Heroicon::NumberedList)
                            ->url(CategoryResource::getUrl('index')),

                        Action::make('open_advertisements')
                            ->color('primary')
                            ->icon(Heroicon::Megaphone)
                            ->url(AdvertisementResource::getUrl('index')),

                        Action::make('open_audit_logs')
                            ->color('primary')
                            ->icon(Heroicon::ClipboardDocumentList)
                            ->url(AuditResource::getUrl('index')),

                    ]),
                Section::make('Welcome to In-Cart Dashboard!')->schema([
                    View::make('components.welcome-widget'),
                ]),
            ]);
    }

    public static function canAccess(): bool
    {
        return true;
    }
}
