<?php

namespace App\Filament\Resources\Branches\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Details')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TranslationComponent::configure('title'),
                        TranslationComponent::configure('description', false),
                        Select::make('city_id')->relationship('city', 'name')->columnSpanFull(),
                        TextInput::make('latitude')->numeric(),
                        TextInput::make('longitude')->numeric(),
                    ]),
            ]);
    }
}
