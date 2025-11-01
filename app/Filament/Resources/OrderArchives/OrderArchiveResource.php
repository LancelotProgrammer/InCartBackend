<?php

namespace App\Filament\Resources\OrderArchives;

use App\Filament\Resources\OrderArchives\Pages\ManageOrderArchives;
use App\Models\OrderArchive;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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
            ->columns(1)
            ->components([
                Section::make('General Info')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_number')->label('Order number'),
                        TextEntry::make('order_status')->badge(),
                        TextEntry::make('payment_status')->badge(),
                        TextEntry::make('delivery_status')->badge(),
                        TextEntry::make('created_at')->dateTime()->label('Created At'),
                        TextEntry::make('delivery_date')->date()->label('Delivery Date'),
                        TextEntry::make('delivery_scheduled_type')->label('Delivery Type'),
                    ]),

                Section::make('Customer & Staff')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('customer_name')->label('Customer'),
                        TextEntry::make('delivery_name')->label('Delivery'),
                        TextEntry::make('manager_name')->label('Manager'),
                        TextEntry::make('branch_title')->label('Branch'),
                        TextEntry::make('cancelled_by_name')->label('Cancelled By'),
                    ]),

                Section::make('Pricing')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('subtotal_price')->money('SAR'),
                        TextEntry::make('discount_price')->money('SAR'),
                        TextEntry::make('delivery_fee')->money('SAR'),
                        TextEntry::make('service_fee')->money('SAR'),
                        TextEntry::make('tax_amount')->money('SAR'),
                        TextEntry::make('total_price')->money('SAR'),
                        TextEntry::make('payed_price')->money('SAR'),
                        TextEntry::make('coupon_title')->label('Coupon'),
                    ]),

                Section::make('Configuration')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('notes')->label('Notes'),
                        TextEntry::make('payment_method_title')->label('Payment Method'),
                        TextEntry::make('user_address_title')->label('Address Title'),
                    ]),

                Section::make('Order Cart Details')
                    ->columns(1)
                    ->schema([
                        RepeatableEntry::make('cart')
                            ->schema([
                                TextEntry::make('order_number'),
                                RepeatableEntry::make('cart_products')
                                    ->grid(3)
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('title')->columnSpanFull(),
                                        TextEntry::make('price'),
                                        TextEntry::make('quantity'),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number')->searchable(),
                TextColumn::make('total_price'),
                TextColumn::make('order_status')->badge(),
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
