<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\OrderActions;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TodaysOrdersTable extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->query(fn(): Builder => Order::query()->whereDate('delivery_date', '=', now()))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('branch.title'),
                TextColumn::make('order_number')->searchable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('customer', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('order_status')->badge(),
                TextColumn::make('delivery_date')->dateTime(),
                TextColumn::make('delivery.name'),
            ])
            ->recordActions([
                Action::make('view')
                    ->color('primary')
                    ->url(fn($record) => route('filament.admin.resources.orders.view', $record->id), true),
                Action::make('edit')
                    ->color('primary')
                    ->url(fn($record) => route('filament.admin.resources.orders.edit', $record->id), true),
                OrderActions::configure(false),
            ])
            ->toolbarActions([
                Action::make('open_full_page')
                    ->color('primary')
                    ->url(fn() => route('filament.admin.resources.orders.index'), true),
            ]);
    }
}
