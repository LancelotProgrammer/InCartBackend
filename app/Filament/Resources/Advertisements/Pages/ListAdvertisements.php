<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Enums\AdvertisementType;
use App\Filament\Resources\Advertisements\AdvertisementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAdvertisements extends ListRecords
{
    protected static string $resource = AdvertisementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'statuses' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', AdvertisementType::STATUS->value)),
            'videos' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', AdvertisementType::VIDEO->value)),
            'cards' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', AdvertisementType::CARD->value)),
            'offers' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('type', '=', AdvertisementType::OFFER->value)),
        ];
    }
}
