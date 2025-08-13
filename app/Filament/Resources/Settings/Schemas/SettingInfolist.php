<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SettingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('key'),
                TextEntry::make('value'),
                TextEntry::make('type'),
                TextEntry::make('group'),
                IconEntry::make('is_locked')
                    ->boolean(),
            ]);
    }
}
