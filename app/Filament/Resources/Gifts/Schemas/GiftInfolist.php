<?php

namespace App\Filament\Resources\Gifts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GiftInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                TextEntry::make('code'),
                TextEntry::make('points'),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('allowed_sub_total_price')
                    ->numeric(),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
