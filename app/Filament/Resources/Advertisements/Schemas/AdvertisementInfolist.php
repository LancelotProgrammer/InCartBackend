<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdvertisementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->numeric(),
                TextEntry::make('title'),
                TextEntry::make('description'),
                TextEntry::make('order')->numeric(),
                TextEntry::make('type')->badge(),
                TextEntry::make('link')->badge(),
                TextEntry::make('published_at')->dateTime(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
                RepeatableEntry::make('Files')
                    ->schema([
                        ImageEntry::make('url'),
                    ])
                    ->columns(2),
            ]);
    }
}
