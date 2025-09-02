<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name'),
                        TextInput::make('email'),
                        TextInput::make('password')->password()->revealable()->dehydrateStateUsing(function ($state) {
                            return Hash::make($state);
                        }),
                        TextInput::make('phone'),
                        Select::make('city_id')
                            ->relationship('city', 'name'),
                        Select::make('role_id')
                            ->relationship('role', 'title'),
                    ]),
            ]);
    }
}
