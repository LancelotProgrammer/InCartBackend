<?php

namespace App\Filament\Resources\Cities\Schemas;

use App\Filament\Components\TranslationComponent;
use App\Filament\Forms\Components\MapBoundary;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TranslationComponent::configure('name')
                    ->columnSpanFull()
                    ->partiallyRenderComponentsAfterStateUpdated(['code'])
                    ->belowContent(Schema::between([
                        Action::make('generate_code')->action(function ($state, Set $set) {
                            if (isset($state['en'])) {
                                $set('code', Str::slug($state['en']));
                            }
                        }),
                    ])),
                TextInput::make('code')->disabled()->dehydrated()->required(),
                MapBoundary::make('boundary')->label('City Boundary')->columnSpanFull()->required(),
            ]);
    }
}
