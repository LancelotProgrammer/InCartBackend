<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\OrderActions;
use App\Filament\Filters\OrderTableFilter;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrdersTable extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
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
            ->filters(OrderTableFilter::configure(), FiltersLayout::Modal)
            ->recordActions([
                OrderActions::configure(false),
            ])
            ->toolbarActions([
                Action::make('Go')
                    ->color('primary')
                    ->url(fn () => route('filament.admin.resources.orders.index'), true),
            ]);
    }
}
