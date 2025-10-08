<?php

namespace App\Filament\Resources\OrderArchives;

use App\Filament\Resources\OrderArchives\Pages\ManageOrderArchives;
use App\Models\OrderArchive;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OrderArchiveResource extends Resource
{
    protected static ?string $model = OrderArchive::class;

    protected static string|UnitEnum|null $navigationGroup = 'Resources';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('archived_at')->dateTime(),
                TextEntry::make('order_number'),
                TextEntry::make('cancel_reason'),
                TextEntry::make('order_status')->badge(),
                TextEntry::make('payment_status')->badge(),
                TextEntry::make('delivery_status')->badge(),
                TextEntry::make('subtotal_price')->numeric(),
                TextEntry::make('coupon_discount')->numeric(),
                TextEntry::make('delivery_fee')->numeric(),
                TextEntry::make('service_fee')->numeric(),
                TextEntry::make('tax_amount')->numeric(),
                TextEntry::make('total_price')->numeric(),
                TextEntry::make('delivery_scheduled_type')->badge(),
                TextEntry::make('delivery_date')->dateTime(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('order_number'),
                TextColumn::make('archived_at'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrderArchives::route('/'),
        ];
    }
}
