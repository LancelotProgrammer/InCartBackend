<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TranslationComponent::configure('title'),
                TranslationComponent::configure('description'),
                TextInput::make('latitude'),
                TextInput::make('longitude'),
                Select::make('city_id')
                    ->columnSpanFull()
                    ->relationship('city', 'name')
                    ->required(),
            ]);
    }
}
