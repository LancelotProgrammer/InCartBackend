<?php

namespace App\Filament\Resources\Orders\Pages;

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
            'today' => Tab::make()->modifyQueryUsing(fn(Builder $query) => $query->whereDate('delivery_date', '=', now())),
            'to archive' => Tab::make()->modifyQueryUsing(fn(Builder $query) => $query->whereDate('delivery_date', '<', now())),
        ];
    }
}
