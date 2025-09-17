<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\DeliveryStatus;
use App\Enums\DeliveryScheduledType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
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
            ->columns([
                TextColumn::make('order_number')->searchable(),

                TextColumn::make('order_status')->badge(),
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('delivery_status')->badge(),
                TextColumn::make('delivery_scheduled_type')->badge(),

                TextColumn::make('total_price')->numeric(),
                TextColumn::make('delivery_date')->dateTime(),

                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('customer.name'),
                TextColumn::make('branch.title'),
                TextColumn::make('cart.title')->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('order_status')->options(OrderStatus::class),
                SelectFilter::make('payment_status')->options(PaymentStatus::class),
                SelectFilter::make('delivery_status')->options(DeliveryStatus::class),
                SelectFilter::make('delivery_scheduled_type')->options(DeliveryScheduledType::class),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
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
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
