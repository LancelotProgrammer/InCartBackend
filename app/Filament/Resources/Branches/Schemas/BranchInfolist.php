<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BranchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                TextEntry::make('id')->numeric(),
                TextEntry::make('title'),
                TextEntry::make('description'),
                TextEntry::make('longitude')->numeric(),
                TextEntry::make('latitude')->numeric(),
                TextEntry::make('city_id'),
            ]);
    }
}
