<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdvertisementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order')
                    ->numeric(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('published_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('branch.title')
                    ->numeric(),
                TextEntry::make('product.title')
                    ->numeric(),
                TextEntry::make('category.title')
                    ->numeric(),
            ]);
    }
}
