<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('id'),
                TextEntry::make('title'),
                TextEntry::make('code'),
                TextEntry::make('permissions.title')->separator(' - ')->label('permissions')->columnSpanFull(),
            ]);
    }
}
