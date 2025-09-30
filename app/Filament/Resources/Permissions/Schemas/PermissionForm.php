<?php

namespace App\Filament\Resources\Permissions\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TranslationComponent::configure('title')->required(),
                        TextInput::make('code')->required(),
                    ]),
            ]);
    }
}
