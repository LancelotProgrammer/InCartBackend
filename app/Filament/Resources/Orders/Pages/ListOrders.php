<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Actions\CreateOrderAction;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateOrderAction::configure(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),

            'future' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('delivery_date', '<', now()->inApplicationTodayRange()[0])),
            'today' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('delivery_date', now()->inApplicationTodayRange())->whereNotIn('order_status', [OrderStatus::CLOSED->value, OrderStatus::CANCELLED->value])),
            'past' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('delivery_date', '>', now()->inApplicationTodayRange()[1])),

            'missed' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereBeforeToday('delivery_date')->whereNotIn('order_status', [OrderStatus::CLOSED->value, OrderStatus::CANCELLED->value])),
            'to archive' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereIn('order_status', [OrderStatus::CLOSED->value, OrderStatus::CANCELLED->value])),
        ];
    }
}
