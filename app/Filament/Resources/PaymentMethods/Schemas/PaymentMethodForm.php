<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TranslationComponent::configure('title')->required(),
                        TextInput::make('code')
                            ->required()
                            ->scopedUnique(modifyQueryUsing: function ($query, $get) {
                                return $query->where('branch_id', $get('branch_id'));
                            }),
                        TextInput::make('order')->numeric()->required(),
                        Select::make('branch_id')
                            ->relationship('branch', 'title')
                            ->required(),
                    ]),
            ]);
    }
}
