<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Filament\Components\TranslationComponent;
use App\Filament\Forms\Components\MapBoundary;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TranslationComponent::configure('name')->columnSpanFull(),
                MapBoundary::make('boundary')->label('City Boundary')->columnSpanFull()->required(),
            ]);
    }
}
