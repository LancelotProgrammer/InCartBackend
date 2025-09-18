<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TranslationComponent::configure('name')->columnSpanFull(),
                TextInput::make('latitude'),
                TextInput::make('longitude'),
            ]);
    }
}
