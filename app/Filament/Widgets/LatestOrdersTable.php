<?php

namespace App\Filament\Widgets;

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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrdersTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Order::query())
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('branch.title'),
                TextColumn::make('order_number')->searchable(),
                TextColumn::make('customer.name'),
                TextColumn::make('order_status')->badge(),
                TextColumn::make('delivery_date')->dateTime(),
                TextColumn::make('customer.name'),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                Filter::make('is_today')->query(fn(Builder $query): Builder => $query->whereDate('delivery_date', '=', now())),
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
                Action::make('invoice')
                    ->icon(Heroicon::OutlinedArrowDownCircle)
                    ->color('primary')
                    ->url(fn (Order $record) => route('order.invoice', ['id' => $record->id]), true),
                OrderActions::configure(false),
            ])
            ->toolbarActions([
                Action::make('Go')
                    ->color('primary')
                    ->url(fn () => route('filament.admin.resources.orders.index'), true),
            ]);
    }
}
