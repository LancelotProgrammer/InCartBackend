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
            'today' => Tab::make()->modifyQueryUsing(fn(Builder $query) =>
            $query->whereDate('delivery_date', '=', now())),
            'all' => Tab::make(),
            'missed' => Tab::make()->modifyQueryUsing(fn(Builder $query) =>
            $query->whereBeforeToday('delivery_date')),
            'future' => Tab::make()->modifyQueryUsing(fn(Builder $query) =>
            $query->whereAfterToday('delivery_date')),
            'to archive' => Tab::make()->modifyQueryUsing(fn(Builder $query) => 
            $query->whereIn('order_status', [OrderStatus::FINISHED->value, OrderStatus::CANCELLED->value])),
        ];
    }
}
