<?php

namespace App\Filament\Pages;

use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliveryOrders extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected string $view = 'filament.pages.delivery-orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    public function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->query(
                fn (): Builder => Order::query()
                    ->whereDate('delivery_date', '=', now())
                    ->where('delivery_id', '=', auth()->user()->id)
            )
            ->defaultSort('id', 'desc')
            ->columns([
                Stack::make([
                    TextColumn::make('userAddress.title')->prefix('Address: '),
                    TextColumn::make('order_number')->prefix('Order number: '),
                    TextColumn::make('customer.name')->prefix('Customer name: '),
                    TextColumn::make('customer.phone')->prefix('Customer phone: '),
                    TextColumn::make('total_price')->prefix('Total price: '),
                    TextColumn::make('total_price')
                        ->label('Cart')
                        ->formatStateUsing(function (Order $record) {
                            return $record->carts->first()->cartProducts
                                ->map(fn ($item) => "{$item->product->title} × {$item->quantity}")
                                ->join(' | ');
                        })
                        ->wrap(),
                ]),
            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 3,
            ])
            ->recordActions([
                Action::make('open_location')
                    ->color('primary')
                    ->url(fn ($record) => "https://www.google.com/maps?q={$record->userAddress->latitude},{$record->userAddress->longitude}"),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->canViewDeliveryOrders();
    }

    public static function getNavigationBadge(): ?string
    {
        return Order::whereDate('delivery_date', now()->toDateString())
            ->where('delivery_id', '=', auth()->user()->id)
            ->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of pending orders for today';
    }
}
