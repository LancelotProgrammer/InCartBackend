<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('longitude')
                    ->required()
                    ->numeric(),
                TextInput::make('latitude')
                    ->required()
                    ->numeric(),
                Select::make('city_id')
                    ->relationship('city', 'name')
                    ->required(),
            ]);
    }
}
