<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Policies\RolePolicy;
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
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        TextInput::make('phone')->tel()->unique(ignoreRecord: true),
                        Select::make('city_id')
                            ->relationship('city', 'name'),
                        Select::make('role_id')
                            ->relationship(
                                'role',
                                'title',
                                fn ($query) => RolePolicy::filterOwnerAndDeveloperAndCustomer($query)
                            ),
                    ]),
            ]);
    }
}
