<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Actions\OrderActions;
use App\Filament\Filters\OrderTableFilter;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->poll('30s')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('branch.title')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order_number')->searchable(),
                TextColumn::make('customer.name'),

                TextColumn::make('order_status')->badge(),
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('delivery_status')->badge(),

                TextColumn::make('total_price')->money('SAR', 2),
                TextColumn::make('delivery_date')->dateTime(),

                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery.name'),
                TextColumn::make('coupon.title')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paymentMethod.title')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('userAddress.title')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters(OrderTableFilter::configure(), FiltersLayout::Modal)
            ->recordActions([
                ViewAction::make(),
                Action::make('invoice')
                    ->authorize('viewInvoice')
                    ->icon(Heroicon::OutlinedArrowDownCircle)
                    ->color('primary')
                    ->url(fn (Order $record) => route('order.invoice', ['id' => $record->id]), true),
                OrderActions::configure(true),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
