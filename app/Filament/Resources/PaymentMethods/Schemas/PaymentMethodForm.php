<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('order')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('published_at'),
                Select::make('branch_id')
                    ->relationship('branch', 'title')
                    ->required(),
            ]);
    }
}
