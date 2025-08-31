<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Config')
                    ->columns(1)
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        Select::make('permissions')
                            ->multiple()
                            ->relationship('permissions', 'title')
                            ->required(),
                    ]),
            ]);
    }
}
