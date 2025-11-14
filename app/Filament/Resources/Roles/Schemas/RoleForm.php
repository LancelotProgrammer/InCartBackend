<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Components\TranslationComponent;
use App\Policies\PermissionPolicy;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                        TranslationComponent::configure('title')
                            ->partiallyRenderComponentsAfterStateUpdated(['code'])
                            ->belowContent(Schema::between([
                                Action::make('generate_code')->action(function ($state, Set $set) {
                                    if (isset($state['en'])) {
                                        $set('code', Str::slug($state['en']));
                                    }
                                }),
                            ])),
                        TextInput::make('code')->disabled()->dehydrated()->required(),
                        CheckboxList::make('permissions')
                            ->searchable()
                            ->columns(6)
                            ->relationship(
                                'permissions',
                                'title',
                                fn ($query) => PermissionPolicy::filterPermissions($query)->orderBy('code')
                            )
                            ->required(),
                    ]),
            ]);
    }
}
