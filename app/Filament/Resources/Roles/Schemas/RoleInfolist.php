<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Info')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id'),
                        TextEntry::make('title'),
                        TextEntry::make('code'),
                    ]),
                Fieldset::make('Permissions')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('permissions.title')->separator(' - ')->label('permissions')->columnSpanFull(),
                    ]),
            ]);
    }
}
