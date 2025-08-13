<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Enums\SettingType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required(),
                TextInput::make('value')
                    ->required(),
                Select::make('type')
                    ->options(SettingType::class)
                    ->required(),
                TextInput::make('group')
                    ->required(),
                Toggle::make('is_locked')
                    ->required(),
            ]);
    }
}
