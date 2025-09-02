<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('description'),
                TextEntry::make('brand'),
                TextEntry::make('unit')
                    ->badge(),
                TextEntry::make('sku'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
