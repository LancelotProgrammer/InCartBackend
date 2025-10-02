<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Components\TranslationComponent;
use App\Policies\PermissionPolicy;
use Filament\Forms\Components\CheckboxList;
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
                        TranslationComponent::configure('title')->required(),
                        TextInput::make('code')->required(),
                        CheckboxList::make('permissions')
                            ->columns(6)
                            ->relationship(
                                'permissions',
                                'title',
                                fn ($query) => PermissionPolicy::filterDeveloperSittings($query)
                            )
                            ->required(),
                    ]),
            ]);
    }
}
