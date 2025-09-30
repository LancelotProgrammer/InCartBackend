<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Actions\OrderActions;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
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
            ->filters([
                Filter::make('is_today')->query(fn (Builder $query): Builder => $query->whereDate('delivery_date', '=', now())),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until')->after('created_from'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                SelectFilter::make('order_status')->options(OrderStatus::class),
                SelectFilter::make('payment_status')->options(PaymentStatus::class),
                SelectFilter::make('delivery_status')->options(DeliveryStatus::class),
                SelectFilter::make('delivery_scheduled_type')->options(DeliveryScheduledType::class),
            ], layout: FiltersLayout::Modal)
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
